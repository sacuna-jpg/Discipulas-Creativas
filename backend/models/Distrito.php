<?php
/**
 * Modelo Distrito
 */

require_once __DIR__ . '/../core/Database.php';

class Distrito {
    public static function all(): array {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT id, nombre FROM distritos ORDER BY nombre ASC");
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id, nombre FROM distritos WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
