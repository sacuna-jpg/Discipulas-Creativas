<?php
/**
 * Configuración Estricta de Cabeceras CORS (Cross-Origin Resource Sharing)
 * Solo permite solicitudes desde orígenes explícitamente autorizados (Whitelist).
 */

require_once __DIR__ . '/../core/Response.php';

function handleCors() {
    // -------------------------------------------------------------
    // LISTA DE ORÍGENES / DOMINIOS PERMITIDOS (WHITELIST)
    // -------------------------------------------------------------
    $allowedOrigins = [
        'https://sacuna-jpg.github.io',
        'https://sacuna-jpg.github.io/Discipulas-Creativas',
        'http://localhost',
        'https://localhost',
        'http://127.0.0.1',
        'https://127.0.0.1',
        'http://apidc.xo.je',
        'https://apidc.xo.je'
    ];

    $origin = $_SERVER['HTTP_ORIGIN'] ?? null;

    // Si no hay cabecera Origin (peticiones directas, Postman, curl, servidores internos)
    if (!$origin) {
        // Permitir la solicitud pero sin cabeceras CORS de navegador
        return;
    }

    // Normalizar el origen recibido (remover puertos para comparación base si es localhost)
    $parsedOrigin = parse_url($origin);
    $originHost = $parsedOrigin['host'] ?? '';
    $originScheme = $parsedOrigin['scheme'] ?? 'http';
    $originBase = "{$originScheme}://{$originHost}";

    $isAllowed = false;

    // 1. Verificación directa contra la lista
    if (in_array($origin, $allowedOrigins, true)) {
        $isAllowed = true;
    } 
    // 2. Verificación de host base (útil para GitHub Pages o localhost con cualquier puerto ej: 3000, 5500, 8080)
    elseif (
        in_array($originBase, $allowedOrigins, true) || 
        $originHost === 'localhost' || 
        $originHost === '127.0.0.1' ||
        $originHost === ($_SERVER['HTTP_HOST'] ?? '') ||
        str_ends_with($originHost, '.github.io') ||
        str_ends_with($originHost, '.infinityfreeapp.com') ||
        str_ends_with($originHost, '.epizy.com') ||
        str_ends_with($originHost, '.rf.gd') ||
        str_ends_with($originHost, '.xo.je')
    ) {
        $isAllowed = true;
    }

    // Si el origen NO está autorizado, denegar la petición
    if (!$isAllowed) {
        Response::forbidden(
            "Acceso denegado por política de seguridad CORS. El origen '{$origin}' no está autorizado para consumir esta API.",
            'CORS_ORIGIN_DENIED'
        );
    }

    // Si está autorizado, emitir las cabeceras CORS correspondientes
    header("Access-Control-Allow-Origin: {$origin}");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Max-Age: 86400"); // Cache de preflight durante 24 horas
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, X-Auth-Token");

    // Responder exitosamente a las peticiones preflight OPTIONS del navegador
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}
