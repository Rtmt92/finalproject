<?php
namespace Controllers;

use Src\Services\PanierProduitService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Config\JwtConfig;

class PanierProduitController {
    private PanierProduitService $service; // Service pour gérer la liaison panier-produit

    public function __construct() {
        $this->service = new PanierProduitService(); // Initialise le service
    }

    // Extrait l’ID client depuis le token JWT Bearer
    private function authenticateClient(): ?int {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? ''; // Récupère le header Authorization
        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) { // Vérifie la présence du token
            http_response_code(401);
            echo json_encode(['error' => 'Token manquant']);
            return null;
        }

        try {
            $decoded = JWT::decode($matches[1], new Key(JwtConfig::SECRET_KEY, 'HS256')); // Décode le JWT
            return (int)$decoded->sub; // Retourne l’ID client
        } catch (\Exception $e) { // En cas de token invalide ou expiré
            http_response_code(401);
            echo json_encode(['error' => 'Token invalide']);
            return null;
        }
    }

    // POST /panier_produit : ajoute un produit au panier de l’utilisateur
    public function store(): void {
        header('Content-Type: application/json');
        $idClient = $this->authenticateClient(); // Authentifie et récupère l’ID client
        if ($idClient === null) return;

        $data = json_decode(file_get_contents('php://input'), true); // Lit les données JSON
        if (empty($data['id_produit'])) { // Vérifie la présence de l’ID produit
            http_response_code(400);
            echo json_encode(['error' => 'ID produit manquant']);
            return;
        }

        $result = $this->service->addProductToClientPanier($idClient, (int)$data['id_produit']); // Appelle le service

        if ($result['success']) { // Succès
            http_response_code(201);
            echo json_encode(['message' => $result['message']]);
        } else { // Échec
            http_response_code(400);
            echo json_encode(['error' => $result['message']]);
        }
    }

    // DELETE /panier_produit/{id_panier}/{id_produit} : retire un produit du panier
    public function destroy(int $id_panier, int $id_produit): void {
        header('Content-Type: application/json');
        $idClient = $this->authenticateClient(); // Authentifie et récupère l’ID client
        if ($idClient === null) return;

        $result = $this->service->removeProductFromClientPanier($idClient, $id_panier, $id_produit); // Appelle le service

        if ($result['success']) { // Succès
            echo json_encode(['message' => $result['message']]);
        } else { // Échec
            http_response_code(403);
            echo json_encode(['error' => $result['error']]);
        }
    }
}
