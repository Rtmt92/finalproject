<?php
namespace Controllers;

use Src\Models\Signaler;

class SignalerController {
    private Signaler $signalerModel;

    public function __construct() {
        // Instancie le modèle Signaler
        $this->signalerModel = new Signaler();
    }

    /**
     * GET /signaler
     * Renvoie tous les signalements
     */
    public function index(): void {
        $items = $this->signalerModel->getAll();
        header('Content-Type: application/json');
        echo json_encode($items);
    }

    /**
     * GET /signaler/{id}
     * Renvoie un seul signalement ou 404 si non trouvé
     */
    public function show(int $id): void {
        $item = $this->signalerModel->getById($id);
        if (!$item) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Signalement non trouvé']);
            return;
        }
        header('Content-Type: application/json');
        echo json_encode($item);
    }

    /**
     * POST /signaler
     * Crée un signalement (description, date_envoi, id_client)
     */
    public function store(): void {
        $data = json_decode(file_get_contents('php://input'), true);
        if (
            empty($data['description']) ||
            empty($data['date_envoi']) ||
            empty($data['id_client'])
        ) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Champs manquants']);
            return;
        }

        $newId = $this->signalerModel->create([
            'description' => $data['description'],
            'date_envoi'  => $data['date_envoi'],
            'id_client'   => (int)$data['id_client'],
        ]);

        if ($newId !== null) {
            http_response_code(201);
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Signalement créé', 'id_report' => $newId]);
        } else {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Impossible de créer le signalement']);
        }
    }

    /**
     * PATCH /signaler/{id}
     * Mets à jour un signalement existant
     */
    public function update(int $id): void {
        $existing = $this->signalerModel->getById($id);
        if (!$existing) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Signalement non trouvé']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (!is_array($data)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Données invalides']);
            return;
        }

        $ok = $this->signalerModel->update($id, [
            'description' => $data['description'] ?? null,
            'date_envoi'  => $data['date_envoi']  ?? null,
        ]);

        if ($ok) {
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Signalement mis à jour']);
        } else {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Impossible de mettre à jour']);
        }
    }

    /**
     * DELETE /signaler/{id}
     * Supprime un signalement
     */
    public function destroy(int $id): void {
        $existing = $this->signalerModel->getById($id);
        if (!$existing) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Signalement non trouvé']);
            return;
        }

        $ok = $this->signalerModel->delete($id);
        if ($ok) {
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Signalement supprimé']);
        } else {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Impossible de supprimer']);
        }
    }
}
