Modernización e Implementación de Presencia Web Empresarial - Tecasis
Este proyecto consiste en el desarrollo de un ecosistema web dinámico y autogestionable para la empresa Tecasis s.a.s de c.v.. El sistema migra la infraestructura estática previa hacia una plataforma moderna que integra un catálogo de servicios dinámico, un panel administrativo centralizado y un asistente virtual inteligente.
Realizado como proyecto de Servicio Social para el Técnico Superior Universitario en Desarrollo de Software en Código Abierto de la Escuela Superior de Innovación y Tecnología (ESIT).
Características Principales
•	Frontend Dinámico: Interfaz responsiva diseñada bajo el estándar "Industrial Clean" utilizando CSS Grid y Flexbox.
•	Gestor de Contenidos (CMS): Dashboard administrativo para la gestión autónoma de servicios (crear, editar, eliminar) y actualización de secciones corporativas sin necesidad de modificar el código fuente.
•	Asistente Virtual (Chatbot): Widget flotante desarrollado en Vanilla JavaScript que utiliza peticiones asíncronas (API Fetch) para brindar atención al cliente 24/7.
•	Módulo de Inteligencia: Algoritmo de keyword matching en PHP que permite al administrador "entrenar" al bot directamente desde el panel administrativo.
•	Auditoría y Control: Registro automático de interacciones en la base de datos para análisis de calidad y mejora continua de las respuestas del bot.
•	Gestión de Usuarios (RBAC): Sistema de control de acceso basado en roles (Administrador y Editor) con autenticación protegida.
Stack Tecnológico
•	Lenguaje de Servidor: PHP 8.x (Nativo).
•	Base de Datos: MySQL / MariaDB (Motor InnoDB).
•	Frontend: HTML5, CSS3, JavaScript (ES6+).
•	Seguridad: Cifrado de contraseñas con Bcrypt, sentencias preparadas para prevenir Inyección SQL y manejo de variables de entorno.
Seguridad y Hardening
El proyecto implementa capas críticas de seguridad para entornos de producción:
1.	Blindaje de Credenciales: Uso de archivos .env para aislar claves de conexión a la base de datos, excluidos del repositorio público mediante .gitignore.
2.	Protección de Sesiones: Configuración para regenerar IDs de sesión al autenticarse, evitando ataques de secuestro de sesión.
3.	Sanitización de Datos: Implementación de mysqli_real_escape_string y sentencias preparadas en todos los módulos de entrada de datos.
Estructura del Proyecto
•	/admin: Módulos de gestión, autenticación y dashboard administrativo.
•	/api: Lógica del motor del Chatbot y respuestas JSON.
•	/assets: Recursos estáticos (CSS, imágenes, scripts de cliente).
•	/config: Archivo central de conexión a la base de datos.
•	/includes: Componentes reutilizables del sitio (header, footer).
•	.env: Configuración de entorno (Protegido/No incluido en repositorio).
Desarrollador
•	Rubén Elí Durán Ramírez
•	TSU en Desarrollo de Software en Código Abierto - ESIT.
 
Nota: Este repositorio contiene el código fuente final validado y entregado a la empresa receptora el 13 de marzo de 2026.

<img width="468" height="644" alt="image" src="https://github.com/user-attachments/assets/f6c3bd52-fe36-4cef-a9f6-254ae0b2af38" />
