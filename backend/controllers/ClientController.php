<?php
namespace Controllers;

use Src\Services\ClientService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ClientController {
    private ClientService $clientService; // Service pour gérer les clients

    public function __construct() {
        $this->clientService = new ClientService(); // Instancie le service client
    }

    public function storeFromData(array $data): void {
        $result = $this->clientService->createClient($data); // Tente de créer un client
        if ($result === false) { // Si validation échoue
            http_response_code(400);
            echo json_encode(['error' => 'Champs obligatoires manquants ou invalides']);
            return;
        }
        if (is_int($result)) { // Si création réussie (retourne l’ID)
            http_response_code(201);
            echo json_encode(['message' => 'Client créé', 'id_client' => $result]);
        } else { // Sinon, erreur serveur
            http_response_code(500);
            echo json_encode(['error' => is_string($result) ? $result : 'Erreur lors de la création']);
        }
    }

    public function index(): void {
        $q = $_GET['search'] ?? ''; // Récupère le filtre recherche
        $clients = $this->clientService->getAllClients($q); // Récupère les clients
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($clients); // Renvoie la liste en JSON
    }

    public function show(int $id): void {
        $client = $this->clientService->getClientById($id); // Récupère un client par ID
        if (!$client) { // Si non trouvé
            http_response_code(404);
            echo json_encode(['error' => 'Client non trouvé']);
            return;
        }
        echo json_encode($client); // Renvoie le client
    }

    public function store(): void {
        $data = json_decode(file_get_contents('php://input'), true); // Lit JSON d’entrée
        $this->storeFromData($data); // Délègue la création
    }

    private function authenticate(): object {
        $h = $_SERVER['HTTP_AUTHORIZATION'] ?? ''; // Récupère le header Authorization
        if (!preg_match('/Bearer\s(\S+)/', $h, $m)) { // Vérifie la présence du token
            http_response_code(401);
            echo json_encode(['error' => 'Token manquant']);
            exit;
        }
        try {
            return JWT::decode( // Décode et vérifie le JWT
                $m[1],
                new Key(\Config\JwtConfig::SECRET_KEY, 'HS256')
            );
        } catch (\Exception $e) { // Si token invalide/expiré
            http_response_code(403);
            echo json_encode(['error' => 'Token invalide ou expiré']);
            exit;
        }
    }

    public function update(int $id): void {
        $data = json_decode(file_get_contents('php://input'), true); // Lit JSON d’entrée
        if (!is_array($data)) { // Validation basique
            http_response_code(400);
            echo json_encode(['error' => 'Données invalides']);
            return;
        }

        $result = $this->clientService->updateClient($id, $data); // Tente la mise à jour

        if ($result === 'EMAIL_ALREADY_EXISTS') { // Conflit email
            http_response_code(400);
            echo json_encode(['error' => 'Cette adresse email est déjà utilisée.']);
            return;
        }

        if ($result === false) { // Client non trouvé ou autre échec
            http_response_code(404);
            echo json_encode(['error' => 'Client non trouvé ou impossible à mettre à jour']);
            return;
        }

        echo json_encode(['message' => 'Client mis à jour']); // Succès
    }

    public function destroy(int $id): void {
        $ok = $this->clientService->deleteClient($id); // Tente la suppression
        if (!$ok) { // Si échec
            http_response_code(404);
            echo json_encode(['error' => 'Client non trouvé ou erreur lors de la suppression']);
            return;
        }
        echo json_encode(['message' => 'Client supprimé avec ses données associées']); // Succès
    }

    public function updatePassword(int $id): void {
        $payload = $this->authenticate(); // Vérifie le JWT
        if ((int)$payload->sub !== $id) { // Vérifie l’ID utilisateur
            http_response_code(403);
            echo json_encode(['error' => 'Accès interdit']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true); // Lit JSON d’entrée

        if (
            empty($data['ancien']) ||
            empty($data['nouveau']) ||
            empty($data['confirmation'])
        ) { // Vérifie les champs requis
            http_response_code(400);
            echo json_encode(['error' => 'Champs requis manquants']);
            return;
        }

        $result = $this->clientService->updatePassword(
            $id,
            $data['ancien'],
            $data['nouveau'],
            $data['confirmation']
        ); // Tente la mise à jour du mot de passe

        if ($result === true) { // Succès
            echo json_encode(['message' => 'Mot de passe mis à jour']);
        } else { // Erreur
            http_response_code(400);
            echo json_encode(['error' => $result]);
        }
    }
}
