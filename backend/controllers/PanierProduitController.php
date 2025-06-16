<?php
namespace Controllers;

use Src\Models\PanierProduit;

class PanierProduitController {
    private PanierProduit $model;

    public function __construct() {
        $this->model = new PanierProduit();
    }

    /** GET /panier_produit */
    public function index(): void {
        header('Content-Type: application/json');
        echo json_encode($this->model->getAll());
    }

    /** POST /panier_produit */
    public function store(): void {
        session_start();
        header('Content-Type: application/json');

        if (!isset($_SESSION['id_client'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Utilisateur non connecté']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['id_produit'])) {
            http_response_code(400);
            echo json_encode(['error' => 'ID produit manquant']);
            return;
        }

        $id_client  = $_SESSION['id_client'];
        $id_produit = (int) $data['id_produit'];

        try {
            // ✅ Connexion avec mot de passe explicite
            $db = new \PDO("mysql:host=localhost;dbname=dejavu", "root", "admin");

            // Vérifie ou crée un panier
            $stmt = $db->prepare("SELECT id_panier FROM panier WHERE id_client = ?");
            $stmt->execute([$id_client]);
            $row = $stmt->fetch();

            if (!$row) {
                $stmt = $db->prepare("INSERT INTO panier (prix_total, id_client) VALUES (0, ?)");
                $stmt->execute([$id_client]);
                $id_panier = $db->lastInsertId();
            } else {
                $id_panier = $row['id_panier'];
            }

            // Produit déjà dans panier ?
            $stmt = $db->prepare("SELECT 1 FROM panier_produit WHERE id_panier = ? AND id_produit = ?");
            $stmt->execute([$id_panier, $id_produit]);
            if ($stmt->fetch()) {
                echo json_encode(['message' => 'Produit déjà dans le panier']);
                return;
            }

            // Ajoute au panier
            $stmt = $db->prepare("INSERT INTO panier_produit (id_panier, id_produit) VALUES (?, ?)");
            $stmt->execute([$id_panier, $id_produit]);

            // Met à jour le prix total
            $stmt = $db->prepare("SELECT prix FROM produit WHERE id_produit = ?");
            $stmt->execute([$id_produit]);
            $produit = $stmt->fetch();

            if (!$produit) {
                http_response_code(404);
                echo json_encode(['error' => 'Produit introuvable']);
                return;
            }

            $stmt = $db->prepare("UPDATE panier SET prix_total = prix_total + ? WHERE id_panier = ?");
            $stmt->execute([$produit['prix'], $id_panier]);

            http_response_code(201);
            echo json_encode(['message' => 'Produit ajouté au panier']);
        } catch (\PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur serveur : ' . $e->getMessage()]);
        }
    }

    /** DELETE /panier_produit/{panier}/{produit} */
    public function destroy(int $idPanier, int $idProduit): void {
        $ok = $this->model->delete($idPanier, $idProduit);
        header('Content-Type: application/json');
        if ($ok) {
            echo json_encode(['message' => 'Ligne panier supprimée']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Impossible de supprimer']);
        }
    }

    /** GET /fake-login (pour Postman) */
    public function testLogin(): void {
        session_start();
        $_SESSION['id_client'] = 17; // à adapter
        echo json_encode(['message' => 'Session active', 'id_client' => $_SESSION['id_client']]);
    }
}
