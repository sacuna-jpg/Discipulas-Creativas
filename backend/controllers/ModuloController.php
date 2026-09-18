<?php
/**
 * ModuloController
 * Endpoint para listar los 11 módulos del seminario con sus 2 preguntas de reflexión.
 * Protegido por AuthMiddleware (solo usuarias autenticadas).
 */

require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/Request.php';
require_once __DIR__ . '/../models/Modulo.php';

class ModuloController {
    public function listar(Request $request) {
        $modulos = Modulo::allWithPreguntas();
        Response::success($modulos, 'Catálogo de módulos cargado exitosamente.');
    }
}
