<?php
namespace Controllers;

use Src\Services\ClientService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ClientController {
    private ClientService $clientService;

    public function __construct() {
        $this->clientService = new ClientService();
    }

    public function storeFromData(array $data): void {
        $result = $this->clientService->createClient($data);
        if ($result === false) {
            http_response_code(400);
            echo json_encode(['error' => 'Champs obligatoires manquants ou invalides']);
            return;
        }
        if (is_int($result)) {
            http_response_code(201);
            echo json_encode(['message' => 'Client créé', 'id_client' => $result]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => is_string($result) ? $result : 'Erreur lors de la création']);
        }
    }

    public function index(): void {
        $q = $_GET['search'] ?? '';
        $clients = $this->clientService->getAllClients($q);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($clients);
    }

    public function show(int $id): void {
        $client = $this->clientService->getClientById($id);
        if (!$client) {
            http_response_code(404);
            echo json_encode(['error' => 'Client non trouvé']);
            return;
        }
        echo json_encode($client);
    }

    public function store(): void {
        $data = json_decode(file_get_contents('php://input'), true);
        $this->storeFromData($data);
    }

    private function authenticate(): object {
        $h = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!preg_match('/Bearer\s(\S+)/', $h, $m)) {
            http_response_code(401);
            echo json_encode(['error' => 'Token manquant']);
            exit;
        }

        try {
            return JWT::decode(
                $m[1],
                new Key(\Config\JwtConfig::SECRET_KEY, 'HS256')
            );
        } catch (\Exception $e) {
            http_response_code(403);
            echo json_encode(['error' => 'Token invalide ou expiré']);
            exit;
        }
    }

    public function update(int $id): void {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!is_array($data)) {
            http_response_code(400);
            echo json_encode(['error' => 'Données invalides']);
            return;
        }

        $ok = $this->clientService->updateClient($id, $data);
        if (!$ok) {
            http_response_code(404);
            echo json_encode(['error' => 'Client non trouvé ou impossible à mettre à jour']);
            return;
        }
        echo json_encode(['message' => 'Client mis à jour']);

        $result = $clientModel->update($id, $data);

        if ($result === 'EMAIL_ALREADY_EXISTS') {
            http_response_code(400);
            echo json_encode(['error' => 'Cette adresse email est déjà utilisée.']);
            exit;
        }
        if (!$result) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de la mise à jour.']);
            exit;
        }
    }

    public function destroy(int $id): void {
        $ok = $this->clientService->deleteClient($id);
        if (!$ok) {
            http_response_code(404);
            echo json_encode(['error' => 'Client non trouvé ou erreur lors de la suppression']);
            return;
        }
        echo json_encode(['message' => 'Client supprimé avec ses données associées']);
    }

    public function updatePassword(int $id): void {
        $payload = $this->authenticate();

        if ((int)$payload->sub !== $id) {
            http_response_code(403);
            echo json_encode(['error' => 'Accès interdit']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (
            empty($data['ancien']) ||
            empty($data['nouveau']) ||
            empty($data['confirmation'])
        ) {
            http_response_code(400);
            echo json_encode(['error' => 'Champs requis manquants']);
            return;
        }

        $result = $this->clientService->updatePassword($id, $data['ancien'], $data['nouveau'], $data['confirmation']);

        if ($result === true) {
            echo json_encode(['message' => 'Mot de passe mis à jour']);
        } else {
            http_response_code(400);
            echo json_encode(['error' => $result]);
        }
    }
}
