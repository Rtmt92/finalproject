<?php
namespace Controllers;

use Src\Services\PanierService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Config\JwtConfig;

class PanierController {
    private PanierService $panierService; // Service pour gérer les paniers

    public function __construct() {
        $this->panierService = new PanierService(); // Initialise le service panier
    }

    // Extrait l’ID client depuis le token JWT Bearer
    private function getClientIdFromToken(): int {
        $h = $_SERVER['HTTP_AUTHORIZATION'] ?? ''; // Récupère le header Authorization
        if (!preg_match('/Bearer\s(\S+)/', $h, $m)) { // Vérifie la présence du token
            http_response_code(401);
            echo json_encode(['error' => 'Token manquant']);
            exit;
        }
        try {
            $decoded = JWT::decode($m[1], new Key(JwtConfig::SECRET_KEY, 'HS256')); // Décode le JWT
            return (int)$decoded->sub; // Retourne l’ID utilisateur
        } catch (\Exception $e) { // En cas de token invalide ou expiré
            http_response_code(401);
            echo json_encode(['error' => 'Token invalide']);
            exit;
        }
    }

    // GET /panier : retourne tous les paniers
    public function index(): void {
        $all = $this->panierService->getAll();
        header('Content-Type: application/json');
        echo json_encode($all);
    }

    // GET /panier/{id} : retourne un panier par son ID
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

    // POST /panier : crée un nouveau panier
    public function store(): void {
        $data = json_decode(file_get_contents('php://input'), true); // Lit JSON d’entrée
        $newId = $this->panierService->create($data);
        if ($newId === false) {
            http_response_code(400);
            echo json_encode(['error' => 'Champs manquants']);
            return;
        }
        http_response_code(201);
        echo json_encode(['message' => 'Panier créé', 'id_panier' => $newId]);
    }

    // PUT|PATCH /panier/{id} : met à jour un panier existant
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

    // DELETE /panier/{id} : supprime un panier
    public function destroy(int $id): void {
        $ok = $this->panierService->delete($id);
        if (!$ok) {
            http_response_code(404);
            echo json_encode(['error' => 'Panier non trouvé']);
            return;
        }
        echo json_encode(['message' => 'Panier supprimé']);
    }

    // GET /mon-panier : retourne le panier de l’utilisateur avec première image
    public function showUserPanier(): void {
        $idClient = $this->getClientIdFromToken(); // Récupère l’ID client du token
        $result = $this->panierService->getWithFirstImagesByClientId($idClient);
        header('Content-Type: application/json; charset=utf-8');
        if (!$result) {
            echo json_encode(['id_panier' => null, 'prix_total' => 0, 'produits' => []]);
        } else {
            echo json_encode($result);
        }
    }

    // GET /mon-panier/produits : retourne le panier complet avec produits
    public function getMyPanier(): void {
        $idClient = $this->getClientIdFromToken(); // Récupère l’ID client du token
        $data = $this->panierService->getWithProduitsByClientId($idClient);
        echo json_encode($data);
    }

    // POST /panier/{id}/vider : vide les produits d’un panier donné
    public function vider(int $id_panier): void {
        $this->panierService->vider($id_panier); // Vide le panier en base
        echo json_encode(['message' => 'Panier vidé avec succès']);
    }
}
