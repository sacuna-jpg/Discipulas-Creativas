<?php
/**
 * Modelo Respuesta
 * Guarda y actualiza las reflexiones personales de las usuarias.
 */

require_once __DIR__ . '/../core/Database.php';

class Respuesta {
    public static function saveOrUpdate(int $usuarioId, int $preguntaId, string $texto): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO respuestas (usuario_id, pregunta_id, respuesta)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE respuesta = VALUES(respuesta), enviado_at = CURRENT_TIMESTAMP
        ");
        return $stmt->execute([$usuarioId, $preguntaId, trim($texto)]);
    }

    public static function getByUserAndModule(int $usuarioId, int $moduloId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT r.pregunta_id, r.respuesta, r.enviado_at
            FROM respuestas r
            INNER JOIN preguntas p ON r.pregunta_id = p.id
            WHERE r.usuario_id = ? AND p.modulo_id = ?
        ");
        $stmt->execute([$usuarioId, $moduloId]);
        return $stmt->fetchAll();
    }
}
