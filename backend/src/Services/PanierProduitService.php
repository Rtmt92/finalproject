<?php
namespace Src\Services;

use Src\Models\PanierProduit;
use Src\Models\Panier;
use Core\Database;
use PDO;

class PanierProduitService {
    private PanierProduit $panierProduitModel;
    private Panier $panierModel;
    private PDO $db;

    public function __construct() {
        $this->panierProduitModel = new PanierProduit();
        $this->panierModel = new Panier();
        $this->db = Database::getConnection();
    }

    public function addProductToClientPanier(int $idClient, int $idProduit): array {
        // Vérifier ou créer panier utilisateur
        $stmt = $this->db->prepare("SELECT id_panier FROM panier WHERE id_client = ?");
        $stmt->execute([$idClient]);
        $row = $stmt->fetch();

        if (!$row) {
            $stmt = $this->db->prepare("INSERT INTO panier (prix_total, id_client) VALUES (0, ?)");
            $stmt->execute([$idClient]);
            $idPanier = (int)$this->db->lastInsertId();
        } else {
            $idPanier = (int)$row['id_panier'];
        }

        // Vérifier si produit déjà présent
        $stmt = $this->db->prepare("SELECT 1 FROM panier_produit WHERE id_panier = ? AND id_produit = ?");
        $stmt->execute([$idPanier, $idProduit]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Produit déjà dans le panier'];
        }

        // Ajouter produit au panier
        $stmt = $this->db->prepare("INSERT INTO panier_produit (id_panier, id_produit) VALUES (?, ?)");
        $stmt->execute([$idPanier, $idProduit]);

        // Récupérer prix produit
        $stmt = $this->db->prepare("SELECT prix FROM produit WHERE id_produit = ?");
        $stmt->execute([$idProduit]);
        $produit = $stmt->fetch();

        if (!$produit) {
            return ['success' => false, 'message' => 'Produit introuvable'];
        }

        // Mettre à jour prix total panier
        $stmt = $this->db->prepare("UPDATE panier SET prix_total = prix_total + ? WHERE id_panier = ?");
        $stmt->execute([$produit['prix'], $idPanier]);

        return ['success' => true, 'message' => 'Produit ajouté au panier'];
    }

    public function removeProductFromClientPanier(int $idClient, int $idPanier, int $idProduit): array {
        // Vérifier que le panier appartient bien au client
        $stmt = $this->db->prepare("SELECT id_panier FROM panier WHERE id_panier = ? AND id_client = ?");
        $stmt->execute([$idPanier, $idClient]);
        if (!$stmt->fetch()) {
            return ['success' => false, 'error' => 'Ce panier ne vous appartient pas'];
        }

        // Supprimer lien produit-panier
        $stmt = $this->db->prepare("DELETE FROM panier_produit WHERE id_panier = ? AND id_produit = ?");
        $stmt->execute([$idPanier, $idProduit]);

        // Récupérer prix produit
        $stmt = $this->db->prepare("SELECT prix FROM produit WHERE id_produit = ?");
        $stmt->execute([$idProduit]);
        $produit = $stmt->fetch();

        if ($produit) {
            $stmt = $this->db->prepare("UPDATE panier SET prix_total = prix_total - ? WHERE id_panier = ?");
            $stmt->execute([$produit['prix'], $idPanier]);
        }

        return ['success' => true, 'message' => 'Produit supprimé du panier et total mis à jour'];
    }
}
