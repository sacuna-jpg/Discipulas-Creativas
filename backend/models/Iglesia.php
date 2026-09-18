<?php
/**
 * Modelo Iglesia
 */

require_once __DIR__ . '/../core/Database.php';

class Iglesia {
    public static function getByDistrito(int $distritoId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id, distrito_id, nombre FROM iglesias WHERE distrito_id = ? ORDER BY nombre ASC");
        $stmt->execute([$distritoId]);
        return $stmt->fetchAll();
    }

    public static function all(): array {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT id, distrito_id, nombre FROM iglesias ORDER BY nombre ASC");
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id, distrito_id, nombre FROM iglesias WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
