<?php
namespace Controllers;

use Src\Models\Client;

class ClientController {
    private Client $clientModel;

    public function __construct() {
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
     * Crée un nouveau client au format JSON { nom, prenom, email, numero_telephone, mot_de_passe, role }
     */
    public function store(): void {
        // Lecture du JSON brut envoyé dans le corps de la requête
        $data = json_decode(file_get_contents('php://input'), true);

        // Vérifications minimales
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

        // Hasher le mot de passe avant insertion
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
        // Vérifier si le client existe
        $existing = $this->clientModel->getById($id);
        if (!$existing) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Client non trouvé']);
            return;
        }

        // Lecture du JSON brut envoyé dans le corps de la requête
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !is_array($data)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Données invalides']);
            return;
        }

        // Si mot_de_passe fourni, on le hash
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
     * Supprime le client existant
     */
    public function destroy(int $id): void {
        // Vérifier si le client existe
        $existing = $this->clientModel->getById($id);
        if (!$existing) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Client non trouvé']);
            return;
        }

        $ok = $this->clientModel->delete($id);
        if ($ok) {
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Client supprimé']);
        } else {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Impossible de supprimer']);
        }
    }
}
