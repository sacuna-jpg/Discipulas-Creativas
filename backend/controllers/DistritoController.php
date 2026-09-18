<?php
/**
 * DistritoController
 * Endpoints públicos para catálogos de distritos e iglesias.
 */

require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/Request.php';
require_once __DIR__ . '/../models/Distrito.php';
require_once __DIR__ . '/../models/Iglesia.php';

class DistritoController {
    public function listarDistritos(Request $request) {
        $distritos = Distrito::all();
        Response::success($distritos, 'Listado de distritos obtenido correctamente.');
    }

    public function listarIglesias(Request $request) {
        $distritoId = $request->query('distrito_id');

        if ($distritoId !== null && is_numeric($distritoId)) {
            $iglesias = Iglesia::getByDistrito((int) $distritoId);
            Response::success($iglesias, "Iglesias del distrito #{$distritoId} obtenidas correctamente.");
        } else {
            $iglesias = Iglesia::all();
            Response::success($iglesias, 'Listado general de iglesias obtenido correctamente.');
        }
    }
}
