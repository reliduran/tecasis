<?php
session_start();
require_once '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    // Buscamos el usuario
    $sql = "SELECT id_usuario, nombre_completo, password_hash FROM usuarios_admin WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        
        // VERIFICACIÓN DE CONTRASEÑA
        // Nota: Como insertamos la contraseña manual 'admin123_temporal' en SQL sin encriptar,
        // primero validamos si es esa, O si coincide con el hash seguro.
        
        if ($password === 'admin123_temporal' || password_verify($password, $row['password_hash'])) {
            // ¡Login Exitoso!
            $_SESSION['user_id'] = $row['id_usuario'];
            $_SESSION['user_name'] = $row['nombre_completo'];
            
            header("Location: dashboard.php");
            exit();
        }
    }
    
    // Si falla
    header("Location: login.php?error=1");
    exit();
}
?>