<?php
// admin/usuarios.php
session_start();
// Si la sesión no tiene rol o el rol NO es 'admin', detener la ejecución
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] != 'admin') {
    // Redirigir al dashboard o mostrar error
    header("Location: dashboard.php"); 
    exit();
}
require_once '../config/db.php';

// 1. SEGURIDAD: Verificar si es admin
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$mensaje = "";
$usuario_editar = null;

// 2. LÓGICA CRUD

// A. ELIMINAR USUARIO
if (isset($_GET['borrar'])) {
    $id_borrar = intval($_GET['borrar']);
    // Evitar que te borres a ti mismo
    if ($id_borrar != $_SESSION['user_id']) {
        $conn->query("DELETE FROM usuarios_admin WHERE id_usuario = $id_borrar");
        $mensaje = "Usuario eliminado.";
    } else {
        $mensaje = "Error: No puedes eliminar tu propia cuenta.";
    }
}

// B. PREPARAR EDICIÓN (Cargar datos en el formulario)
if (isset($_GET['editar'])) {
    $id_editar = intval($_GET['editar']);
    $res = $conn->query("SELECT * FROM usuarios_admin WHERE id_usuario = $id_editar");
    $usuario_editar = $res->fetch_assoc();
}

// C. GUARDAR (CREAR O ACTUALIZAR)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $email = $conn->real_escape_string($_POST['email']);
    $rol = $_POST['rol'];
    $pass = $_POST['password'];
    
    // Si hay un ID oculto, es una ACTUALIZACIÓN
    if (!empty($_POST['id_usuario'])) {
        $id = intval($_POST['id_usuario']);
        
        // Solo actualizamos contraseña si el campo no está vacío
        if (!empty($pass)) {
            $pass_hash = password_hash($pass, PASSWORD_DEFAULT);
            $sql = "UPDATE usuarios_admin SET nombre_completo='$nombre', email='$email', rol='$rol', password_hash='$pass_hash' WHERE id_usuario=$id";
        } else {
            $sql = "UPDATE usuarios_admin SET nombre_completo='$nombre', email='$email', rol='$rol' WHERE id_usuario=$id";
        }
        
        if ($conn->query($sql)) {
            $mensaje = "Usuario actualizado correctamente.";
            $usuario_editar = null; // Limpiar formulario
        }
    } 
    // Si no hay ID, es una INSERCIÓN (NUEVO)
    else {
        if (!empty($pass)) {
            $pass_hash = password_hash($pass, PASSWORD_DEFAULT);
            $sql = "INSERT INTO usuarios_admin (nombre_completo, email, password_hash, rol) VALUES ('$nombre', '$email', '$pass_hash', '$rol')";
            
            if ($conn->query($sql)) {
                $mensaje = "Usuario registrado con éxito.";
            } else {
                $mensaje = "Error: El email ya existe o hubo un problema.";
            }
        } else {
            $mensaje = "Error: La contraseña es obligatoria para nuevos usuarios.";
        }
    }
}

// 3. LEER LISTA DE USUARIOS
$lista_usuarios = $conn->query("SELECT * FROM usuarios_admin ORDER BY id_usuario ASC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios - Tecasis</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Estilos simples para complementar tu CSS base */
        body { background-color: #f3f4f6; padding: 20px; }
        .admin-container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 15px; }
        .btn { padding: 8px 15px; border-radius: 5px; text-decoration: none; color: white; display: inline-block; cursor: pointer; border: none;}
        .btn-primary { background-color: #2563eb; }
        .btn-warning { background-color: #f59e0b; }
        .btn-danger { background-color: #ef4444; }
        .btn-secondary { background-color: #6b7280; }
        
        /* Tabla */
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f8fafc; color: #0f172a; }
        
        /* Formulario */
        .form-row { display: flex; gap: 15px; margin-bottom: 15px; }
        .form-group { flex: 1; }
        .form-control { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #334155; }
    </style>
</head>
<body>

<div class="admin-container">
    <div class="header-top">
        <h2><i class="fas fa-users-cog"></i> Gestión de Usuarios</h2>
        <div>
            <a href="dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver al Dashboard</a>
            <a href="logout.php" class="btn btn-danger">Salir</a>
        </div>
    </div>

    <?php if ($mensaje): ?>
        <div style="padding: 10px; background: #dcfce7; color: #166534; border-radius: 5px; margin-bottom: 15px;">
            <?php echo $mensaje; ?>
        </div>
    <?php endif; ?>

    <!-- FORMULARIO DE REGISTRO / EDICIÓN -->
    <div style="background: #f8fafc; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
        <h3><?php echo $usuario_editar ? 'Editar Usuario' : 'Registrar Nuevo Usuario'; ?></h3>
        
        <form method="POST" action="usuarios.php">
            <input type="hidden" name="id_usuario" value="<?php echo $usuario_editar['id_usuario'] ?? ''; ?>">
            
            <div class="form-row">
                <div class="form-group">
                    <label>Nombre Completo:</label>
                    <input type="text" name="nombre" class="form-control" required value="<?php echo $usuario_editar['nombre_completo'] ?? ''; ?>">
                </div>
                <div class="form-group">
                    <label>Email (Usuario):</label>
                    <input type="email" name="email" class="form-control" required value="<?php echo $usuario_editar['email'] ?? ''; ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Contraseña:</label>
                    <input type="password" name="password" class="form-control" placeholder="<?php echo $usuario_editar ? 'Dejar en blanco para mantener la actual' : 'Requerida para nuevos usuarios'; ?>">
                </div>
                <div class="form-group">
                    <label>Rol:</label>
                    <select name="rol" class="form-control">
                        <option value="admin" <?php if(isset($usuario_editar) && $usuario_editar['rol'] == 'admin') echo 'selected'; ?>>Administrador</option>
                        <option value="editor" <?php if(isset($usuario_editar) && $usuario_editar['rol'] == 'editor') echo 'selected'; ?>>Editor (Restringido)</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> <?php echo $usuario_editar ? 'Actualizar Usuario' : 'Guardar Usuario'; ?>
            </button>
            
            <?php if ($usuario_editar): ?>
                <a href="usuarios.php" class="btn btn-secondary">Cancelar Edición</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- LISTA DE USUARIOS EXISTENTES -->
    <h3>Usuarios Activos</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while($user = $lista_usuarios->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $user['id_usuario']; ?></td>
                    <td><?php echo htmlspecialchars($user['nombre_completo']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td>
                        <span style="padding: 3px 8px; border-radius: 10px; background: <?php echo $user['rol']=='admin' ? '#dbeafe' : '#f3f4f6'; ?>; font-size: 0.85rem;">
                            <?php echo ucfirst($user['rol']); ?>
                        </span>
                    </td>
                    <td>
                        <!-- Botón Editar -->
                        <a href="usuarios.php?editar=<?php echo $user['id_usuario']; ?>" class="btn btn-warning" style="padding: 5px 10px;">
                            <i class="fas fa-edit"></i>
                        </a>
                        
                        <!-- Botón Borrar (Protegido para no borrarse a sí mismo) -->
                        <?php if($user['id_usuario'] != $_SESSION['user_id']): ?>
                            <a href="usuarios.php?borrar=<?php echo $user['id_usuario']; ?>" 
                               class="btn btn-danger" 
                               style="padding: 5px 10px;"
                               onclick="return confirm('¿Estás seguro de eliminar a este usuario?');">
                                <i class="fas fa-trash"></i>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>