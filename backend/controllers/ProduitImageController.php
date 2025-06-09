<?php
namespace Controllers;

use Src\Models\ProduitImage;

class ProduitImageController {
    private ProduitImage $model;

    public function __construct() {
        $this->model = new ProduitImage();
    }

    /** GET /produit_image */
    public function index(): void {
        header('Content-Type: application/json');
        echo json_encode($this->model->getAll());
    }

    /** POST /produit_image */
    public function store(): void {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['id_produit'], $data['id_image'])) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Champs manquants']);
            return;
        }
        $ok = $this->model->create($data);
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

    /** DELETE /produit_image/{produit}/{image} */
    public function destroy(int $idProduit, int $idImage): void {
        $ok = $this->model->delete($idProduit, $idImage);
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
