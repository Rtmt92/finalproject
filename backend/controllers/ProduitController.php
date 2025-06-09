<?php
namespace Controllers;

use Src\Models\Produit;

class ProduitController {
    private Produit $produitModel;

    public function __construct() {
        $this->produitModel = new Produit();
    }

    // GET /produit
    public function index(): void {
        $items = $this->produitModel->getAll();
        header('Content-Type: application/json');
        echo json_encode($items);
    }

    // GET /produit/{id}
    public function show(int $id): void {
        $item = $this->produitModel->getById($id);
        if (!$item) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Produit non trouvé']);
            return;
        }
        header('Content-Type: application/json');
        echo json_encode($item);
    }

    // POST /produit
    public function store(): void {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['prix']) || empty($data['description']) || empty($data['id_categorie'])) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Champs manquants']);
            return;
        }
        $newId = $this->produitModel->create([
            'prix' => $data['prix'], 
            'description' => $data['description'], 
            'id_categorie' => (int)$data['id_categorie']
        ]);
        if ($newId) {
            http_response_code(201);
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Produit créé', 'id_produit' => $newId]);
        } else {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Impossible de créer']);
        }
    }

    // PATCH /produit/{id}
    public function update(int $id): void {
        $existing = $this->produitModel->getById($id);
        if (!$existing) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Produit non trouvé']);
            return;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        if (!is_array($data)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Données invalides']);
            return;
        }
        $ok = $this->produitModel->update($id, [
            'prix' => $data['prix'] ?? null,
            'description' => $data['description'] ?? null,
            'id_categorie' => isset($data['id_categorie']) ? (int)$data['id_categorie'] : null
        ]);
        if ($ok) {
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Produit mis à jour']);
        } else {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Impossible de mettre à jour']);
        }
    }

    // DELETE /produit/{id}
    public function destroy(int $id): void {
        $existing = $this->produitModel->getById($id);
        if (!$existing) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Produit non trouvé']);
            return;
        }
        $ok = $this->produitModel->delete($id);
        if ($ok) {
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Produit supprimé']);
        } else {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Impossible de supprimer']);
        }
    }
}
