<?php
namespace Src\Models;

use Core\Database;
use PDO;
use PDOException;

class PanierProduit {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getAll(): array {
        return $this->db->query("SELECT * FROM panier_produit")->fetchAll(PDO::FETCH_ASSOC);
    }

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
            return false;
        }
    }

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

    public function deleteByPanier(int $panierId): void {
        $stmt = $this->db->prepare("DELETE FROM panier_produit WHERE id_panier = :id");
        $stmt->execute(['id' => $panierId]);
    }

    public function ajouterProduitAuPanier() {
        session_start();

        if (!isset($_SESSION['id_client'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Utilisateur non connecté']);
            return;
        }

        $idClient = $_SESSION['id_client'];
        $data = json_decode(file_get_contents('php://input'), true);
        $idProduit = $data['id_produit'] ?? null;

        if (!$idProduit) {
            http_response_code(400);
            echo json_encode(['error' => 'Produit non précisé']);
            return;
        }

        $panier = $this->panierModel->getByClientId($idClient);
        $idPanier = $panier ? $panier['id_panier'] : $this->panierModel->create(['id_client' => $idClient]);

        $this->panierProduitModel->addProduit($idPanier, $idProduit);
        echo json_encode(['message' => 'Produit ajouté au panier']);
    }
}
