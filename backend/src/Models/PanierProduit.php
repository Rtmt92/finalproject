<?php
namespace Src\Models;

use Core\Database;
use PDO;
use PDOException;

class PanierProduit {
    private PDO $db;

    public function __construct() {
        // Obtient la connexion PDO via la classe Database
        $this->db = Database::getConnection();
    }

    /**
     * Récupère toutes les lignes panier_produit
     * @return array
     */
    public function getAll(): array {
        return $this->db->query("SELECT * FROM panier_produit")->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crée une nouvelle ligne dans panier_produit
     * @param array $data [id_panier, id_produit, price, description]
     * @return bool
     */
    public function create(array $data): bool {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO panier_produit (id_panier, id_produit, price, description)
                 VALUES (:panier, :produit, :price, :desc)"
            );
            $stmt->bindValue(':panier',   $data['id_panier'], PDO::PARAM_INT);
            $stmt->bindValue(':produit',  $data['id_produit'], PDO::PARAM_INT);
            $stmt->bindValue(':price',    $data['price']);
            $stmt->bindValue(':desc',     $data['description']);
            return $stmt->execute();
        } catch (PDOException $e) {
            // En cas d'erreur SQL, on peut logger l'erreur si besoin
            return false;
        }
    }

    /**
     * Supprime une ligne de panier_produit
     * @param int $panier
     * @param int $produit
     * @return bool
     */
    public function delete(int $panier, int $produit): bool {
        try {
            $stmt = $this->db->prepare(
                "DELETE FROM panier_produit
                 WHERE id_panier = :panier AND id_produit = :produit"
            );
            $stmt->bindValue(':panier',   $panier, PDO::PARAM_INT);
            $stmt->bindValue(':produit',  $produit, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Supprime toutes les lignes associées à un panier
     * @param int $panierId
     * @return void
     */
    public function deleteByPanier(int $panierId): void {
        $stmt = $this->db->prepare("DELETE FROM panier_produit WHERE id_panier = :id");
        $stmt->execute(['id' => $panierId]);
    }
}
