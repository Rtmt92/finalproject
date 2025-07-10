<?php
namespace Controllers;

use Src\Services\TransactionPanierService;

class TransactionPanierController {
    private TransactionPanierService $service; // Service pour gérer les liens transaction ↔ panier

    public function __construct() {
        $this->service = new TransactionPanierService(); // Initialise le service
    }

    // GET /transaction_panier : renvoie tous les liens transaction-panier
    public function index(): void {
        header('Content-Type: application/json');
        echo json_encode($this->service->getAll());
    }

    // POST /transaction_panier : crée un lien entre transaction et panier
    public function store(): void {
        $data = json_decode(file_get_contents('php://input'), true); // Lit JSON d’entrée
        if (!isset($data['id_panier'], $data['id_transaction'])) { // Vérifie les champs requis
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Champs manquants']);
            return;
        }
        $ok = $this->service->create($data); // Crée le lien
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

    // DELETE /transaction_panier/{idPanier}/{idTransaction} : supprime un lien transaction-panier
    public function destroy(int $idPanier, int $idTransaction): void {
        $ok = $this->service->delete($idPanier, $idTransaction); // Supprime le lien
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
