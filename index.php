<?php
require_once 'config/db.php';
$conn->set_charset("utf8");

// 1. CARGAR SECCIÓN NOSOTROS
$sql_nosotros = "SELECT * FROM seccion_nosotros WHERE id=1";
$res_nosotros = $conn->query($sql_nosotros);
$nosotros = $res_nosotros->fetch_assoc();

// 2. CARGAR SERVICIOS ACTIVOS
$sql_servicios = "SELECT * FROM servicios WHERE activo = 1";
$servicios = $conn->query($sql_servicios);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tecasis - Soluciones Tecnológicas</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                <li><a href="#">Inicio</a></li>
                <li><a href="#nosotros">Nosotros</a></li>
                <li><a href="#servicios">Servicios</a></li>
                <li><a href="#contacto">Contacto</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="hero">
            <h1><?php echo htmlspecialchars($nosotros['hero_titulo']); ?></h1>
            <p><?php echo htmlspecialchars($nosotros['hero_desc']); ?></p>
            <a href="#servicios" class="btn-primary"><?php echo htmlspecialchars($nosotros['hero_boton']); ?></a>
        </section>

        <!-- SECCIÓN SOBRE NOSOTROS (VERSIÓN FINAL DINÁMICA) -->
<!-- SECCIÓN SOBRE NOSOTROS (RESTAURADA QUIRÚRGICAMENTE) -->
<section id="nosotros" class="section-padding">
    <div class="container">
        <div class="about-grid">
            <div class="about-img">
                <?php 
                    // Validación para la imagen de "Nosotros"
                    // Usamos hero-bg.jpg como respaldo si el archivo de la DB no existe
                    $img_nosotros = (!empty($nosotros['imagen_path']) && file_exists($nosotros['imagen_path'])) 
                    ? $nosotros['imagen_path'] 
                    : 'assets/img/hero-bg.jpg'; 
                    ?>
                    <img src="<?php echo htmlspecialchars($img_nosotros); ?>" 
                    alt="Sobre Nosotros" 
                    style="width: 80%; height: auto; display: block; margin: 0 auto; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">

            </div>
            <div class="about-text">
                <span style="color: var(--accent); font-weight: 700; text-transform: uppercase;">
                    <?php echo htmlspecialchars($nosotros['subtitulo']); ?>
                </span>
                <h3><?php echo htmlspecialchars($nosotros['titulo']); ?></h3>
                <p><?php echo nl2br(htmlspecialchars($nosotros['descripcion'])); ?></p>
            </div>
        </div>
    </div>
</section>
        <section id="servicios" class="section-padding bg-light">
            <div class="container">
                <div class="section-title">
                    <span>NUESTROS SERVICIOS</span>
                    <h2>Soluciones a tu medida</h2>
                </div>
                
                <div class="grid-servicios">
                    <?php if ($servicios->num_rows > 0): ?>
                        <?php while($row = $servicios->fetch_assoc()): ?>
                            <a href="servicio.php?id=<?php echo $row['id_servicio']; ?>" class="card-link">
                                <div class="card-servicio">
                                    <div class="icono">
                                        <i class="fa-solid <?php echo htmlspecialchars($row['icono']); ?>"></i>
                                    </div>
                                    <h3><?php echo htmlspecialchars($row['titulo']); ?></h3>
                                    <p><?php echo substr(htmlspecialchars($row['descripcion']), 0, 100) . '...'; ?></p>
                                </div>
                            </a>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="text-align:center; width:100%;">No hay servicios activos.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="cta-section">
            <h2>¿Listo para optimizar tu tecnología?</h2>
            <p>Contáctanos hoy mismo para una evaluación gratuita.</p>
            <a href="#chatbot-widget" onclick="toggleChat()" class="btn-white">Hablar con Asistente</a>
        </section>

        <div id="chatbot-widget">
            <div id="chat-header" onclick="toggleChat()">
                <span>Asistente Virtual</span>
                <i class="fa-solid fa-chevron-up" id="chat-icon"></i>
            </div>
            <div id="chat-body">
                <div id="chat-messages">
                    <div class="bot-msg">Hola, bienvenido a Tecasis. ¿En qué te ayudo?</div>
                </div>
                <div id="chat-input-area">
                    <input type="text" id="user-input" placeholder="Pregunta algo..." onkeypress="handleEnter(event)">
                    <button onclick="sendMessage()"><i class="fa-solid fa-paper-plane"></i></button>
                </div>
            </div>
        </div>
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
                    <li><a href="#">Inicio</a></li>
                    <li><a href="#servicios">Servicios</a></li>
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

    <script src="assets/js/bot.js"></script>
</body>
</html>