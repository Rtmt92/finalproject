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

    public function destroy(int $idPanier, int $idProduit): void {
        header('Content-Type: application/json');
        
        try {
            $db = new \PDO("mysql:host=localhost;dbname=dejavu", "root", "admin");

            // 1. Récupérer le prix du produit
            $stmt = $db->prepare("SELECT prix FROM produit WHERE id_produit = :idProduit");
            $stmt->execute(['idProduit' => $idProduit]);
            $produit = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$produit) {
                http_response_code(404);
                echo json_encode(['error' => 'Produit introuvable']);
                return;
            }

            $prix = (float)$produit['prix'];

            // 2. Supprimer l'entrée panier_produit
            $stmt = $db->prepare("DELETE FROM panier_produit WHERE id_panier = :idPanier AND id_produit = :idProduit");
            $stmt->execute(['idPanier' => $idPanier, 'idProduit' => $idProduit]);

            // 3. Mettre à jour le prix_total du panier
            $stmt = $db->prepare("UPDATE panier SET prix_total = prix_total - :prix WHERE id_panier = :idPanier");
            $stmt->execute(['prix' => $prix, 'idPanier' => $idPanier]);

            echo json_encode(['message' => 'Produit supprimé et total mis à jour']);
        } catch (\PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur serveur : ' . $e->getMessage()]);
        }
    }


    /** GET /fake-login (pour Postman) */
    public function testLogin(): void {
        session_start();
        $_SESSION['id_client'] = 19; // à adapter
        echo json_encode(['message' => 'Session active', 'id_client' => $_SESSION['id_client']]);
    }

    public function viderPanier(int $id_panier): void {
        header('Content-Type: application/json');

        try {
            $db = new \PDO("mysql:host=localhost;dbname=dejavu", "root", "admin");

            // Supprimer les produits du panier
            $stmt = $db->prepare("DELETE FROM panier_produit WHERE id_panier = :id");
            $stmt->execute(['id' => $id_panier]);

            // Réinitialiser le prix total du panier à 0
            $stmt = $db->prepare("UPDATE panier SET prix_total = 0 WHERE id_panier = :id");
            $stmt->execute(['id' => $id_panier]);

            echo json_encode(['message' => 'Panier vidé avec succès']);
        } catch (\PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors du vidage du panier']);
        }
    }

}
