Modernización y Autonomía Digital: Proyecto Tecasis s.a.s. de c.v.

Este repositorio documenta el desarrollo integral del ecosistema web para Tecasis, realizado por Rubén Elí Durán Ramírez como proyecto de servicio social para el TSU en Desarrollo de Software en Código Abierto de la Escuela Superior de Innovación y Tecnología (ESIT).

 🎯 Objetivo del Proyecto
Migrar una infraestructura web estática hacia una plataforma dinámica, segura y autogestionable que elimine la dependencia técnica de la empresa y automatice la atención al cliente.

---

 🗓️ Cronología de Desarrollo (Servicio Social ESIT)

 Fase I: Arquitectura y Diseño (Semanas 1-4)
   Análisis: Levantamiento de requisitos y documentación de la infraestructura previa.
   Base de Datos: Diseño del modelo relacional `tecasis_tecasis_db` bajo motor InnoDB y codificación **utf8mb4** para soporte de caracteres especiales y emojis.
   UI/UX: Definición del estándar "Industrial Clean" y creación de wireframes responsivos.

 Fase II: Ingeniería de Software y Backend (Semanas 5-7)
   Seguridad Inicial: Configuración del entorno local y desarrollo del módulo de autenticación con cifrado Hash.
   Maquetación: Implementación semántica en HTML5 y CSS3 (Grid/Flexbox).
   PHP Dinámico: Conversión de la lógica estática a dinámica, permitiendo que los servicios se rendericen automáticamente desde la base de datos.

 Fase III: Automatización y Gestión (Semanas 8-10)
   Chatbot: Desarrollo de un asistente virtual flotante con JavaScript Vanilla y comunicación asíncrona vía API Fetch.
   Módulo CMS: Creación del panel administrativo para que la empresa gestione la sección "Nosotros" y el entrenamiento del bot sin programar.
   Auditoría: Registro automático de interacciones para el análisis de calidad del servicio.

 Fase IV: Calidad y Despliegue (Semanas 11-13)
   QA: Pruebas de compatibilidad cross-browser y optimización de usabilidad móvil.
   Producción: Despliegue en hosting Linux/cPanel y migración de base de datos local a servidor real.
   Seguridad Avanzada: Instalación de certificados SSL (HTTPS) y blindaje contra inyecciones SQL.

Fase V: Entrega y Blindaje (Semanas 14-16)
  Transferencia: Entrega de manuales de usuario y documentación técnica para administración autónoma.
  Diseño Final: Integración global del bot y ajuste visual de imágenes al 25% de escala.
  Hardening: Implementación de protección de credenciales mediante archivos **.env** fuera del alcance público.

---

 🛠️ Stack Tecnológico
   Core: PHP 8.x nativo y MySQL.
   Frontend: HTML5, CSS3, JavaScript (AJAX/Fetch).
   Servidor: Linux, cPanel, SSL.

---

 📢 NOTA DE VALOR AGREGADO (Post-16 Semanas)
Para: Lester Freeman Aguilar

Fuera de los requisitos académicos originales de la ESIT, se ha realizado una **actualización extraordinaria** de última milla para potenciar la autonomía y el motor de inteligencia de Tecasis:

1.  Motor de IA Generativa: Se migró el algoritmo básico de palabras clave a una integración avanzada con la API de Gemini 1.5 Flash, permitiendo que el asistente responda con lenguaje natural y técnico avanzado.
2.  Dashboard de Contacto Total: Se habilitaron nuevos campos en el panel administrativo para gestionar dinámicamente el teléfono, correo de ventas, horarios de atención y dirección física, sincronizándolos automáticamente con las respuestas de la IA y el pie de página del sitio.
3.  Sincronización de Interfaz: Se corrigió la alineación estructural del footer en `index.php`, asegurando que toda la información corporativa extraída de la base de datos sea visualmente perfecta en cualquier dispositivo.

Estado Actual: El sistema es ahora 100% independiente y cuenta con inteligencia artificial de última generación integrada directamente en el ecosistema corporativo.

---
Desarrollado por: Rubén Elí Durán Ramírez
Institución: ESIT 2025-2026
