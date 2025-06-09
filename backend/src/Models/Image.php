<?php
namespace Src\Models;

use Core\Database;
use PDO;
use PDOException;

class Image {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /** Récupère toutes les images */
    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM image");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Récupère une image par son ID */
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM image WHERE id_image = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Crée une nouvelle image
     * @param array $data ['lien' => string]
     * @return int ID inséré
     */
    public function create(array $data): int {
        $stmt = $this->db->prepare("INSERT INTO image (lien) VALUES (:lien)");
        $stmt->execute(['lien' => $data['lien']]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Met à jour le lien d’une image
     * @param int   $id   ID de l’image
     * @param array $data ['lien' => string]
     * @return bool
     */
    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare(
            "UPDATE image SET lien = :lien WHERE id_image = :id"
        );
        return $stmt->execute([
            'lien' => $data['lien'],
            'id'   => $id
        ]);
    }

    /** Supprime une image par son ID */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM image WHERE id_image = :id");
        return $stmt->execute(['id' => $id]);
    }
}
