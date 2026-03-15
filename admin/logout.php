<?php
// for logout, we just need to destroy the session and redirect to login.
session_start();
session_destroy();
header("Location: login.php");
exit();
?>