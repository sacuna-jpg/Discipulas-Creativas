<?php
/**
 * ==========================================================
 * Discípulas Creativas - API REST (Front Controller)
 * ==========================================================
 */

// 1. Manejo de Errores Silencioso para Producción (No filtrar errores PHP al frontend)
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');

// 2. Cabeceras CORS
require_once __DIR__ . '/config/cors.php';
handleCors();

// 3. Core
require_once __DIR__ . '/core/Logger.php';
require_once __DIR__ . '/core/Response.php';
require_once __DIR__ . '/core/Request.php';
require_once __DIR__ . '/core/Router.php';
require_once __DIR__ . '/core/Database.php';

// 4. Middlewares
require_once __DIR__ . '/middleware/AuthMiddleware.php';

// 5. Controladores
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/DistritoController.php';
require_once __DIR__ . '/controllers/ModuloController.php';
require_once __DIR__ . '/controllers/ProgresoController.php';

// 6. Instanciar Request y Router
$request = new Request();
$router = new Router();

// ==========================================================
// DEFINICIÓN DE RUTAS REST
// ==========================================================

// --- Rutas Públicas (Sin Autenticación) ---
$router->get('/api/distritos', [DistritoController::class, 'listarDistritos']);
$router->get('/api/iglesias', [DistritoController::class, 'listarIglesias']);
$router->post('/api/auth/register', [AuthController::class, 'register']);
$router->post('/api/auth/login', [AuthController::class, 'login']);

// --- Rutas Protegidas (Requieren AuthMiddleware con Bearer Token) ---
$router->get('/api/auth/me', [AuthController::class, 'me'], [AuthMiddleware::class]);
$router->post('/api/auth/logout', [AuthController::class, 'logout'], [AuthMiddleware::class]);
$router->post('/api/auth/change-password', [AuthController::class, 'changePassword'], [AuthMiddleware::class]);

// Módulos: PROTEGIDO (Requiere estar logueada para ver los 11 videos y reflexiones)
$router->get('/api/modulos', [ModuloController::class, 'listar'], [AuthMiddleware::class]);

// Progreso y Reflexiones: PROTEGIDO
$router->get('/api/progreso', [ProgresoController::class, 'obtenerProgreso'], [AuthMiddleware::class]);
$router->post('/api/progreso/completar', [ProgresoController::class, 'completar'], [AuthMiddleware::class]);
$router->post('/api/progreso/toggle', [ProgresoController::class, 'toggle'], [AuthMiddleware::class]);

// Ruta raíz de verificación de salud de la API
$router->get('/api', function() {
    Response::success([
        'api'     => 'Discípulas Creativas REST API',
        'version' => '1.0.0',
        'status'  => 'online'
    ], 'API REST en línea y funcionando.');
});

// Despachar petición
$router->dispatch($request);
