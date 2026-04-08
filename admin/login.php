<?php
// admin/login.php
session_start();
require_once '../config/db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // 1. Usar Sentencias Preparadas (Prepared Statements) para seguridad total
    $sql = "SELECT id_usuario, nombre_completo, password_hash, rol FROM usuarios_admin WHERE email = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            
            // 2. VERIFICACIÓN SEGURA
            // Se eliminó la validación insegura: ($password === 'admin123_temporal')
            // Ahora solo verifica el hash Bcrypt de la base de datos
            if (password_verify($password, $row['password_hash'])) {
                
                // Login Exitoso
                session_regenerate_id(true); // Prevenir secuestro de sesión
                $_SESSION['user_id'] = $row['id_usuario'];
                $_SESSION['user_name'] = $row['nombre_completo'];
                $_SESSION['user_rol'] = $row['rol'];
                
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Credenciales incorrectas.";
            }
        } else {
            $error = "Credenciales incorrectas.";
        }
        $stmt->close();
    } else {
        // En producción usa error_log, no imprimas errores de SQL al usuario
        error_log("Error en login: " . $conn->error);
        $error = "Error del sistema. Intente más tarde.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión - Tecasis</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body.login-page {
            display: flex; align-items: center; justify-content: center;
            height: 100vh; background: #0f172a; margin: 0;
        }
        .login-card {
            background: white; padding: 40px; border-radius: 20px;
            width: 100%; max-width: 400px; text-align: center;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        .login-card h2 { margin-bottom: 20px; color: #0f172a; }
        .input-group { margin-bottom: 20px; text-align: left; }
        .input-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #334155; }
        
        .input-group input {
            width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; font-size: 1rem;
            background-color: white !important; /* Forzar blanco */
        }
        /* Eliminar el fondo amarillo/azul de Chrome */
        input:-webkit-autofill,
        input:-webkit-autofill:hover, 
        input:-webkit-autofill:focus, 
        input:-webkit-autofill:active{
            -webkit-box-shadow: 0 0 0 30px white inset !important;
            transition: background-color 5000s ease-in-out 0s;
        }

        .input-group input:focus { border-color: #2563eb; }
        .btn-full {
            width: 100%; background-color: #2563eb; color: white; padding: 12px;
            border: none; border-radius: 50px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: background 0.3s;
        }
        .btn-full:hover { background-color: #1d4ed8; }
        .error-msg { background: #fee2e2; color: #dc2626; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; }
    </style>
</head>
<body class="login-page">

    <div class="login-card">
        <h2>Zona Administrativa</h2>
        <?php if($error): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="fake_email" style="position:fixed; left:-9999px; width:1px; height:1px;">
            <input type="password" name="fake_pass" style="position:fixed; left:-9999px; width:1px; height:1px;">

            <div class="input-group">
                <label>Email:</label>
                <input type="text" name="email" id="real_email" 
                       autocomplete="off" 
                       placeholder="Ingrese su correo de admin">
            </div>
            
            <div class="input-group">
                <label>Contraseña:</label>
                <input type="password" name="password" id="real_pass" 
                       autocomplete="new-password" 
                       placeholder=" ">
            </div>
            
            <button type="submit" class="btn-full">Iniciar Sesión</button>
        </form>
        
        <div style="margin-top: 25px;">
            <a href="../index.php" style="color: #64748b; text-decoration: none; font-size: 0.9rem;">&larr; Volver al sitio web</a>
        </div>
    </div>

    <script>
        // Esperamos a que la página cargue y luego limpiamos los campos
        // Esto borra lo que sea que Chrome haya logrado insertar
        window.addEventListener('load', function() {
            setTimeout(function() {
                var emailField = document.getElementById('real_email');
                var passField = document.getElementById('real_pass');
                
                // Si tienen valor, lo borramos (pero dejamos que el usuario escriba después)
                if(emailField) emailField.value = '';
                if(passField) passField.value = '';
            }, 100); // 100ms de retraso para darle tiempo a Chrome a fallar
        });
    </script>

</body>
</html>