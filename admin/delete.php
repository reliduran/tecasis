<?php
session_start();
require_once '../config/db.php';

// Seguridad: Solo admin logueado puede borrar
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Opción A: Borrado Físico (Desaparece de la BD)
    $sql = "DELETE FROM servicios WHERE id_servicio = $id";
    
    // Opción B (Mejor): Borrado Lógico (Solo se oculta)
    // $sql = "UPDATE servicios SET activo = 0 WHERE id_servicio = $id";

    if ($conn->query($sql)) {
        header("Location: dashboard.php?msg=borrado");
    } else {
        echo "Error al eliminar: " . $conn->error;
    }
}
?>