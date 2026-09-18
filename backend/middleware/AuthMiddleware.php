<?php
/**
 * AuthMiddleware
 * Intercepta y valida el Bearer Token en peticiones a rutas protegidas.
 */

require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/Request.php';
require_once __DIR__ . '/../models/Sesion.php';

class AuthMiddleware {
    public function handle(Request $request): void {
        $token = $request->getBearerToken();

        if (empty($token)) {
            Response::unauthorized(
                'Token de autorización no proporcionado en la cabecera Authorization (Bearer).',
                'TOKEN_MISSING'
            );
        }

        $user = Sesion::findValidUserByToken($token);

        if (!$user) {
            Response::unauthorized(
                'Sesión inválida o expirada. Por favor inicia sesión nuevamente.',
                'TOKEN_INVALID_OR_EXPIRED'
            );
        }

        // Inyectar la usuaria autenticada en el objeto Request
        $request->user = $user;
    }
}
