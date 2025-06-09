<?php
namespace Controllers;

use Src\Models\Transaction;

class TransactionController {
    private Transaction $transactionModel;

    public function __construct() {
        $this->transactionModel = new Transaction();
    }

    /**
     * GET /transaction
     */
    public function index(): void {
        $list = $this->transactionModel->getAll();
        header('Content-Type: application/json');
        echo json_encode($list);
    }

    /**
     * GET /transaction/{id}
     */
    public function show(int $id): void {
        $row = $this->transactionModel->getById($id);
        if (!$row) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Transaction non trouvée']);
            return;
        }
        header('Content-Type: application/json');
        echo json_encode($row);
    }

    /**
     * POST /transaction
     */
    public function store(): void {
        $data = json_decode(file_get_contents('php://input'), true);

        // on vérifie montant_total et non total
        if (
            empty($data['montant_total']) ||
            empty($data['date_transaction']) ||
            empty($data['id_client'])
        ) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Champs manquants']);
            return;
        }

        // Création
        $newId = $this->transactionModel->create([
            'montant_total'    => $data['montant_total'],
            'date_transaction' => $data['date_transaction'],
            'id_client'        => $data['id_client'],
        ]);

        if ($newId) {
            http_response_code(201);
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Transaction créée', 'id_transaction' => $newId]);
        } else {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Impossible de créer la transaction']);
        }
    }

    /**
     * PATCH/PUT /transaction/{id}
     */
    public function update(int $id): void {
        // on vérifie l’existence
        $existing = $this->transactionModel->getById($id);
        if (!$existing) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Transaction non trouvée']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !is_array($data)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Données invalides']);
            return;
        }

        // on renomme total → montant_total si présent
        if (isset($data['montant_total'])) {
            $payload['montant_total'] = $data['montant_total'];
        }
        if (isset($data['date_transaction'])) {
            $payload['date_transaction'] = $data['date_transaction'];
        }
        if (isset($data['id_client'])) {
            $payload['id_client'] = $data['id_client'];
        }
        if (empty($payload)) {
            http_response_code(400);
            echo json_encode(['error' => 'Aucun champ à mettre à jour']);
            return;
        }

        $ok = $this->transactionModel->update($id, $payload);
        if ($ok) {
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Transaction mise à jour']);
        } else {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Impossible de mettre à jour']);
        }
    }

    /**
     * DELETE /transaction/{id}
     */
    public function destroy(int $id): void {
        $existing = $this->transactionModel->getById($id);
        if (!$existing) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Transaction non trouvée']);
            return;
        }
        $ok = $this->transactionModel->delete($id);
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
