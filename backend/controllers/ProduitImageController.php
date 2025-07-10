<?php
namespace Controllers;

use Src\Services\ProduitImageService;

class ProduitImageController {
    private ProduitImageService $service; // Service pour gérer le lien produit ↔ image

    public function __construct() {
        $this->service = new ProduitImageService(); // Initialise le service
    }

    // GET /produit_image : renvoie tous les liens produit–image
    public function index(): void {
        header('Content-Type: application/json');
        echo json_encode($this->service->getAll());
    }

    // POST /produit_image : crée un lien entre un produit et une image
    public function store(): void {
        $data = json_decode(file_get_contents('php://input'), true); // Lit JSON d’entrée
        if (!isset($data['id_produit'], $data['id_image'])) { // Vérifie les champs requis
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Champs manquants']);
            return;
        }
        $ok = $this->service->create($data); // Crée le lien
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

    // DELETE /produit_image/{idProduit}/{idImage} : supprime un lien produit–image
    public function destroy(int $idProduit, int $idImage): void {
        $ok = $this->service->delete($idProduit, $idImage); // Supprime le lien
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
