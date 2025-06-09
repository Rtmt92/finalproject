<?php
namespace Controllers;

use Src\Models\Categorie;

class CategorieController {
    private Categorie $categorieModel;

    public function __construct() {
        $this->categorieModel = new Categorie();
    }

    /** GET /categorie */
    public function index(): void {
        $cats = $this->categorieModel->getAll();
        header('Content-Type: application/json');
        echo json_encode($cats);
    }

    /** GET /categorie/{id} */
    public function show(int $id): void {
        $cat = $this->categorieModel->getById($id);
        if (!$cat) {
            http_response_code(404);
            echo json_encode(['error' => 'Catégorie non trouvée']);
            return;
        }
        header('Content-Type: application/json');
        echo json_encode($cat);
    }

    /** POST /categorie */
    public function store(): void {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['nom'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Champs manquants']);
            return;
        }
        $newId = $this->categorieModel->create(['nom' => $data['nom']]);
        http_response_code(201);
        echo json_encode(['message' => 'Catégorie créée', 'id_categorie' => $newId]);
    }

    /** PUT|PATCH /categorie/{id} */
    public function update(int $id): void {
        $existing = $this->categorieModel->getById($id);
        if (!$existing) {
            http_response_code(404);
            echo json_encode(['error' => 'Catégorie non trouvée']);
            return;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['nom'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Champs manquants']);
            return;
        }
        $ok = $this->categorieModel->update($id, ['nom' => $data['nom']]);
        if ($ok) {
            echo json_encode(['message' => 'Catégorie mise à jour']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Impossible de mettre à jour']);
        }
    }

    /** DELETE /categorie/{id} */
    public function destroy(int $id): void {
        $existing = $this->categorieModel->getById($id);
        if (!$existing) {
            http_response_code(404);
            echo json_encode(['error' => 'Catégorie non trouvée']);
            return;
        }
        $ok = $this->categorieModel->delete($id);
        if ($ok) {
            echo json_encode(['message' => 'Catégorie supprimée']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Impossible de supprimer']);
        }
    }
}
