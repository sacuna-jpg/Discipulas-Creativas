# Plan de Implementación de APIs y Base de Datos

Este documento detalla el diseño completo de la arquitectura de backend, la base de datos MySQL normalizada con las 7 tablas del sistema (incluyendo reflexiones y preguntas) y los endpoints REST para la plataforma **Discípulas Creativas**.

---

## 🗄️ Esquema Completo de la Base de Datos (8 Tablas)

A continuación se detalla la estructura relacional completa:

```
[distritos] 1 ──── N [iglesias]
                          │ 1
                          │ N
[usuarios] 1 ─────────────┘
    │ 1
    ├─────── N [sesiones] (Tokens de autenticación API)
    │
    │ 1
    │ N
    ├─────── N [respuestas] N ────── 1 [preguntas]
    │                                      │ N
    │ N                                    │ 1
    └─────── N [progreso]   N ────── 1 [modulos]
```

### 1. Tabla: `distritos`
Catálogo de distritos misioneros regionales de la Asociación Sur Andina.
*   `id` (INT, Primary Key, Auto Increment)
*   `nombre` (VARCHAR(150), NOT NULL, UNIQUE) — Ej: "Neiva Central", "Pitalito Sur".
*   `creado_at` (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)

### 2. Tabla: `iglesias`
Catálogo de congregaciones vinculadas a cada distrito.
*   `id` (INT, Primary Key, Auto Increment)
*   `distrito_id` (INT, Foreign Key -> `distritos.id`, ON DELETE CASCADE)
*   `nombre` (VARCHAR(150), NOT NULL) — Ej: "Iglesia Central Neiva".
*   `creado_at` (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)

### 3. Tabla: `usuarios`
Cuentas de acceso de las alumnas/hermanas inscritas.
*   `id` (INT, Primary Key, Auto Increment)
*   `nombre` (VARCHAR(150), NOT NULL)
*   `correo` (VARCHAR(150), NOT NULL, UNIQUE)
*   `password_hash` (VARCHAR(255), NOT NULL) — Generado con `password_hash($pass, PASSWORD_DEFAULT)`.
*   `distrito_id` (INT, Foreign Key -> `distritos.id`, ON DELETE SET NULL)
*   `iglesia_id` (INT, Foreign Key -> `iglesias.id`, ON DELETE SET NULL)
*   `creado_at` (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)

### 4. Tabla: `modulos`
Catálogo oficial de los 11 módulos del seminario en video.
*   `id` (INT, Primary Key, Auto Increment)
*   `numero` (INT, NOT NULL, UNIQUE) — Número de orden (1 al 11).
*   `titulo` (VARCHAR(200), NOT NULL) — Ej: "Módulo 1: Introducción".
*   `descripcion` (TEXT, NOT NULL) — Sinopsis descriptiva del contenido.
*   `youtube_id` (VARCHAR(50), NOT NULL) — ID único del video de YouTube (ej. `GTAE-9enobE`).
*   `duracion` (VARCHAR(50), DEFAULT 'Seminario en Video')
*   `activo` (TINYINT(1), DEFAULT 1)

### 5. Tabla: `preguntas`
Las 2 preguntas de reflexión pedagógica/espiritual asociadas a cada módulo.
*   `id` (INT, Primary Key, Auto Increment)
*   `modulo_id` (INT, Foreign Key -> `modulos.id`, ON DELETE CASCADE)
*   `orden` (INT, NOT NULL) — 1 o 2 (orden de visualización en la interfaz).
*   `enunciado` (TEXT, NOT NULL) — Ej: "¿A quién animé esta semana?", "¿Con quién me conecté a través de algo que compartí?".

### 6. Tabla: `respuestas`
Registro de las reflexiones personales escritas por cada usuaria en los módulos.
*   `id` (INT, Primary Key, Auto Increment)
*   `usuario_id` (INT, Foreign Key -> `usuarios.id`, ON DELETE CASCADE)
*   `pregunta_id` (INT, Foreign Key -> `preguntas.id`, ON DELETE CASCADE)
*   `respuesta` (TEXT, NOT NULL) — Texto redactado en el área de reflexión.
*   `enviado_at` (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)
*   *Restricción Única:* `UNIQUE KEY (usuario_id, pregunta_id)` (permite actualizar la reflexión si la usuaria edita su respuesta).

### 7. Tabla: `progreso`
Control de avance y estado de certificación por usuaria.
*   `usuario_id` (INT, Foreign Key -> `usuarios.id`, ON DELETE CASCADE)
*   `modulo_id` (INT, Foreign Key -> `modulos.id`, ON DELETE CASCADE)
*   `completado_at` (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
*   *Clave Primaria Compuesta:* `PRIMARY KEY (usuario_id, modulo_id)`

### 8. Tabla: `sesiones` (Tokens de Autenticación REST)
Control de sesiones activas, tokens de seguridad e inicio de sesión multidispositivo.
*   `id` (INT, Primary Key, Auto Increment)
*   `usuario_id` (INT, Foreign Key -> `usuarios.id`, ON DELETE CASCADE)
*   `token` (VARCHAR(128), NOT NULL, UNIQUE) — Token aleatorio criptoseguro generado en el login.
*   `ip_address` (VARCHAR(45), NULL) — Dirección IP de la conexión.
*   `user_agent` (TEXT, NULL) — Navegador y dispositivo de la alumna.
*   `expira_at` (DATETIME, NOT NULL) — Fecha y hora de expiración (ej. 30 días de inactividad).
*   `creado_at` (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
*   `ultimo_acceso` (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)

---

## 🗺️ Arquitectura de Endpoints REST

*   **`GET /api/distritos`**: Lista todos los distritos.
*   **`GET /api/iglesias?distrito_id={id}`**: Lista iglesias filtradas por distrito.
*   **`POST /api/auth/register`**: Registra alumna con validación de correo único.
*   **`POST /api/auth/login`**: Valida credenciales y genera sesión/token.
*   **`POST /api/auth/logout`**: Cierra la sesión en el servidor.
*   **`POST /api/auth/change-password`**: Actualiza contraseña validando la anterior.
*   **`GET /api/modulos`**: Retorna los 11 módulos con sus respectivas preguntas de reflexión.
*   **`GET /api/progreso`**: Retorna módulos completados por la usuaria autenticada y sus respuestas previas.
*   **`POST /api/progreso/completar`**: Guarda las respuestas a las 2 reflexiones y marca el módulo como visto.
