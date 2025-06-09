<?php
namespace Controllers;

use Src\Models\Image;

class ImageController {
    private Image $imageModel;

    public function __construct() {
        $this->imageModel = new Image();
    }

    /** GET /image */
    public function index(): void {
        $images = $this->imageModel->getAll();
        header('Content-Type: application/json');
        echo json_encode($images);
    }

    /** GET /image/{id} */
    public function show(int $id): void {
        $image = $this->imageModel->getById($id);
        if (!$image) {
            http_response_code(404);
            echo json_encode(['error' => 'Image non trouvée']);
            return;
        }
        header('Content-Type: application/json');
        echo json_encode($image);
    }

    /** POST /image */
    public function store(): void {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['lien'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Champs manquants']);
            return;
        }
        $newId = $this->imageModel->create(['lien' => $data['lien']]);
        http_response_code(201);
        echo json_encode(['message' => 'Image créée', 'id_image' => $newId]);
    }

    /** PUT/PATCH /image/{id} */
    public function update(int $id): void {
        $existing = $this->imageModel->getById($id);
        if (!$existing) {
            http_response_code(404);
            echo json_encode(['error' => 'Image non trouvée']);
            return;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['lien'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Champs manquants']);
            return;
        }
        $ok = $this->imageModel->update($id, ['lien' => $data['lien']]);
        if ($ok) {
            echo json_encode(['message' => 'Image mise à jour']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Impossible de mettre à jour']);
        }
    }

    /** DELETE /image/{id} */
    public function destroy(int $id): void {
        $existing = $this->imageModel->getById($id);
        if (!$existing) {
            http_response_code(404);
            echo json_encode(['error' => 'Image non trouvée']);
            return;
        }
        $ok = $this->imageModel->delete($id);
        if ($ok) {
            echo json_encode(['message' => 'Image supprimée']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Impossible de supprimer']);
        }
    }
}
