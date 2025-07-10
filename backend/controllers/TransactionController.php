<?php
namespace Controllers;

use Src\Services\TransactionService;

class TransactionController {
    private TransactionService $service; // Service pour gérer les transactions

    public function __construct() {
        $this->service = new TransactionService(); // Initialise le service transaction
    }

    // GET /transactions : liste toutes les transactions
    public function index(): void {
        $list = $this->service->getAll();
        header('Content-Type: application/json');
        echo json_encode($list);
    }

    // GET /transactions/{id} : renvoie une transaction par son ID
    public function show(int $id): void {
        $row = $this->service->getById($id);
        if (!$row) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Transaction non trouvée']);
            return;
        }
        header('Content-Type: application/json');
        echo json_encode($row);
    }

    // POST /transactions : crée une nouvelle transaction
    public function store(): void {
        $data = json_decode(file_get_contents('php://input'), true);
        $newId = $this->service->create($data);
        if ($newId === false) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Champs manquants']);
            return;
        }
        http_response_code(201);
        header('Content-Type: application/json');
        echo json_encode(['message' => 'Transaction créée', 'id_transaction' => $newId]);
    }

    // PUT|PATCH /transactions/{id} : met à jour une transaction existante
    public function update(int $id): void {
        $existing = $this->service->getById($id);
        if (!$existing) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Transaction non trouvée']);
            return;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        if (!is_array($data)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Données invalides']);
            return;
        }
        $ok = $this->service->update($id, $data);
        if (!$ok) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Aucun champ à mettre à jour ou erreur']);
            return;
        }
        header('Content-Type: application/json');
        echo json_encode(['message' => 'Transaction mise à jour']);
    }

    // DELETE /transactions/{id} : supprime une transaction
    public function destroy(int $id): void {
        $existing = $this->service->getById($id);
        if (!$existing) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Transaction non trouvée']);
            return;
        }
        $ok = $this->service->delete($id);
        if ($ok) {
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Transaction supprimée']);
        } else {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Impossible de supprimer']);
        }
    }
}
