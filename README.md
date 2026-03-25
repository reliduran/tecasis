Modernización e Implementación de Presencia Web Empresarial - Tecasis

Este proyecto representa la transición de una infraestructura web estática previa hacia un ecosistema dinámico y autogestionable desarrollado para la empresa Tecasis s.a.s de c.v.. El sistema fue diseñado para permitir la administración autónoma de contenidos y la atención automatizada de clientes mediante tecnologías de código abierto. Se realizó como proyecto de Servicio Social para el Técnico Superior Universitario en Desarrollo de Software en Código Abierto de la Escuela Superior de Innovación y Tecnología (ESIT).

Características del Sistema

   Interfaz Dinámica y Responsiva: El frontend se adapta automáticamente a dispositivos móviles, tabletas y escritorio utilizando **CSS Grid y Flexbox.
   Gestor de Contenidos (CMS): Panel administrativo centralizado que permite agregar, editar o desactivar servicios y textos corporativos sin modificar el código fuente.
   Asistente Virtual (Chatbot): Widget flotante que ofrece atención al cliente 24/7, procesando mensajes de forma asíncrona sin recargar la página.
   Módulo de Entrenamiento: Interfaz en el dashboard para que el administrador enseñe nuevas palabras clave y respuestas al chatbot directamente en la base de datos.
   Auditoría de Interacciones: Registro automático de todas las consultas realizadas por los usuarios para análisis de calidad y mejora continua.
   Control de Acceso (RBAC): Sistema de seguridad con roles de Administrador y Editor para restringir las funciones según el perfil del usuario.

 Stack Tecnológico

   Lenguaje de Servidor: PHP 8.x Nativo, seleccionado para maximizar el rendimiento sin depender de frameworks pesados.
   Base de Datos: MySQL / MariaDB utilizando el motor InnoDB para integridad transaccional y cotejamiento utf8mb4_unicode_ci para soporte de emojis y tildes.
   Frontend: Estructura semántica en HTML5, estilos visuales en CSS3 con variables personalizadas y lógica de cliente en Vanilla JavaScript.
   Entorno y Despliegue: Desarrollo realizado en Visual Studio Code con entorno local MAMP y despliegue final mediante cPanel

Seguridad y Hardening

   Blindaje de Credenciales: Las claves de conexión se encuentran aisladas en un archivo externo .env, el cual está excluido del repositorio público mediante .ignore.
   Protección de Sesiones: El sistema regenera automáticamente el ID de sesión al autenticarse para prevenir ataques de secuestro de sesión.
   Cifrado de Contraseñas: Uso del algoritmo Bcrypt para asegurar que ninguna credencial administrativa se almacene en texto plano.
   Sanitización de Datos: Implementación de sentencias preparadas y funciones de escape para mitigar ataques de Inyección SQL en todos los módulos de entrada.

Estructura del Proyecto

   /admin: Módulos de autenticación, gestión de servicios y panel administrativo principal.
   /api: Motor lógico del chatbot y procesamiento de respuestas en formato JSON.
   /assets: Recursos estáticos del sitio incluyendo hojas de estilo CSS, scripts JS y librerías de iconos.
   /config: Configuración centralizada de la conexión a la base de datos.
   /includes: Componentes de interfaz reutilizables como encabezados y pies de página.

Desarrollador

   Rubén Elí Durán Ramírez**.
   TSU en Desarrollo de Software en Código Abierto - ESIT.

Fecha de finalización técnica: 13 de marzo de 2026.
