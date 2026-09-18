<?php
/**
 * Modelo Usuario
 */

require_once __DIR__ . '/../core/Database.php';

class Usuario {
    public static function create(string $nombre, string $correo, string $password, ?int $distritoId, ?int $iglesiaId): int {
        $db = Database::getConnection();
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $db->prepare("
            INSERT INTO usuarios (nombre, correo, password_hash, distrito_id, iglesia_id)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$nombre, strtolower(trim($correo)), $passwordHash, $distritoId, $iglesiaId]);
        return (int) $db->lastInsertId();
    }

    public static function findByEmail(string $correo): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE correo = ?");
        $stmt->execute([strtolower(trim($correo))]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findById(int $id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT u.id, u.nombre, u.correo, u.distrito_id, u.iglesia_id, u.creado_at,
                   d.nombre AS distrito_nombre,
                   i.nombre AS iglesia_nombre
            FROM usuarios u
            LEFT JOIN distritos d ON u.distrito_id = d.id
            LEFT JOIN iglesias i ON u.iglesia_id = i.id
            WHERE u.id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function updatePassword(int $id, string $newPassword): bool {
        $db = Database::getConnection();
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE usuarios SET password_hash = ? WHERE id = ?");
        return $stmt->execute([$newHash, $id]);
    }
}
