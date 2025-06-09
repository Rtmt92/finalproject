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
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['id_panier'], $data['id_produit'], $data['price'], $data['description'])) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Champs manquants']);
            return;
        }
        $ok = $this->model->create($data);
        if ($ok) {
            http_response_code(201);
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Ligne panier créée']);
        } else {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Impossible de créer']);
        }
    }

    /** DELETE /panier_produit/{panier}/{produit} */
    public function destroy(int $idPanier, int $idProduit): void {
        $ok = $this->model->delete($idPanier, $idProduit);
        if ($ok) {
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Ligne panier supprimée']);
        } else {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Impossible de supprimer']);
        }
    }
}
