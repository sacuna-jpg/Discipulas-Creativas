# 📜 Contratos y Especificación de la API REST - Discípulas Creativas

Este documento define formalmente la especificación y contratos de comunicación entre el frontend (GitHub Pages / Local) y el backend REST en PHP.

---

## 📐 Estándar Global de Respuestas JSON

Todas las respuestas de la API devuelven el encabezado `Content-Type: application/json; charset=utf-8` y comparten una estructura predecible:

### Respuesta Exitosa
```json
{
  "success": true,
  "http_status_code": 200,
  "message": "Descripción legible de la operación exitosa.",
  "data": { ... }
}
```

### Respuesta de Error de Validación o Cliente (4xx)
```json
{
  "success": false,
  "http_status_code": 400,
  "message": "Mensaje comprensible y amigable para mostrar al usuario.",
  "data": null,
  "error_code": "CODIGO_ERROR_SEMANTICO"
}
```

### Respuesta de Error Interno del Servidor (500)
*Las excepciones y trazas técnicas **nunca** se envían al cliente. Quedan registradas de forma confidencial en `backend/logs/app.log`.*
```json
{
  "success": false,
  "http_status_code": 500,
  "message": "Ocurrió un error inesperado en el servidor. Por favor intenta más tarde.",
  "data": null,
  "error_code": "INTERNAL_SERVER_ERROR"
}
```

---

## 🌐 Política de Seguridad CORS (Whitelist de Orígenes Autorizados)

Para prevenir ataques de falsificación y consumos indebidos de terceros, la API valida el origen (`Origin`) de cada petición. Solo se admiten los siguientes dominios:

1. **Producción (GitHub Pages):**
   * `https://sacuna-jpg.github.io`
   * `https://sacuna-jpg.github.io/Discipulas-Creativas`
2. **Desarrollo Local:**
   * `http://localhost` (admite cualquier puerto como `:3000`, `:5500`, `:8080`)
   * `https://localhost`
   * `http://127.0.0.1` / `https://127.0.0.1`
3. **Herramientas de Testing (Postman / cURL / CLI):**
   * Al no enviar cabecera de navegador `Origin`, tienen acceso directo permitido.

> **Respuesta en caso de origen no autorizado (`403 Forbidden`):**
> ```json
> {
>   "success": false,
>   "http_status_code": 403,
>   "message": "Acceso denegado por política de seguridad CORS. El origen 'https://malicioso.com' no está autorizado para consumir esta API.",
>   "data": null,
>   "error_code": "CORS_ORIGIN_DENIED"
> }
> ```

---
## 🔐 Mecanismo de Autenticación (Bearer Token)

Para consumir cualquier endpoint protegido, el cliente debe incluir el token devuelto en el Login o Registro dentro de las cabeceras HTTP:

```http
Authorization: Bearer <token_de_64_caracteres>
```
*(Alternativa admitida por el router: cabecera `X-Auth-Token: <token>`)*

Si el token no se envía o ha expirado, el servidor responderá con código `HTTP 401 Unauthorized`.

---

## 📋 Catálogo Detallado de Endpoints

### 1. `GET /api/distritos`
* **Acceso:** Público (Sin autenticación).
* **Propósito:** Obtener la lista completa de distritos regionales misioneros para el formulario de registro.
* **Headers:** `Accept: application/json`
* **Payload:** Ninguno.
* **Respuesta Exitosa (`200 OK`):**
  ```json
  {
    "success": true,
    "http_status_code": 200,
    "message": "Listado de distritos obtenido correctamente.",
    "data": [
      { "id": 1, "nombre": "Algeciras" },
      { "id": 2, "nombre": "Campo Alegre" },
      { "id": 8, "nombre": "Neiva Central" }
    ]
  }
  ```

---

### 2. `GET /api/iglesias`
* **Acceso:** Público (Sin autenticación).
* **Propósito:** Obtener iglesias, con opción de filtrar por distrito para el select dinámico.
* **Query Params:**
  * `distrito_id` *(Opcional, Entero)*: ID del distrito seleccionado.
* **Respuesta Exitosa (`200 OK`):**
  ```json
  {
    "success": true,
    "http_status_code": 200,
    "message": "Iglesias del distrito #8 obtenidas correctamente.",
    "data": [
      { "id": 14, "distrito_id": 8, "nombre": "Iglesia Neiva Central" },
      { "id": 15, "distrito_id": 8, "nombre": "Grupo Roca Eterna" },
      { "id": 16, "distrito_id": 8, "nombre": "Iglesia AMAS CABI" }
    ]
  }
  ```

---

### 3. `POST /api/auth/register`
* **Acceso:** Público (Sin autenticación).
* **Propósito:** Registro de una nueva alumna. Inicia sesión automáticamente devolviendo su sesión y token.
* **Headers:** `Content-Type: application/json`
* **Payload (Body):**
  ```json
  {
    "nombre": "Marta Gómez",
    "correo": "marta@ejemplo.com",
    "password": "miPasswordSeguro123",
    "distrito_id": 8,
    "iglesia_id": 14
  }
  ```
* **Respuesta Exitosa (`201 Created`):**
  ```json
  {
    "success": true,
    "http_status_code": 201,
    "message": "¡Registro exitoso! Bienvenida a Discípulas Creativas.",
    "data": {
      "token": "7a8b9c0d1e2f3a4b5c6d7e8f90123456789abcdef0123456789abcdef0123456",
      "usuario": {
        "id": 1,
        "nombre": "Marta Gómez",
        "correo": "marta@ejemplo.com",
        "distrito_id": 8,
        "iglesia_id": 14,
        "distrito_nombre": "Neiva Central",
        "iglesia_nombre": "Iglesia Neiva Central",
        "creado_at": "2026-09-17 10:00:00"
      }
    }
  }
  ```
* **Errores Posibles:**
  * `422 Unprocessable Entity`: Correo duplicado (`EMAIL_ALREADY_EXISTS`), contraseña menor a 6 caracteres (`PASSWORD_TOO_SHORT`) o correo inválido (`INVALID_EMAIL`).

---

### 4. `POST /api/auth/login`
* **Acceso:** Público (Sin autenticación).
* **Propósito:** Iniciar sesión con correo y contraseña. Genera y guarda un nuevo token en la tabla `sesiones`.
* **Headers:** `Content-Type: application/json`
* **Payload (Body):**
  ```json
  {
    "correo": "marta@ejemplo.com",
    "password": "miPasswordSeguro123"
  }
  ```
* **Respuesta Exitosa (`200 OK`):**
  ```json
  {
    "success": true,
    "http_status_code": 200,
    "message": "Inicio de sesión exitoso.",
    "data": {
      "token": "7a8b9c0d1e2f3a4b5c6d7e8f90123456789abcdef0123456789abcdef0123456",
      "usuario": {
        "id": 1,
        "nombre": "Marta Gómez",
        "correo": "marta@ejemplo.com",
        "distrito_id": 8,
        "iglesia_id": 14,
        "distrito_nombre": "Neiva Central",
        "iglesia_nombre": "Iglesia Neiva Central"
      }
    }
  }
  ```
* **Errores Posibles:**
  * `401 Unauthorized`: Credenciales inválidas (`INVALID_CREDENTIALS`).
  * `400 Bad Request`: Campos vacíos (`CREDENTIALS_REQUIRED`).

---

### 5. `GET /api/auth/me`
* **Acceso:** **PROTEGIDO** (Requiere Bearer Token).
* **Propósito:** Obtener los datos del perfil activo a partir del token.
* **Headers:** `Authorization: Bearer <token>`
* **Respuesta Exitosa (`200 OK`):**
  ```json
  {
    "success": true,
    "http_status_code": 200,
    "message": "Perfil de usuaria obtenido correctamente.",
    "data": {
      "id": 1,
      "nombre": "Marta Gómez",
      "correo": "marta@ejemplo.com",
      "distrito_nombre": "Neiva Central",
      "iglesia_nombre": "Iglesia Neiva Central"
    }
  }
  ```

---

### 6. `POST /api/auth/logout`
* **Acceso:** **PROTEGIDO** (Requiere Bearer Token).
* **Propósito:** Cerrar sesión y destruir el token en la base de datos.
* **Headers:** `Authorization: Bearer <token>`
* **Payload:** Ninguno.
* **Respuesta Exitosa (`200 OK`):**
  ```json
  {
    "success": true,
    "http_status_code": 200,
    "message": "Sesión cerrada correctamente.",
    "data": null
  }
  ```

---

### 7. `POST /api/auth/change-password`
* **Acceso:** **PROTEGIDO** (Requiere Bearer Token).
* **Propósito:** Cambiar contraseña actual.
* **Headers:** `Authorization: Bearer <token>`, `Content-Type: application/json`
* **Payload (Body):**
  ```json
  {
    "password_actual": "miPasswordSeguro123",
    "password_nueva": "nuevaClaveSuperSegura456"
  }
  ```
* **Respuesta Exitosa (`200 OK`):**
  ```json
  {
    "success": true,
    "http_status_code": 200,
    "message": "Tu contraseña ha sido actualizada con éxito.",
    "data": null
  }
  ```

---

### 8. `GET /api/modulos`
* **Acceso:** **PROTEGIDO** (Requiere Bearer Token).
* **Propósito:** Obtener el catálogo oficial de los 11 módulos con sus 2 preguntas de reflexión.
* **Headers:** `Authorization: Bearer <token>`
* **Respuesta Exitosa (`200 OK`):**
  ```json
  {
    "success": true,
    "http_status_code": 200,
    "message": "Catálogo de módulos cargado exitosamente.",
    "data": [
      {
        "id": 1,
        "numero": 1,
        "titulo": "Módulo 1: Introducción",
        "descripcion": "Bienvenida al seminario de Discípulas Creativas...",
        "youtube_id": "GTAE-9enobE",
        "duracion": "Seminario en Video",
        "preguntas": [
          {
            "id": 1,
            "orden": 1,
            "enunciado": "¿A quién animé esta semana?"
          },
          {
            "id": 2,
            "orden": 2,
            "enunciado": "¿Con quién me conecté a través de algo que compartí?"
          }
        ]
      }
    ]
  }
  ```

---

### 9. `GET /api/progreso`
* **Acceso:** **PROTEGIDO** (Requiere Bearer Token).
* **Propósito:** Obtener los módulos completados por la alumna, su porcentaje total y sus reflexiones guardadas.
* **Headers:** `Authorization: Bearer <token>`
* **Respuesta Exitosa (`200 OK`):**
  ```json
  {
    "success": true,
    "http_status_code": 200,
    "message": "Progreso de la alumna obtenido exitosamente.",
    "data": {
      "completados_ids": [1, 2],
      "total_completados": 2,
      "total_modulos": 11,
      "porcentaje": 18,
      "certificado_listo": false,
      "respuestas": {
        "1": [
          {
            "pregunta_id": 1,
            "respuesta": "Animé a mi hermana de iglesia con una llamada.",
            "enviado_at": "2026-09-17 10:15:00"
          },
          {
            "pregunta_id": 2,
            "respuesta": "Le compartí una imagen de esperanza a mi vecina.",
            "enviado_at": "2026-09-17 10:15:00"
          }
        ]
      }
    }
  }
  ```

---

### 10. `POST /api/progreso/completar`
* **Acceso:** **PROTEGIDO** (Requiere Bearer Token).
* **Propósito:** Enviar las respuestas de reflexión de un módulo y marcarlo como completado en una sola transacción.
* **Headers:** `Authorization: Bearer <token>`, `Content-Type: application/json`
* **Payload (Body):**
  ```json
  {
    "modulo_id": 1,
    "respuestas": [
      {
        "pregunta_id": 1,
        "texto": "Animé a mi compañera de trabajo en un momento difícil."
      },
      {
        "pregunta_id": 2,
        "texto": "Compartí el versículo del día en mis redes sociales."
      }
    ]
  }
  ```
* **Respuesta Exitosa (`200 OK`):**
  ```json
  {
    "success": true,
    "http_status_code": 200,
    "message": "¡Módulo #1 completado y reflexiones guardadas con éxito!",
    "data": {
      "completados_ids": [1],
      "total_completados": 1,
      "total_modulos": 11,
      "porcentaje": 9,
      "certificado_listo": false,
      "respuestas": { ... }
    }
  }
  ```

---

### 11. `POST /api/progreso/toggle`
* **Acceso:** **PROTEGIDO** (Requiere Bearer Token).
* **Propósito:** Alternar manualmente el estado de visto/no visto de un módulo.
* **Headers:** `Authorization: Bearer <token>`, `Content-Type: application/json`
* **Payload (Body):**
  ```json
  {
    "modulo_id": 1,
    "completado": false
  }
  ```
* **Respuesta Exitosa (`200 OK`):**
  ```json
  {
    "success": true,
    "http_status_code": 200,
    "message": "Módulo #1 desmarcado como visto.",
    "data": { ... }
  }
  ```
