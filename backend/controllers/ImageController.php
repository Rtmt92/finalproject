<?php
namespace Controllers;

use Src\Services\ImageService;

class ImageController {
    private ImageService $imageService;

    public function __construct() {
        $this->imageService = new ImageService();
    }

    public function index(): void {
        $images = $this->imageService->getAllImages();
        header('Content-Type: application/json');
        echo json_encode($images);
    }

    public function show(int $id): void {
        $image = $this->imageService->getImageById($id);
        if (!$image) {
            http_response_code(404);
            echo json_encode(['error' => 'Image non trouvée']);
            return;
        }
        header('Content-Type: application/json');
        echo json_encode($image);
    }

    public function store(): void {
        $data = json_decode(file_get_contents('php://input'), true);
        $newId = $this->imageService->createImage($data);
        if ($newId === false) {
            http_response_code(400);
            echo json_encode(['error' => 'Champs manquants']);
            return;
        }
        http_response_code(201);
        echo json_encode(['message' => 'Image créée', 'id_image' => $newId]);
    }

    public function update(int $id): void {
        $data = json_decode(file_get_contents('php://input'), true);
        $ok = $this->imageService->updateImage($id, $data);
        if (!$ok) {
            http_response_code(404);
            echo json_encode(['error' => 'Image non trouvée ou champs manquants']);
            return;
        }
        echo json_encode(['message' => 'Image mise à jour']);
    }

    public function destroy(int $id): void {
        $ok = $this->imageService->deleteImage($id);
        if (!$ok) {
            http_response_code(404);
            echo json_encode(['error' => 'Image non trouvée ou utilisée ailleurs']);
            return;
        }
        echo json_encode(['message' => 'Image supprimée']);
    }
}
