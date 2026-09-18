<?php
/**
 * Modelo Pregunta
 */

require_once __DIR__ . '/../core/Database.php';

class Pregunta {
    public static function getByModulo(int $moduloId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id, modulo_id, orden, enunciado FROM preguntas WHERE modulo_id = ? ORDER BY orden ASC");
        $stmt->execute([$moduloId]);
        return $stmt->fetchAll();
    }
}
