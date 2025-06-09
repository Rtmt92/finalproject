<?php
namespace Controllers;

use Src\Models\TransactionPanier;

class TransactionPanierController {
    private TransactionPanier $model;

    public function __construct() {
        $this->model = new TransactionPanier();
    }

    /** GET /transaction_panier */
    public function index(): void {
        header('Content-Type: application/json');
        echo json_encode($this->model->getAll());
    }

    /** POST /transaction_panier */
    public function store(): void {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['id_panier'], $data['id_transaction'])) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Champs manquants']);
            return;
        }
        $ok = $this->model->create($data);
        if ($ok) {
            http_response_code(201);
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Lien transaction-panier créé']);
        } else {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Impossible de créer']);
        }
    }

    /** DELETE /transaction_panier/{panier}/{transaction} */
    public function destroy(int $idPanier, int $idTransaction): void {
        $ok = $this->model->delete($idPanier, $idTransaction);
        if ($ok) {
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Lien transaction-panier supprimé']);
        } else {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Impossible de supprimer']);
        }
    }
}
