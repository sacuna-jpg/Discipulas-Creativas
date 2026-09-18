<?php
/**
 * ProgresoController
 * Control de avance de la alumna, guardado de reflexiones y certificación.
 * Protegido por AuthMiddleware.
 */

require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/Request.php';
require_once __DIR__ . '/../models/Progreso.php';
require_once __DIR__ . '/../models/Respuesta.php';
require_once __DIR__ . '/../models/Modulo.php';

class ProgresoController {

    public function obtenerProgreso(Request $request) {
        $usuarioId = (int) $request->user['id'];
        $progreso = Progreso::getUserProgress($usuarioId);

        Response::success($progreso, 'Progreso de la alumna obtenido exitosamente.');
    }

    public function completar(Request $request) {
        $usuarioId = (int) $request->user['id'];
        $moduloId = $request->input('modulo_id');
        $respuestas = $request->input('respuestas', []);

        if (empty($moduloId) || !is_numeric($moduloId)) {
            Response::badRequest('El identificador del módulo (modulo_id) es requerido.', 'MODULE_ID_REQUIRED');
        }

        $moduloId = (int) $moduloId;
        $modulo = Modulo::findById($moduloId);
        if (!$modulo) {
            Response::notFound("El módulo #{$moduloId} no existe o no está disponible.", 'MODULE_NOT_FOUND');
        }

        // Guardar reflexiones si se proporcionaron
        if (is_array($respuestas)) {
            foreach ($respuestas as $resp) {
                $preguntaId = $resp['pregunta_id'] ?? null;
                $texto = $resp['texto'] ?? $resp['respuesta'] ?? '';

                if ($preguntaId && is_numeric($preguntaId) && trim($texto) !== '') {
                    Respuesta::saveOrUpdate($usuarioId, (int) $preguntaId, trim($texto));
                }
            }
        }

        // Marcar módulo como completado
        Progreso::markCompleted($usuarioId, $moduloId);

        // Devolver progreso actualizado
        $progresoActualizado = Progreso::getUserProgress($usuarioId);

        Response::success($progresoActualizado, "¡Módulo #{$moduloId} completado y reflexiones guardadas con éxito!");
    }

    public function toggle(Request $request) {
        $usuarioId = (int) $request->user['id'];
        $moduloId = $request->input('modulo_id');
        $completado = $request->input('completado');

        if (empty($moduloId) || !is_numeric($moduloId)) {
            Response::badRequest('El identificador del módulo (modulo_id) es requerido.', 'MODULE_ID_REQUIRED');
        }

        $moduloId = (int) $moduloId;

        if ($completado === false || $completado === 0 || $completado === '0' || $completado === 'false') {
            Progreso::markIncomplete($usuarioId, $moduloId);
            $mensaje = "Módulo #{$moduloId} desmarcado como visto.";
        } else {
            Progreso::markCompleted($usuarioId, $moduloId);
            $mensaje = "Módulo #{$moduloId} marcado como visto.";
        }

        $progresoActualizado = Progreso::getUserProgress($usuarioId);
        Response::success($progresoActualizado, $mensaje);
    }
}
