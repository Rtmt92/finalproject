<?php
namespace Controllers;

use Src\Models\Client;
use Src\Models\Message;
use Src\Models\Signaler;
use Src\Models\Panier;
use Src\Models\Transaction;
use PDOException;

class ClientController {
    private Client $clientModel;

    public function __construct() {
        $this->clientModel = new Client();
    }

    public function index(): void {
        $clients = $this->clientModel->getAll();
        header('Content-Type: application/json');
        echo json_encode($clients);
    }

    public function show(int $id): void {
        $client = $this->clientModel->getById($id);
        if (!$client) {
            http_response_code(404);
            echo json_encode(['error' => 'Client non trouvé']);
            return;
        }
        echo json_encode($client);
    }

    public function store(): void {
        $data = json_decode(file_get_contents('php://input'), true);

        if (
            empty($data['nom']) ||
            empty($data['prenom']) ||
            empty($data['email']) ||
            empty($data['numero_telephone']) ||
            empty($data['mot_de_passe'])
        ) {
            http_response_code(400);
            echo json_encode(['error' => 'Champs obligatoires manquants']);
            return;
        }

        $data['mot_de_passe'] = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);

        $result = $this->clientModel->create([
            'nom'              => $data['nom'],
            'prenom'           => $data['prenom'],
            'email'            => $data['email'],
            'numero_telephone' => $data['numero_telephone'],
            'mot_de_passe'     => $data['mot_de_passe'],
            'role'             => $data['role'] ?? 'client',
            'photo_profil'     => $data['photo_profil'] ?? null,
            'description'      => $data['description']  ?? null,
        ]);

        if (is_int($result)) {
            http_response_code(201);
            echo json_encode(['message' => 'Client créé', 'id_client' => $result]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => is_string($result) ? $result : 'Erreur lors de la création']);
        }
    }

    public function update(int $id): void {
        $existing = $this->clientModel->getById($id);
        if (!$existing) {
            http_response_code(404);
            echo json_encode(['error' => 'Client non trouvé']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!is_array($data)) {
            http_response_code(400);
            echo json_encode(['error' => 'Données invalides']);
            return;
        }

        // Si mot de passe est présent ET non vide, il sera hashé dans le modèle
        $ok = $this->clientModel->update($id, $data);
        if ($ok) {
            echo json_encode(['message' => 'Client mis à jour']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Impossible de mettre à jour le client']);
        }
    }

    public function destroy(int $id): void {
        $existing = $this->clientModel->getById($id);
        if (!$existing) {
            http_response_code(404);
            echo json_encode(['error' => 'Client non trouvé']);
            return;
        }

        (new Message())->deleteByClient($id);
        (new Signaler())->deleteByClient($id);
        (new Panier())->deleteByClient($id);

        try {
            $this->clientModel->delete($id);
            echo json_encode(['message' => 'Client supprimé avec ses données associées']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur SQL : ' . $e->getMessage()]);
        }
    }
}
