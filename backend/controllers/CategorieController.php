<?php
namespace Controllers;

use Src\Services\CategorieService;

class CategorieController {
    private CategorieService $categorieService;

    public function __construct() {
        // Initialise le service de gestion des catégories
        $this->categorieService = new CategorieService();
    }

    // Récupère et renvoie toutes les catégories
    public function index(): void {
        $cats = $this->categorieService->getAllCategories();
        header('Content-Type: application/json');
        echo json_encode($cats);
    }

    // Récupère et renvoie une catégorie par son ID
    public function show(int $id): void {
        $cat = $this->categorieService->getCategoryById($id);
        if (!$cat) {
            http_response_code(404);
            echo json_encode(['error' => 'Catégorie non trouvée']);
            return;
        }
        header('Content-Type: application/json');
        echo json_encode($cat);
    }

    // Crée une nouvelle catégorie à partir des données reçues
    public function store(): void {
        $data = json_decode(file_get_contents('php://input'), true);
        $newId = $this->categorieService->createCategory($data);
        if ($newId === false) {
            http_response_code(400);
            echo json_encode(['error' => 'Champs manquants ou invalides']);
            return;
        }
        http_response_code(201);
        echo json_encode(['message' => 'Catégorie créée', 'id_categorie' => $newId]);
    }

    // Met à jour une catégorie existante
    public function update(int $id): void {
        $data = json_decode(file_get_contents('php://input'), true);
        $ok = $this->categorieService->updateCategory($id, $data);
        if (!$ok) {
            http_response_code(404);
            echo json_encode(['error' => 'Catégorie non trouvée ou champs invalides']);
            return;
        }
        echo json_encode(['message' => 'Catégorie mise à jour']);
    }

    // Supprime une catégorie par son ID
    public function destroy(int $id): void {
        $ok = $this->categorieService->deleteCategory($id);
        if (!$ok) {
            http_response_code(404);
            echo json_encode(['error' => 'Catégorie non trouvée']);
            return;
        }
        echo json_encode(['message' => 'Catégorie supprimée']);
    }
}
