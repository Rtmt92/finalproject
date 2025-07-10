<?php
namespace Controllers;

use Src\Services\ImageService;

class ImageController {
    private ImageService $imageService; // Service pour gérer les images

    public function __construct() {
        $this->imageService = new ImageService(); // Initialise le service image
    }

    // GET /images : retourne toutes les images
    public function index(): void {
        $images = $this->imageService->getAllImages();
        header('Content-Type: application/json');
        echo json_encode($images);
    }

    // GET /images/{id} : retourne une image par son ID
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

    // POST /images : crée une nouvelle image
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

    // PUT|PATCH /images/{id} : met à jour une image existante
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

    // DELETE /images/{id} : supprime une image
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
