<?php
// api/chatbot.php
header('Content-Type: application/json');

// Ajusta la ruta si tu archivo db.php está en otro nivel de carpetas
require_once '../config/db.php'; 

// agregamos la API de Gemini
$apiKey = $config['GEMINI_API_KEY']; 

// Asegurar que la conexión hable UTF-8
$conn->set_charset("utf8mb4");

// Leer la entrada JSON
$input = json_decode(file_get_contents('php://input'), true);
$mensaje = isset($input['message']) ? trim($input['message']) : '';

// Limpieza básica
$mensaje_limpio = mb_strtolower($mensaje, 'UTF-8');
$mensaje_limpio = preg_replace('/[¿?¡!.,]/', '', $mensaje_limpio);

$respuesta = "";
$es_error = 0;

if (empty($mensaje)) { 
    echo json_encode(['reply' => '']); 
    exit; 
}

// --- FASE 1: BÚSQUEDA INTELIGENTE EN BASE DE DATOS ---
$sql_knowledge = "SELECT palabras_clave, respuesta FROM bot_conocimiento WHERE activo = 1";
$result = $conn->query($sql_knowledge);

$mejor_distancia = -1;
$coincidencia_encontrada = false;

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $keywords = explode(',', $row['palabras_clave']);
        
        foreach ($keywords as $kw) {
            $kw = mb_strtolower(trim($kw), 'UTF-8');
            if (empty($kw)) continue;

            if (strpos($mensaje_limpio, $kw) !== false) {
                $respuesta = $row['respuesta'];
                $coincidencia_encontrada = true;
                break 2; 
            }

            if (!$coincidencia_encontrada && strlen($kw) >= 4) {
                $distancia = levenshtein($mensaje_limpio, $kw);
                if ($distancia <= 2) {
                    if ($mejor_distancia == -1 || $distancia < $mejor_distancia) {
                        $mejor_distancia = $distancia;
                        $respuesta = $row['respuesta'];
                    }
                }
            }
        }
    }
}

if (!$coincidencia_encontrada && !empty($respuesta)) {
    $coincidencia_encontrada = true;
}

// --- FASE 2: RESPUESTAS DE RESPALDO (ACTUALIZADA CON IA) ---
if (empty($respuesta)) {
    if (strpos($mensaje_limpio, 'servicio') !== false || strpos($mensaje_limpio, 'ofrecen') !== false) {
        $serv_sql = "SELECT titulo FROM servicios WHERE activo = 1";
        $serv_res = $conn->query($serv_sql);
        $items = [];
        if($serv_res) {
            while($s = $serv_res->fetch_assoc()) { $items[] = $s['titulo']; }
        }
        $respuesta = !empty($items) 
            ? "Nuestros servicios activos son: " . implode(", ", $items) . ". ¿Te interesa cotizar alguno?"
            : "Actualmente estamos actualizando nuestro catálogo.";
    } 
    elseif (strpos($mensaje_limpio, 'hola') !== false) {
        $respuesta = "¡Hola! Soy el asistente virtual de Tecasis. Pregúntame por 'Soporte', 'Ubicación' o nuestros servicios.";
    } 
    else {
        // --- EXTRAER MEMORIA DE LA BASE DE DATOS ---
        $memoria_empresa = "No hay datos adicionales."; 
        
        $sql_info = "SELECT telefono, email_contacto, direccion, horarios, descripcion FROM seccion_nosotros LIMIT 1";
        $result_info = $conn->query($sql_info);
        
        if (!$result_info) {
            error_log("Error SQL (seccion_nosotros): " . $conn->error);
        } elseif ($result_info->num_rows == 0) {
            error_log("Error SQL: La tabla seccion_nosotros existe pero ESTÁ VACÍA.");
        } else {
            $info = $result_info->fetch_assoc();
            
            $serv_activos = [];
            $serv_sql = "SELECT titulo FROM servicios WHERE activo = 1";
            $serv_res = $conn->query($serv_sql);
            if($serv_res) {
                while($s = $serv_res->fetch_assoc()) { $serv_activos[] = $s['titulo']; }
            }
            
            $memoria_empresa = "INFORMACIÓN OFICIAL DE TECASIS:\n" .
                               "- Teléfono/WhatsApp: " . $info['telefono'] . "\n" .
                               "- Correo de Ventas: " . $info['email_contacto'] . "\n" .
                               "- Ubicación: " . $info['direccion'] . "\n" .
                               "- Horarios: " . $info['horarios'] . "\n" .
                               "- ¿Quiénes somos?: " . $info['descripcion'] . "\n" .
                               "- Servicios activos: " . implode(", ", $serv_activos) . ".\n";
        }

        $respuestaIA = llamarGemini($mensaje_limpio, $apiKey, $memoria_empresa);
        
        if ($respuestaIA) {
            $respuesta = $respuestaIA;
            $es_error = 0; 
        } else {
            $es_error = 1;
            $respuesta = "No estoy seguro de entender eso. Intenta usar palabras clave como 'Ubicación', 'Precios' o 'Contacto'.";
        }
    }
} 

// --- LOGGING ---
$stmt_log = $conn->prepare("INSERT INTO interacciones_bot (pregunta_usuario, respuesta_bot, es_error) VALUES (?, ?, ?)");
if ($stmt_log) {
    $stmt_log->bind_param("ssi", $mensaje, $respuesta, $es_error);
    $stmt_log->execute();
    $stmt_log->close();
}

echo json_encode(['reply' => $respuesta]);

// --- FUNCIÓN GLOBAL ---
function llamarGemini($pregunta, $key, $memoria) {
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite:generateContent?key=" . $key;

    $contexto = "Eres el asistente virtual de Tecasis, una empresa de TI y soporte técnico. " .
                "REGLAS ESTRICTAS: " .
                "1. Solo responde preguntas relacionadas con los servicios, soporte, ubicación o contacto de Tecasis. " .
                "2. Si preguntan por precios, diles amablemente que nos contacten por WhatsApp o correo para una cotización personalizada. " .
                "3. Basa todas tus respuestas en esta información real de la empresa:\n\n" . $memoria;

    $data = [
        "system_instruction" => [
            "parts" => [["text" => $contexto]]
        ],
        "contents" => [
            ["parts" => [["text" => $pregunta]]]
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); 

    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    // --- BLOQUE DE DIAGNÓSTICO PARA CPANEL ---
    if ($err) {
        error_log("Error crítico de cURL en cPanel: " . $err);
        return null; 
    }

    $json = json_decode($response, true);

    if (isset($json['error'])) { 
        error_log("Error de Gemini API en cPanel: " . $json['error']['message']);
        return null; 
    } 

    if (!isset($json['candidates'][0]['content']['parts'][0]['text'])) { 
        error_log("Estructura inesperada. RAW: " . $response);
        return null; 
    }
        
    return $json['candidates'][0]['content']['parts'][0]['text'];
}
?>