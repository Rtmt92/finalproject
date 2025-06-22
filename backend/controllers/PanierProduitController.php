<?php
namespace Controllers;

use Src\Models\PanierProduit;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Config\JwtConfig;
use Core\Database;

class PanierProduitController {
    private PanierProduit $model;

    public function __construct() {
        $this->model = new PanierProduit();
    }

    /** POST /panier_produit */
    public function store(): void {
        header('Content-Type: application/json');

        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            http_response_code(401);
            echo json_encode(['error' => 'Token manquant']);
            return;
        }

        try {
            $decoded = JWT::decode($matches[1], new Key(JwtConfig::SECRET_KEY, 'HS256'));
            $id_client = (int)$decoded->sub;
        } catch (\Exception $e) {
            http_response_code(401);
            echo json_encode(['error' => 'Token invalide']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['id_produit'])) {
            http_response_code(400);
            echo json_encode(['error' => 'ID produit manquant']);
            return;
        }

        $id_produit = (int) $data['id_produit'];

        try {
            $db = Database::getConnection();

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

            $stmt = $db->prepare("SELECT 1 FROM panier_produit WHERE id_panier = ? AND id_produit = ?");
            $stmt->execute([$id_panier, $id_produit]);
            if ($stmt->fetch()) {
                echo json_encode(['message' => 'Produit déjà dans le panier']);
                return;
            }

            $stmt = $db->prepare("INSERT INTO panier_produit (id_panier, id_produit) VALUES (?, ?)");
            $stmt->execute([$id_panier, $id_produit]);

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

    /** DELETE /panier_produit/{id_panier}/{id_produit} */
    public function destroy(int $id_panier, int $id_produit): void {
        header('Content-Type: application/json');

        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            http_response_code(401);
            echo json_encode(['error' => 'Token manquant']);
            return;
        }

        try {
            $decoded = JWT::decode($matches[1], new Key(JwtConfig::SECRET_KEY, 'HS256'));
        } catch (\Exception $e) {
            http_response_code(401);
            echo json_encode(['error' => 'Token invalide']);
            return;
        }

        try {
            $db = Database::getConnection();

            // Supprimer la ligne panier_produit
            $stmt = $db->prepare("DELETE FROM panier_produit WHERE id_panier = ? AND id_produit = ?");
            $stmt->execute([$id_panier, $id_produit]);

            // Récupérer le prix du produit
            $stmt = $db->prepare("SELECT prix FROM produit WHERE id_produit = ?");
            $stmt->execute([$id_produit]);
            $produit = $stmt->fetch();

            if ($produit) {
                // Mettre à jour le total du panier
                $stmt = $db->prepare("UPDATE panier SET prix_total = prix_total - ? WHERE id_panier = ?");
                $stmt->execute([$produit['prix'], $id_panier]);
            }

            echo json_encode(['message' => 'Produit supprimé du panier et total mis à jour']);
        } catch (\PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur suppression : ' . $e->getMessage()]);
        }
    }
}
