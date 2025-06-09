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

    /**
     * Récupère une image par son ID.
     * La colonne `lien` contient déjà l'URL complète ou le chemin public.
     *
     * @param int $id
     * @return array<string,mixed>|null ['id_image'=>…, 'lien'=>…] ou null
     */
    public function getById(int $id): ?array {
        try {
            $stmt = $this->db->prepare("SELECT id_image, lien FROM image WHERE id_image = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }
}
