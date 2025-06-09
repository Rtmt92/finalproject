<?php
namespace Controllers;

use Src\Models\Message;

class MessageController {
    private Message $messageModel;

    public function __construct() {
        $this->messageModel = new Message();
    }

    /**
     * GET /message
     */
    public function index(): void {
        $messages = $this->messageModel->getAll();
        header('Content-Type: application/json');
        echo json_encode($messages);
    }

    /**
     * GET /message/{id}
     */
    public function show(int $id): void {
        $message = $this->messageModel->getById($id);
        if (!$message) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Message non trouvé']);
            return;
        }
        header('Content-Type: application/json');
        echo json_encode($message);
    }

    /**
     * POST /message
     */
    public function store(): void {
        $data = json_decode(file_get_contents('php://input'), true);
        if (
            empty($data['contenu']) ||
            empty($data['date_envoi']) ||
            empty($data['id_client'])
        ) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Champs manquants']);
            return;
        }

        $newId = $this->messageModel->create([
            'contenu'    => $data['contenu'],    
            'date_envoi' => $data['date_envoi'], 
            'id_client'  => (int)$data['id_client']
        ]);

        if ($newId) {
            http_response_code(201);
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Message créé', 'id_message' => $newId]);
        } else {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Impossible de créer le message']);
        }
    }

    /**
     * PATCH /message/{id}
     */
    public function update(int $id): void {
        $existing = $this->messageModel->getById($id);
        if (!$existing) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Message non trouvé']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !is_array($data)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Données invalides']);
            return;
        }

        $ok = $this->messageModel->update($id, [
            'contenu'    => $data['contenu'] ?? null,
            'date_envoi' => $data['date_envoi'] ?? null
        ]);

        if ($ok) {
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Message mis à jour']);
        } else {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Impossible de mettre à jour']);
        }
    }

    /**
     * DELETE /message/{id}
     */
    public function destroy(int $id): void {
        $existing = $this->messageModel->getById($id);
        if (!$existing) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Message non trouvé']);
            return;
        }

        $ok = $this->messageModel->delete($id);
        if ($ok) {
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Message supprimé']);
        } else {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Impossible de supprimer']);
        }
    }
}
