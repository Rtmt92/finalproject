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

    public function create(array $data): bool {
        if (!isset($data['id_produit'], $data['id_image'])) {
            return false;
        }

        try {
            $stmt = $this->db->prepare("
                INSERT INTO produit_image (id_produit, id_image)
                VALUES (:id_produit, :id_image)
            ");
            return $stmt->execute([
                'id_produit' => $data['id_produit'],
                'id_image'   => $data['id_image'],
            ]);
        } catch (PDOException $e) {
            error_log("Erreur liaison image-produit (create) : " . $e->getMessage());
            return false;
        }
    }

    public function delete(int $idProduit, int $idImage): bool {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM produit_image
                WHERE id_produit = :id_produit AND id_image = :id_image
            ");
            return $stmt->execute([
                'id_produit' => $idProduit,
                'id_image'   => $idImage,
            ]);
        } catch (PDOException $e) {
            error_log("Erreur suppression liaison image-produit : " . $e->getMessage());
            return false;
        }
    }


    /**
     * Détache une image d’un produit (table produit_image)
     */
    public function detachImageFromProduct(int $idProduit, int $idImage): bool {
        $stmt = $this->db->prepare("
            DELETE FROM produit_image 
            WHERE id_produit = :id_produit AND id_image = :id_image
        ");
        return $stmt->execute([
            'id_produit' => $idProduit,
            'id_image'   => $idImage
        ]);
    }

    /**
     * Ajoute une nouvelle image à la table image
     *
     * @param string $path Chemin relatif ou URL
     * @return int|null ID de l’image insérée
     */
    public function addImage(string $path): ?int {
        try {
            $stmt = $this->db->prepare("INSERT INTO image (link) VALUES (:link)");
            $stmt->execute(['link' => $path]);
            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Erreur insertion image : " . $e->getMessage());
            return null;
        }
    }

    /**
     * Lie une image à un produit via la table produit_image
     *
     * @param int $idProduit
     * @param int $idImage
     * @return bool
     */
    public function attachImageToProduct(int $idProduit, int $idImage): bool {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO produit_image (id_produit, id_image)
                VALUES (:id_produit, :id_image)
            ");
            return $stmt->execute([
                'id_produit' => $idProduit,
                'id_image' => $idImage
            ]);
        } catch (PDOException $e) {
            error_log("Erreur liaison image-produit : " . $e->getMessage());
            return false;
        }
    }

    public function attach(int $idProduit, int $idImage): bool {
    $stmt = $this->db->prepare("INSERT INTO produit_image (id_produit, id_image) VALUES (:pid, :iid)");
    return $stmt->execute([
        'pid' => $idProduit,
        'iid' => $idImage
    ]);
}

}
