<?php
// api/chatbot.php
header('Content-Type: application/json');

// Ajusta la ruta si tu archivo db.php está en otro nivel de carpetas
require_once '../config/db.php'; 

// Asegurar que la conexión hable UTF-8 (Vital para tildes y ñ)
$conn->set_charset("utf8mb4");

// Leer la entrada JSON
$input = json_decode(file_get_contents('php://input'), true);
$mensaje = isset($input['message']) ? trim($input['message']) : '';

// Limpieza básica: minúsculas y quitar signos de interrogación para mejorar coincidencia
$mensaje_limpio = mb_strtolower($mensaje, 'UTF-8');
$mensaje_limpio = preg_replace('/[¿?¡!.,]/', '', $mensaje_limpio);

$respuesta = "";
$es_error = 0;

// Si el mensaje está vacío, no hacemos nada
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
        // Separamos las palabras clave por coma (ej: "precio, costo, valor")
        $keywords = explode(',', $row['palabras_clave']);
        
        foreach ($keywords as $kw) {
            $kw = mb_strtolower(trim($kw), 'UTF-8');
            if (empty($kw)) continue;

            // MÉTODO A: Coincidencia Exacta (Prioridad Alta)
            // Si el mensaje contiene la palabra clave tal cual
            if (strpos($mensaje_limpio, $kw) !== false) {
                $respuesta = $row['respuesta'];
                $coincidencia_encontrada = true;
                break 2; // Rompemos ambos bucles (foreach y while)
            }

            // MÉTODO B: Coincidencia Difusa (Levenshtein) - Para errores de dedo
            // Solo aplicamos si la palabra tiene al menos 4 letras para evitar falsos positivos
            if (!$coincidencia_encontrada && strlen($kw) >= 4) {
                // Calcula cuántas letras de diferencia hay
                $distancia = levenshtein($mensaje_limpio, $kw);
                
                // Si la diferencia es muy pequeña (ej. 2 letras máximo)
                if ($distancia <= 2) {
                    // Si es la mejor coincidencia hasta ahora, la guardamos
                    if ($mejor_distancia == -1 || $distancia < $mejor_distancia) {
                        $mejor_distancia = $distancia;
                        $respuesta = $row['respuesta'];
                    }
                }
            }
        }
    }
}

// Si no hubo exacta, pero sí una difusa, usamos esa
if (!$coincidencia_encontrada && !empty($respuesta)) {
    $coincidencia_encontrada = true;
}

// --- FASE 2: RESPUESTAS DE RESPALDO (SI FALLA LA BD) ---
if (empty($respuesta)) {
    // Consulta dinámica de servicios (si preguntan qué ofrecen)
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
    // Saludo básico
    elseif (strpos($mensaje_limpio, 'hola') !== false) {
        $respuesta = "¡Hola! Soy el asistente virtual de Tecasis. Pregúntame por 'Soporte', 'Ubicación' o nuestros servicios.";
    } 
    // No entendió nada
    else {
        $es_error = 1;
        $respuesta = "No estoy seguro de entender eso. Intenta usar palabras clave como 'Ubicación', 'Precios' o 'Contacto'.";
    }
}

// --- LOGGING (Guardar historial) ---
// Usamos Prepared Statement para evitar problemas con comillas en el mensaje
$stmt_log = $conn->prepare("INSERT INTO interacciones_bot (pregunta_usuario, respuesta_bot, es_error) VALUES (?, ?, ?)");
if ($stmt_log) {
    $stmt_log->bind_param("ssi", $mensaje, $respuesta, $es_error);
    $stmt_log->execute();
    $stmt_log->close();
}

// Devolver JSON
echo json_encode(['reply' => $respuesta]);
?>