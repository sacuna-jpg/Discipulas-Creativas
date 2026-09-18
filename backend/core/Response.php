<?php
/**
 * Clase Response
 * Estandariza todas las respuestas JSON de la API REST incluyendo http_status_code explícito.
 */

class Response {
    public static function json($data = null, int $statusCode = 200, string $message = 'Operación exitosa', bool $success = true, ?string $errorCode = null) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');

        $payload = [
            'success'          => $success,
            'http_status_code' => $statusCode,
            'message'          => $message,
            'data'             => $data
        ];

        if (!$success && $errorCode !== null) {
            $payload['error_code'] = $errorCode;
        }

        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    public static function success($data = null, string $message = 'Operación exitosa', int $statusCode = 200) {
        self::json($data, $statusCode, $message, true);
    }

    public static function created($data = null, string $message = 'Recurso creado exitosamente') {
        self::json($data, 201, $message, true);
    }

    public static function badRequest(string $message = 'Datos de solicitud inválidos o incompletos', ?string $errorCode = 'BAD_REQUEST') {
        self::json(null, 400, $message, false, $errorCode);
    }

    public static function unauthorized(string $message = 'No autorizado o sesión inválida', ?string $errorCode = 'UNAUTHORIZED') {
        self::json(null, 401, $message, false, $errorCode);
    }

    public static function forbidden(string $message = 'Acceso denegado', ?string $errorCode = 'FORBIDDEN') {
        self::json(null, 403, $message, false, $errorCode);
    }

    public static function notFound(string $message = 'Recurso no encontrado', ?string $errorCode = 'NOT_FOUND') {
        self::json(null, 404, $message, false, $errorCode);
    }

    public static function unprocessable(string $message = 'Error de validación de campos', ?string $errorCode = 'VALIDATION_ERROR') {
        self::json(null, 422, $message, false, $errorCode);
    }

    public static function error(string $message = 'Error interno del servidor. Por favor intenta más tarde.', ?string $errorCode = 'INTERNAL_SERVER_ERROR') {
        self::json(null, 500, $message, false, $errorCode);
    }
}
