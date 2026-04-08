<?php
session_start();
// SEGURIDAD: Si no hay sesión, volver al login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once '../config/db.php';
$conn->set_charset("utf8");

// Validar que venga un ID
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$id = intval($_GET['id']);
$mensaje = "";

// 1. PROCESAR EL GUARDADO (UPDATE)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = $conn->real_escape_string($_POST['titulo']);
    $desc = $conn->real_escape_string($_POST['descripcion']);
    $icono = $conn->real_escape_string($_POST['icono']);
    $contenido = $conn->real_escape_string($_POST['contenido_html']);

    // Manejo de Imagen del Servicio (Opcional)
    $img_sql = "";
    if (!empty($_FILES['imagen_servicio']['name'])) {
        $target_dir = "../assets/img/";
        // Seguimos tu estándar de usar marcas de tiempo para evitar duplicados [3]
        $file_name = "servicio_" . time() . ".jpg"; 
        if (move_uploaded_file($_FILES["imagen_servicio"]["tmp_name"], $target_dir . $file_name)) {
            $img_path = "assets/img/" . $file_name;
            $img_sql = ", imagen_path='$img_path'"; 
        }
    }

    // Actualización integral incluyendo la ruta de la imagen si se subió una nueva
    $sql_update = "UPDATE servicios SET 
                   titulo='$titulo', 
                   descripcion='$desc', 
                   icono='$icono', 
                   contenido_html='$contenido' 
                   $img_sql 
                   WHERE id_servicio=$id";

    if ($conn->query($sql_update)) {
        $mensaje = "¡Servicio actualizado correctamente!";
    } else {
        $mensaje = "Error al guardar: " . $conn->error;
    }
}

// 2. OBTENER DATOS ACTUALES DEL SERVICIO
$sql = "SELECT * FROM servicios WHERE id_servicio = $id";
$res = $conn->query($sql);

// Si el servicio no existe, volver al dashboard
if ($res->num_rows == 0) {
    header("Location: dashboard.php");
    exit();
}

$data = $res->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Servicio - Tecasis</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #f4f6f9; padding: 40px; font-family: sans-serif; }
        .editor-card { 
            background: white; 
            max-width: 800px; 
            margin: 0 auto; 
            padding: 40px; 
            border-radius: 10px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
        }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: bold; margin-bottom: 8px; color: #333; }
        input[type="text"], textarea { 
            width: 100%; 
            padding: 12px; 
            border: 1px solid #ddd; 
            border-radius: 5px; 
            font-size: 1rem;
        }
        textarea { resize: vertical; }
        .btn-guardar {
            background-color: #3b82f6;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            width: 100%;
            font-weight: bold;
        }
        .btn-guardar:hover { background-color: #2563eb; }
        .back-link { text-decoration: none; color: #666; font-size: 0.9rem; }
        .back-link:hover { color: #333; }
    </style>
</head>
<body>

    <div class="editor-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="margin: 0; color: #0f172a;">Editar: <?php echo htmlspecialchars($data['titulo']); ?></h2>
            <a href="dashboard.php" class="back-link">← Volver al Dashboard</a>
        </div>

        <?php if($mensaje): ?>
            <div style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #bbf7d0;">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Título del Servicio:</label>
                <input type="text" name="titulo" value="<?php echo htmlspecialchars($data['titulo']); ?>" required>
            </div>

            <div class="form-group">
                <label>Icono (Clase de FontAwesome):</label>
                <div style="display: flex; gap: 10px;">
                    <div style="background: #eee; padding: 10px; border-radius: 5px; width: 45px; text-align: center;">
                        <i class="fa-solid <?php echo htmlspecialchars($data['icono']); ?>"></i>
                    </div>
                    <input type="text" name="icono" value="<?php echo htmlspecialchars($data['icono']); ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label>Descripción Corta (Aparece en la tarjeta):</label>
                <textarea name="descripcion" rows="3" required><?php echo htmlspecialchars($data['descripcion']); ?></textarea>
            </div>

            <hr style="border: 0; border-top: 1px solid #eee; margin: 30px 0;">

            <div class="input-group">
                <label>Imagen del Servicio (Opcional):</label>
                <input type="file" name="imagen_servicio" accept="image/*">
            <?php if(!empty($data['imagen_path'])): ?>
                <p style="font-size: 0.8rem; color: #666;">Imagen actual: <?php echo $data['imagen_path']; ?></p>
            <?php endif; ?>
    </div>

    <hr style="margin: 20px 0; border: 0; border-top: 1px solid #eee;">
    
        <label>Contenido Completo de la Página:</label>
        <textarea name="contenido_html" rows="10"><?php echo htmlspecialchars($data['contenido_html']); ?></textarea>
    
        <button type="submit" class="btn">Guardar Cambios</button>
    </form>
    </div>

</body>
</html>