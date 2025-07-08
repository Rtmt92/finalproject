<?php
namespace Controllers;

use Src\Services\PanierProduitService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Config\JwtConfig;

class PanierProduitController {
    private PanierProduitService $service;

    public function __construct() {
        $this->service = new PanierProduitService();
    }

    private function authenticateClient(): ?int {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            http_response_code(401);
            echo json_encode(['error' => 'Token manquant']);
            return null;
        }

        try {
            $decoded = JWT::decode($matches[1], new Key(JwtConfig::SECRET_KEY, 'HS256'));
            return (int)$decoded->sub;
        } catch (\Exception $e) {
            http_response_code(401);
            echo json_encode(['error' => 'Token invalide']);
            return null;
        }
    }

    /** POST /panier_produit */
    public function store(): void {
        header('Content-Type: application/json');

        $idClient = $this->authenticateClient();
        if ($idClient === null) return;

        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['id_produit'])) {
            http_response_code(400);
            echo json_encode(['error' => 'ID produit manquant']);
            return;
        }

        $result = $this->service->addProductToClientPanier($idClient, (int)$data['id_produit']);

        if ($result['success']) {
            http_response_code(201);
            echo json_encode(['message' => $result['message']]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => $result['message']]);
        }
    }

    /** DELETE /panier_produit/{id_panier}/{id_produit} */
    public function destroy(int $id_panier, int $id_produit): void {
        header('Content-Type: application/json');

        $idClient = $this->authenticateClient();
        if ($idClient === null) return;

        $result = $this->service->removeProductFromClientPanier($idClient, $id_panier, $id_produit);

        if ($result['success']) {
            echo json_encode(['message' => $result['message']]);
        } else {
            http_response_code(403);
            echo json_encode(['error' => $result['error']]);
        }
    }
}
