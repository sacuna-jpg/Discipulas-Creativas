# 🌸 Discípulas Creativas - Seminarios para Damas Adventistas

¡Bienvenida a la plataforma de **Discípulas Creativas 2026**! Este proyecto es un seminario interactivo diseñado especialmente para el Ministerio de la Mujer de la **Asociación Sur Andina (AsoSurAndina)** de la Iglesia Adventista del Séptimo Día.

Este sitio web permite a las damas registrarse con su distrito e iglesia local, ver los módulos de los seminarios en video y llevar un registro visual de su progreso conforme avanzan en su capacitación.

---

## 🚀 Características del Proyecto

*   **Diseño Elegante y Profesional:** Una interfaz adaptada con tipografías hermosas (*Fraunces*, *Karla* e *IBM Plex Mono*) y colores cálidos pensados especialmente para el proyecto.
*   **Soporte de Modo Oscuro (Dark Mode):** Detección automática del tema del dispositivo para una lectura cómoda de noche o de día.
*   **Registro Dinámico por Distritos:** El formulario filtra de forma inteligente las iglesias y congregaciones que pertenecen a cada distrito de la Asociación Sur Andina.
*   **Seguimiento de Progreso:** Una barra de progreso interactiva que se actualiza en tiempo real al marcar los módulos como "vistos".
*   **Persistencia Local:** Los datos de registro y progreso se guardan directamente en el navegador (`LocalStorage`), por lo que no se pierden al cerrar la página.
*   **11 Módulos en Video:** Acceso directo a cada seminario interactivo en YouTube.

---

## 🛠️ Tecnologías Utilizadas

*   **HTML5 & CSS3:** Maquetación limpia con un sistema de variables CSS personalizadas para el cambio de temas.
*   **JavaScript Puro (Vanilla JS):** Lógica interactiva nativa sin dependencias externas pesadas.
*   **YouTube API / Integración:** Enlaces directos a los videos de YouTube del seminario.
*   **HTML5 LocalStorage:** Guardado de progreso local en el navegador del usuario.

---

## 💻 Cómo Ejecutar el Proyecto Localmente

1. Descarga o clona este repositorio en tu computadora.
2. Abre el archivo `index.html` en cualquier navegador web (Chrome, Edge, Firefox, Safari).
3. ¡Listo! La plataforma funcionará de inmediato de manera local.

---

## 🔮 Próximos Pasos (Migración a LMS Moodle-like)

Para transformar este hermoso prototipo en una plataforma web completa multiusuario con persistencia real, planeamos implementar:
1. **Base de Datos MySQL:** Para guardar el progreso de los usuarios de forma permanente en la nube (independientemente del dispositivo que usen).
2. **Backend en PHP:** Para gestionar el registro, inicio de sesión (Login/Registro) de usuarios y la seguridad de la plataforma.
3. **Panel de Administración:** Una sección de control donde las administradoras del Ministerio de la Mujer puedan ver el progreso consolidado de todas las inscritas.
