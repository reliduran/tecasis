<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
require_once '../config/db.php';
$conn->set_charset("utf8");

if (!isset($_GET['id'])) { header("Location: dashboard.php"); exit(); }
$id = intval($_GET['id']);

$mensaje = "";

// 1. GUARDAR CAMBIOS
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $claves = $conn->real_escape_string($_POST['palabras_clave']);
    $resp = $conn->real_escape_string($_POST['respuesta']);
    
    // CORRECCIÓN: Usamos id_conocimiento
    $sql = "UPDATE bot_conocimiento SET palabras_clave='$claves', respuesta='$resp' WHERE id_conocimiento=$id";
    
    if ($conn->query($sql)) {
        $mensaje = "¡Regla actualizada correctamente!";
    } else {
        $mensaje = "Error: " . $conn->error;
    }
}

// 2. OBTENER DATOS ACTUALES
// CORRECCIÓN: Usamos id_conocimiento
$res = $conn->query("SELECT * FROM bot_conocimiento WHERE id_conocimiento=$id");

if ($res->num_rows == 0) { header("Location: dashboard.php"); exit(); }
$data = $res->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Bot - Tecasis</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { background: #f1f5f9; padding: 40px; display: flex; justify-content: center; }
        .edit-card { background: white; padding: 30px; border-radius: 16px; width: 100%; max-width: 600px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .input-group { margin-bottom: 20px; }
        .input-group label { display: block; margin-bottom: 8px; font-weight: bold; color: #334155; }
        .input-group input, .input-group textarea { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-family: inherit; }
        .btn-save { background: #2563eb; color: white; border: none; padding: 12px 20px; border-radius: 8px; cursor: pointer; width: 100%; font-weight: bold; }
    </style>
</head>
<body>

    <div class="edit-card">
        <h2 style="margin-bottom: 20px; color: #0f172a;">Editar Conocimiento</h2>
        
        <?php if($mensaje): ?>
            <div style="background: #dcfce7; color: #166534; padding: 10px; margin-bottom: 20px; border-radius: 6px;">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-group">
                <label>Palabras Clave (Separadas por coma):</label>
                <input type="text" name="palabras_clave" value="<?php echo htmlspecialchars($data['palabras_clave']); ?>" required>
            </div>
            
            <div class="input-group">
                <label>Respuesta del Bot:</label>
                <textarea name="respuesta" rows="5" required><?php echo htmlspecialchars($data['respuesta']); ?></textarea>
            </div>
            
            <button type="submit" class="btn-save">Guardar Cambios</button>
        </form>
        
        <div style="margin-top: 20px; text-align: center;">
            <a href="dashboard.php" style="color: #64748b; text-decoration: none;">&larr; Volver al Dashboard</a>
        </div>
    </div>

</body>
</html>