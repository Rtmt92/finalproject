<?php
namespace Controllers;

use Src\Models\Client;
use Src\Models\Message;
use Src\Models\Signaler;
use Src\Models\Panier;
use Src\Models\Transaction;

class ClientController {
    private Client $clientModel;

    public function __construct() {
        // Instancie le modèle Client
        $this->clientModel = new Client();
    }

    /**
     * GET /client
     * Renvoie la liste de tous les clients
     */
    public function index(): void {
        $clients = $this->clientModel->getAll();
        header('Content-Type: application/json');
        echo json_encode($clients);
    }

    /**
     * GET /client/{id}
     * Renvoie un seul client ou 404 si non trouvé
     */
    public function show(int $id): void {
        $client = $this->clientModel->getById($id);
        if (!$client) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Client non trouvé']);
            return;
        }
        header('Content-Type: application/json');
        echo json_encode($client);
    }

    /**
     * POST /client
     * Crée un nouveau client (JSON: nom, prenom, email, numero_telephone, mot_de_passe, role)
     */
    public function store(): void {
        $data = json_decode(file_get_contents('php://input'), true);
        if (
            empty($data['nom']) ||
            empty($data['prenom']) ||
            empty($data['email']) ||
            empty($data['numero_telephone']) ||
            empty($data['mot_de_passe']) ||
            empty($data['role'])
        ) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Champs manquants']);
            return;
        }

        // Hasher le mot de passe
        $data['mot_de_passe'] = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);

        $newId = $this->clientModel->create($data);
        if ($newId) {
            http_response_code(201);
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Client créé', 'id_client' => $newId]);
        } else {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Impossible de créer le client']);
        }
    }

    /**
     * PUT/PATCH /client/{id}
     * Met à jour le client existant
     */
    public function update(int $id): void {
        $existing = $this->clientModel->getById($id);
        if (!$existing) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Client non trouvé']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !is_array($data)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Données invalides']);
            return;
        }

        // Hasher le mot de passe si fourni
        if (!empty($data['mot_de_passe'])) {
            $data['mot_de_passe'] = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);
        }

        $ok = $this->clientModel->update($id, $data);
        if ($ok) {
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Client mis à jour']);
        } else {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Impossible de mettre à jour']);
        }
    }

    /**
     * DELETE /client/{id}
     * Supprime le client et toutes ses données enfant (messages, signalements, paniers, transactions)
     */
    public function destroy(int $id): void {
        $existing = $this->clientModel->getById($id);
        if (!$existing) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Client non trouvé']);
            return;
        }

        // Suppression en cascade des données enfants
        (new Message())->deleteByClient($id);
        (new Signaler())->deleteByClient($id);
        (new Panier())->deleteByClient($id);
        (new Transaction())->deleteByClient($id);

        // Suppression du client
        try {
            $this->clientModel->delete($id);
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Client et ses données enfants supprimés']);
        } catch (PDOException $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Erreur SQL : ' . $e->getMessage()]);
        }
    }
}
