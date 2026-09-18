<?php
/**
 * Modelo Progreso
 * Control de módulos completados y cálculo del porcentaje de certificación.
 */

require_once __DIR__ . '/../core/Database.php';

class Progreso {
    public static function markCompleted(int $usuarioId, int $moduloId): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO progreso (usuario_id, modulo_id, completado_at)
            VALUES (?, ?, CURRENT_TIMESTAMP)
            ON DUPLICATE KEY UPDATE completado_at = CURRENT_TIMESTAMP
        ");
        return $stmt->execute([$usuarioId, $moduloId]);
    }

    public static function markIncomplete(int $usuarioId, int $moduloId): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM progreso WHERE usuario_id = ? AND modulo_id = ?");
        return $stmt->execute([$usuarioId, $moduloId]);
    }

    public static function getUserProgress(int $usuarioId): array {
        $db = Database::getConnection();

        // 1. Obtener IDs de módulos completados
        $stmt = $db->prepare("SELECT modulo_id, completado_at FROM progreso WHERE usuario_id = ? ORDER BY modulo_id ASC");
        $stmt->execute([$usuarioId]);
        $completados = $stmt->fetchAll();

        $completadosIds = array_map(function($item) {
            return (int) $item['modulo_id'];
        }, $completados);

        // 2. Total de módulos activos en el sistema
        $totalModulos = (int) $db->query("SELECT COUNT(*) FROM modulos WHERE activo = 1")->fetchColumn();
        $totalCompletados = count($completadosIds);
        $porcentaje = $totalModulos > 0 ? round(($totalCompletados / $totalModulos) * 100) : 0;

        // 3. Obtener todas las respuestas dadas por esta usuaria
        $stmtResp = $db->prepare("
            SELECT r.pregunta_id, r.respuesta, r.enviado_at, p.modulo_id
            FROM respuestas r
            INNER JOIN preguntas p ON r.pregunta_id = p.id
            WHERE r.usuario_id = ?
        ");
        $stmtResp->execute([$usuarioId]);
        $respuestas = $stmtResp->fetchAll();

        $respuestasPorModulo = [];
        foreach ($respuestas as $r) {
            $respuestasPorModulo[$r['modulo_id']][] = [
                'pregunta_id' => (int) $r['pregunta_id'],
                'respuesta'   => $r['respuesta'],
                'enviado_at'  => $r['enviado_at']
            ];
        }

        return [
            'completados_ids'    => $completadosIds,
            'total_completados'  => $totalCompletados,
            'total_modulos'      => $totalModulos,
            'porcentaje'         => $porcentaje,
            'certificado_listo'  => ($totalCompletados >= $totalModulos && $totalModulos > 0),
            'respuestas'         => $respuestasPorModulo
        ];
    }
}
