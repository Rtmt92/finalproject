<?php
namespace Controllers;

use Src\Services\PanierService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Config\JwtConfig;

class PanierController {
    private PanierService $panierService;

    public function __construct() {
        $this->panierService = new PanierService();
    }

    private function getClientIdFromToken(): int {
        $h = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!preg_match('/Bearer\s(\S+)/', $h, $m)) {
            http_response_code(401);
            echo json_encode(['error' => 'Token manquant']);
            exit;
        }
        try {
            $decoded = JWT::decode($m[1], new Key(JwtConfig::SECRET_KEY, 'HS256'));
            return (int)$decoded->sub;
        } catch (\Exception $e) {
            http_response_code(401);
            echo json_encode(['error' => 'Token invalide']);
            exit;
        }
    }

    public function index(): void {
        $all = $this->panierService->getAll();
        header('Content-Type: application/json');
        echo json_encode($all);
    }

    public function show(int $id): void {
        $item = $this->panierService->getById($id);
        if (!$item) {
            http_response_code(404);
            echo json_encode(['error' => 'Panier non trouvé']);
            return;
        }
        header('Content-Type: application/json');
        echo json_encode($item);
    }

    public function store(): void {
        $data = json_decode(file_get_contents('php://input'), true);
        $newId = $this->panierService->create($data);
        if ($newId === false) {
            http_response_code(400);
            echo json_encode(['error' => 'Champs manquants']);
            return;
        }
        http_response_code(201);
        echo json_encode(['message' => 'Panier créé', 'id_panier' => $newId]);
    }

    public function update(int $id): void {
        $data = json_decode(file_get_contents('php://input'), true);
        $ok = $this->panierService->update($id, $data);
        if (!$ok) {
            http_response_code(404);
            echo json_encode(['error' => 'Panier non trouvé ou champs manquants']);
            return;
        }
        echo json_encode(['message' => 'Panier mis à jour']);
    }

    public function destroy(int $id): void {
        $ok = $this->panierService->delete($id);
        if (!$ok) {
            http_response_code(404);
            echo json_encode(['error' => 'Panier non trouvé']);
            return;
        }
        echo json_encode(['message' => 'Panier supprimé']);
    }

    public function showUserPanier(): void {
        $idClient = $this->getClientIdFromToken();
        $result = $this->panierService->getWithFirstImagesByClientId($idClient);

        header('Content-Type: application/json; charset=utf-8');
        if (!$result) {
            echo json_encode(['id_panier' => null, 'prix_total' => 0, 'produits' => []]);
        } else {
            echo json_encode($result);
        }
    }

    public function getMyPanier(): void {
        $idClient = $this->getClientIdFromToken();
        $data = $this->panierService->getWithProduitsByClientId($idClient);
        echo json_encode($data);
    }

    public function vider(int $id_panier): void {
        $this->panierService->vider($id_panier);
        echo json_encode(['message' => 'Panier vidé avec succès']);
    }
}
