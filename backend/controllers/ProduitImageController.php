<?php
namespace Controllers;

use Src\Services\ProduitImageService;

class ProduitImageController {
    private ProduitImageService $service;

    public function __construct() {
        $this->service = new ProduitImageService();
    }

    public function index(): void {
        header('Content-Type: application/json');
        echo json_encode($this->service->getAll());
    }

    public function store(): void {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['id_produit'], $data['id_image'])) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Champs manquants']);
            return;
        }
        $ok = $this->service->create($data);
        if ($ok) {
            http_response_code(201);
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Lien image-produit créé']);
        } else {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Impossible de créer']);
        }
    }

    public function destroy(int $idProduit, int $idImage): void {
        $ok = $this->service->delete($idProduit, $idImage);
        if ($ok) {
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Lien image-produit supprimé']);
        } else {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Impossible de supprimer']);
        }
    }
}
