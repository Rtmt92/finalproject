<?php
namespace Src\Models;

use Core\Database;
use PDO;
use PDOException;

class Image {
    private PDO $db;

    // Initialise la connexion PDO
    public function __construct() {
        $this->db = Database::getConnection();
    }

    // Récupère une image par son ID ou null si inexistante
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

    // Vérifie si l'image est encore associée à un produit
    public function isUsedElsewhere(int $idImage): bool {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM produit_image 
            WHERE id_image = :id_image
        ");
        $stmt->execute(['id_image' => $idImage]);
        return $stmt->fetchColumn() > 0;
    }

    // Supprime une image par son ID
    public function delete(int $idImage): bool {
        $stmt = $this->db->prepare("DELETE FROM image WHERE id_image = :id_image");
        return $stmt->execute(['id_image' => $idImage]);
    }   

    // Insère une nouvelle image et renvoie son ID
    public function create(string $path): ?int {
        $stmt = $this->db->prepare("INSERT INTO image (lien) VALUES (:lien)");
        $stmt->bindValue(':lien', $path);
        $stmt->execute();
        return (int) $this->db->lastInsertId();
    }

    // Crée le lien entre une image et un produit
    public function associateToProduct(int $id_produit, int $id_image): void {
        $stmt = $this->db->prepare("INSERT INTO produit_image (id_produit, id_image) VALUES (:id_produit, :id_image)");
        $stmt->execute([
            'id_produit' => $id_produit,
            'id_image'   => $id_image
        ]);
    }

    // Enregistre et lie une URL d'image à un produit en une seule étape
    public function saveForProduct(int $id_produit, string $url): void {
        $stmt = $this->db->prepare("INSERT INTO image (lien) VALUES (:url)");
        $stmt->execute(['url' => $url]);
        $imageId = $this->db->lastInsertId();
        $stmt2 = $this->db->prepare("INSERT INTO produit_image (id_produit, id_image) VALUES (:pid, :iid)");
        $stmt2->execute([
            'pid' => $id_produit,
            'iid' => $imageId
        ]);
    }

    // Récupère la première image liée à un produit ou null
    public function getFirstByProductId(int $id_produit): ?array {
        $stmt = $this->db->prepare("
            SELECT i.id_image, i.lien
            FROM image i
            JOIN produit_image pi ON pi.id_image = i.id_image
            WHERE pi.id_produit = :id_produit
            ORDER BY i.id_image ASC
            LIMIT 1
        ");
        $stmt->execute(['id_produit' => $id_produit]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
