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

    /**
     * Récupère toutes les images liées à un produit
     *
     * @param int $produitId
     * @return array<int, array<string,mixed>>
     */
    public function getImagesByProduitId(int $produitId): array {
        $sql = "
          SELECT i.*
            FROM image i
            JOIN produit_image pi ON pi.id_image = i.id_image
           WHERE pi.id_produit = :pid
           ORDER BY pi.id_image
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':pid', $produitId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
