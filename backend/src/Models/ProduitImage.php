<?php
namespace Src\Models;

use Core\Database;
use PDO;
use PDOException;

class ProduitImage {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getAll(): array {
        return $this->db->query("SELECT * FROM produit_image")->fetchAll();
    }

    public function create(array $data): bool {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO produit_image (id_produit, id_image)
                VALUES (:produit, :image)
            ");
            $stmt->bindValue(':produit',$data['id_produit'], PDO::PARAM_INT);
            $stmt->bindValue(':image',  $data['id_image'],   PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete(int $produit, int $image): bool {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM produit_image
                WHERE id_produit = :produit AND id_image = :image
            ");
            $stmt->bindValue(':produit',$produit, PDO::PARAM_INT);
            $stmt->bindValue(':image',  $image,   PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}
