<?php
// MODO DIAGNÓSTICO
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

require_once '../config/db.php';
$conn->set_charset("utf8");

$mensaje = "";
$error_db = "";

// --- 1. PROCESAR FORMULARIOS ---

// A. ACTUALIZAR SECCIÓN "NOSOTROS" (CON SOPORTE PARA IMAGEN)

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['actualizar_nosotros'])) {
    // 1. Sanitización de los 6 campos de texto para evitar borrar el Hero [3, 4]
    $sub = $conn->real_escape_string($_POST['subtitulo']);
    $tit = $conn->real_escape_string($_POST['titulo']);
    $desc = $conn->real_escape_string($_POST['descripcion']);
    $h_tit = $conn->real_escape_string($_POST['hero_titulo']);
    $h_desc = $conn->real_escape_string($_POST['hero_desc']);
    $h_btn = $conn->real_escape_string($_POST['hero_boton']);

    // 2. Manejo de Imagen lateral [4]
    $img_sql = "";
    if (!empty($_FILES['imagen_nosotros']['name'])) {
        $target_dir = "../assets/img/";
        $file_name = "nosotros_" . time() . ".jpg";
        if (move_uploaded_file($_FILES["imagen_nosotros"]["tmp_name"], $target_dir . $file_name)) {
            $img_path = "assets/img/" . $file_name;
            $img_sql = ", imagen_path='$img_path'"; 
        }
    }

    // 3. UPDATE INTEGRAL: Mantiene la integridad de la tabla [5]
    $sql = "UPDATE seccion_nosotros SET 
            subtitulo='$sub', titulo='$tit', descripcion='$desc',
            hero_titulo='$h_tit', hero_desc='$h_desc', hero_boton='$h_btn' 
            $img_sql 
            WHERE id=1";

    $conn->query($sql);
}

// B. AGREGAR SERVICIO
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nuevo_servicio'])) {
    $titulo = $conn->real_escape_string($_POST['titulo']);
    $desc = $conn->real_escape_string($_POST['descripcion']);
    $icono = $conn->real_escape_string($_POST['icono']);
    
    if($conn->query("INSERT INTO servicios (titulo, descripcion, icono, activo) VALUES ('$titulo', '$desc', '$icono', 1)")){
        $mensaje = "¡Servicio agregado!";
    } else {
        $error_db = "Error al agregar servicio: " . $conn->error;
    }
}

// C. AGREGAR CONOCIMIENTO (BOT)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nuevo_conocimiento'])) {
    $claves = $conn->real_escape_string($_POST['palabras_clave']);
    $resp = $conn->real_escape_string($_POST['respuesta']);
    
    if($conn->query("INSERT INTO bot_conocimiento (palabras_clave, respuesta, activo) VALUES ('$claves', '$resp', 1)")){
        $mensaje = "¡Regla del bot guardada!";
    } else {
        $error_db = "Error al guardar regla: " . $conn->error;
    }
}

// D. ELIMINAR (BOT O SERVICIO)
if (isset($_GET['borrar_bot'])) {
    $id_borrar = intval($_GET['borrar_bot']);
    $conn->query("DELETE FROM bot_conocimiento WHERE id_conocimiento=$id_borrar");
    header("Location: dashboard.php"); exit();
}
if (isset($_GET['borrar_servicio'])) {
    $id_borrar = intval($_GET['borrar_servicio']);
    $conn->query("DELETE FROM servicios WHERE id_servicio=$id_borrar");
    header("Location: dashboard.php"); exit();
}

// --- 2. CONSULTAS DE DATOS ---

// Datos de "Nosotros"
$nosotros_data = $conn->query("SELECT * FROM seccion_nosotros WHERE id=1")->fetch_assoc();

// Servicios
$servicios = $conn->query("SELECT * FROM servicios");

// Bot y Chat
$conocimientos = $conn->query("SELECT * FROM bot_conocimiento ORDER BY id_conocimiento DESC");
$sql_logs = "SELECT * FROM interacciones_bot ORDER BY fecha_hora DESC LIMIT 50";
$logs_result = $conn->query($sql_logs);

$nombre_usuario = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Admin';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Tecasis</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #f1f5f9; padding-bottom: 50px; }
        .admin-nav { background: #ffffff; padding: 15px 5%; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; }
        .admin-container { max-width: 1200px; margin: 30px auto; padding: 0 20px; display: grid; gap: 30px; }
        .panel-card { background: white; padding: 25px; border-radius: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; }
        h3 { margin-bottom: 20px; color: #0f172a; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #f1f5f9; font-size: 0.95rem; }
        th { color: #64748b; font-weight: 600; }
        
        .input-group { margin-bottom: 15px; }
        .input-group label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 0.9rem; color: #334155; }
        .input-group input, .input-group textarea { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-family: inherit; font-size: 0.95rem; }
        
        .btn-action { padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 0.85rem; margin-right: 5px; color: white; display: inline-block; }
        .btn-edit { background: #3b82f6; }
        .btn-del { background: #ef4444; }
        
        .alert-error { background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #dcfce7; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        
        /* Estilos Chat */
        .chat-user { color: #2563eb; font-weight: 600; }
        .chat-bot { color: #64748b; font-style: italic; }
        .chat-date { font-size: 0.8rem; color: #94a3b8; width: 150px; }
    </style>
</head>
<body>

    <nav class="admin-nav">
        <div style="font-weight: 800; font-size: 1.2rem; color: #0f172a;">TECASIS ADMIN</div>
        <div>
            Hola, <?php echo htmlspecialchars($nombre_usuario); ?> 
            <a href="../index.php" target="_blank" style="margin-left: 15px; text-decoration: none; color: #2563eb;">Ver Web</a>
            <?php if (isset($_SESSION['user_rol']) && $_SESSION['user_rol'] == 'admin'): ?>
    <a href="usuarios.php" class="btn-ver" style="text-decoration: none; color: #2563eb;">Usuarios</a>
            <?php endif; ?>
            <a href="logout.php" style="margin-left: 15px; color: #ef4444; text-decoration: none;">Salir</a>
        </div>
    </nav>

    <div class="admin-container">
        
        <?php if($mensaje): ?>
            <div class="alert-success"><i class="fa-solid fa-check-circle"></i> <?php echo $mensaje; ?></div>
        <?php endif; ?>
        <?php if($error_db): ?>
            <div class="alert-error"><strong>Error SQL:</strong> <?php echo $error_db; ?></div>
        <?php endif; ?>

      <div class="panel-card">
    <h3><i class="fa-solid fa-id-card"></i> Editar Sección "Sobre Nosotros"</h3>
<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="actualizar_nosotros" value="1">
    <div class="input-group"><label>Subtítulo:</label><input type="text" name="subtitulo" value="<?php echo htmlspecialchars($nosotros_data['subtitulo']); ?>" required></div>
    <div class="input-group"><label>Título:</label><input type="text" name="titulo" value="<?php echo htmlspecialchars($nosotros_data['titulo']); ?>" required></div>
    <div class="input-group"><label>Descripción:</label><textarea name="descripcion" required><?php echo htmlspecialchars($nosotros_data['descripcion']); ?></textarea></div>
    <div class="input-group"><label>Imagen Lateral:</label><input type="file" name="imagen_nosotros"></div>
    
    <hr style="margin: 20px 0;">
    <h3>Configuración de Portada (Hero)</h3>
    <div class="input-group"><label>Título Hero:</label><input type="text" name="hero_titulo" value="<?php echo htmlspecialchars($nosotros_data['hero_titulo']); ?>"></div>
    <div class="input-group"><label>Descripción Hero:</label><textarea name="hero_desc"><?php echo htmlspecialchars($nosotros_data['hero_desc']); ?></textarea></div>
    <div class="input-group"><label>Botón Hero:</label><input type="text" name="hero_boton" value="<?php echo htmlspecialchars($nosotros_data['hero_boton']); ?>"></div>
    
    <button type="submit" class="btn" style="width: 100%; margin-top: 15px;">Guardar Todo</button>
</form>
</div>
      
      
        <div class="panel-card">
            <h3><i class="fa-solid fa-briefcase"></i> Gestión de Servicios</h3>
            <form method="POST" style="margin-bottom: 20px;">
                <input type="hidden" name="nuevo_servicio" value="1">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="input-group"><label>Título:</label><input type="text" name="titulo" required></div>
                    <div class="input-group"><label>Icono (fa-solid):</label><input type="text" name="icono" required></div>
                </div>
                <div class="input-group"><label>Descripción:</label><textarea name="descripcion" required rows="2"></textarea></div>
                <button type="submit" class="btn-primary" style="font-size: 0.9rem;">+ Agregar Servicio</button>
            </form>

            <table>
                <thead><tr><th>Icono</th><th>Título</th><th>Acciones</th></tr></thead>
                <tbody>
                    <?php if ($servicios && $servicios->num_rows > 0): ?>
                        <?php while($s = $servicios->fetch_assoc()): ?>
                        <tr>
                            <td><i class="fa-solid <?php echo htmlspecialchars($s['icono']); ?>"></i></td>
                            <td><?php echo htmlspecialchars($s['titulo']); ?></td>
                            <td>
                                <a href="edit_service.php?id=<?php echo $s['id_servicio']; ?>" class="btn-action btn-edit"><i class="fa-solid fa-pen"></i></a>
                                <a href="dashboard.php?borrar_servicio=<?php echo $s['id_servicio']; ?>" class="btn-action btn-del" onclick="return confirm('¿Borrar?');"><i class="fa-solid fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="3">No hay servicios.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="panel-card">
            <h3><i class="fa-solid fa-robot"></i> Entrenar al Bot</h3>
            <form method="POST" style="margin-bottom: 20px;">
                <input type="hidden" name="nuevo_conocimiento" value="1">
                <div class="input-group">
                    <label>Palabras Clave:</label>
                    <input type="text" name="palabras_clave" required placeholder="Ej: precio, costo">
                </div>
                <div class="input-group">
                    <label>Respuesta:</label>
                    <textarea name="respuesta" required rows="2"></textarea>
                </div>
                <button type="submit" class="btn-primary" style="background: #10b981; font-size: 0.9rem;">+ Guardar Regla</button>
            </form>

            <table>
                <thead><tr><th>Claves</th><th>Respuesta</th><th>Acciones</th></tr></thead>
                <tbody>
                    <?php if ($conocimientos && $conocimientos->num_rows > 0): ?>
                        <?php while($c = $conocimientos->fetch_assoc()): ?>
                        <tr>
                            <td style="color: #2563eb; font-weight: bold;"><?php echo htmlspecialchars($c['palabras_clave']); ?></td>
                            <td><?php echo htmlspecialchars(substr($c['respuesta'], 0, 50)) . '...'; ?></td>
                            <td>
                                <a href="edit_bot.php?id=<?php echo $c['id_conocimiento']; ?>" class="btn-action btn-edit"><i class="fa-solid fa-pen"></i></a>
                                <a href="dashboard.php?borrar_bot=<?php echo $c['id_conocimiento']; ?>" class="btn-action btn-del" onclick="return confirm('¿Borrar?');"><i class="fa-solid fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="panel-card">
            <h3><i class="fa-solid fa-comments"></i> Historial de Chats</h3>
            <table>
                <thead><tr><th>Fecha</th><th>Pregunta</th><th>Respuesta</th></tr></thead>
                <tbody>
                    <?php if ($logs_result && $logs_result->num_rows > 0): ?>
                        <?php while($log = $logs_result->fetch_assoc()): ?>
                        <tr>
                            <td class="chat-date"><?php echo htmlspecialchars($log['fecha_hora']); ?></td>
                            <td class="chat-user"><?php echo htmlspecialchars($log['pregunta_usuario']); ?></td>
                            <td class="chat-bot"><?php echo htmlspecialchars($log['respuesta_bot']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="3">Sin actividad reciente.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</body>
</html>