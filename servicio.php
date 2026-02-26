<?php
require_once 'config/db.php';
$conn->set_charset("utf8");

// Validamos que venga un ID numérico
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = intval($_GET['id']);
$sql = "SELECT * FROM servicios WHERE id_servicio = $id";
$resultado = $conn->query($sql);

// Si no existe el servicio, volvemos al inicio
if ($resultado->num_rows == 0) {
    header("Location: index.php");
    exit();
}

$servicio = $resultado->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($servicio['titulo']); ?> - Tecasis</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Estilos específicos para esta página interna */
        .page-header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: white;
            padding: 4rem 2rem;
            text-align: center;
        }
        .service-content {
            max-width: 900px;
            margin: 4rem auto;
            padding: 0 2rem;
            min-height: 400px;
        }
        .service-content h2, .service-content h3 { color: #0f172a; margin-top: 1.5rem; }
        .service-content p { color: #334155; line-height: 1.8; margin-bottom: 1rem; }
        .service-content ul { margin-left: 20px; margin-bottom: 1rem; }
        .back-btn {
            display: inline-block; margin-top: 2rem; 
            color: #64748b; text-decoration: none; font-weight: 600;
        }
        .back-btn:hover { color: #2563eb; }
    </style>
</head>
<body>

    <header>
        <div class="logo">
            <a href="index.php">
                <img src="assets/img/logo.jpg" alt="Tecasis">
              
            </a>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Inicio</a></li>
                <li><a href="index.php#nosotros">Nosotros</a></li>
                <li><a href="index.php#servicios">Servicios</a></li>
                <li><a href="index.php#contacto">Contacto</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="page-header">
            <div style="font-size: 3rem; color: #2563eb; margin-bottom: 1rem;">
                <i class="fa-solid <?php echo htmlspecialchars($servicio['icono']); ?>"></i>
            </div>
            <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($servicio['titulo']); ?></h1>
             <?php if (!empty($servicio['imagen_path']) && file_exists($servicio['imagen_path'])): ?>
                <div class="servicio-banner" style="margin-bottom: 30px; text-align: center;">
                <img src="<?php echo htmlspecialchars($servicio['imagen_path']); ?>" 
                    alt="<?php echo htmlspecialchars($servicio['titulo']); ?>" 
                    style="max-width: 80%; height: auto; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); object-fit: cover; max-height: 400px;">
            </div>
            <?php endif; ?>
            <p style="opacity: 0.8; font-size: 1.1rem;">Detalles y características del servicio</p>
        </section>

        <section class="service-content">
            <?php 
            if (!empty($servicio['contenido_html'])) {
                echo $servicio['contenido_html']; 
            } else {
                echo "<p>" . htmlspecialchars($servicio['descripcion']) . "</p>";
                echo "<p><em>Próximamente agregaremos más detalles sobre este servicio. Contáctanos para más información.</em></p>";
            }
            ?>

            <div style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid #e2e8f0;">
                <a href="index.php#servicios" class="back-btn">&larr; Volver a todos los servicios</a>
                <a href="#contacto" class="btn-primary" style="float: right; padding: 10px 25px; font-size: 0.9rem;">Cotizar este servicio</a>
            </div>
        </section>

        <section class="cta-section">
            <h2>¿Necesitas ayuda personalizada?</h2>
            <p>Nuestro equipo está listo para atenderte.</p>
            <a href="javascript:void(0);" onclick="toggleChat()" class="btn btn-white">Hablar con Soporte</a>

        </section>
    </main>

    <footer id="contacto">
        <div class="footer-grid">
            <div class="footer-col">
                <h4>TECASIS</h4>
                <p>Tu socio tecnológico de confianza en el Estado de México.</p>
            </div>
            <div class="footer-col">
                <h4>Menú</h4>
                <ul>
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="index.php#servicios">Servicios</a></li>
                    <li><a href="admin/login.php">Zona Admin</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Contacto</h4>
                <ul>
                    <li><i class="fa-solid fa-phone"></i> +52 55 1234 5678</li>
                    <li><i class="fa-solid fa-envelope"></i> contacto@tecasis.com</li>
                </ul>
            </div>
        </div>
        <div style="text-align: center; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px;">
            <p>&copy; 2025 Tecasis S.A.S de C.V.</p>
        </div>
    </footer>

    <!-- Widget del Chatbot (Requerimiento RF-03) -->
<div id="chatbot-widget">
    <div id="chat-header" onclick="toggleChat()">
        <span>Asistente Virtual</span>
        <i id="chat-icon" class="fa-solid fa-chevron-up"></i>
    </div>
    <div id="chat-body" style="display: none; flex-direction: column;">
        <div id="chat-messages">
            <div class="bot-msg">Hola, bienvenido a Tecasis. ¿En qué te ayudo?</div>
        </div>
        <div id="chat-input-area">
            <input type="text" id="user-input" placeholder="Pregunta algo..." onkeypress="handleEnter(event)">
            <button onclick="sendMessage()"><i class="fa-solid fa-paper-plane"></i></button>
        </div>
    </div>
</div>
<!-- Incluir el Widget del Chatbot (puedes crear un include para esto) -->
<?php include 'includes/chatbot_widget.php'; ?> 

<!-- Cargar el script de la lógica del bot -->
<script src="assets/js/bot.js"></script>
</body>
</html>