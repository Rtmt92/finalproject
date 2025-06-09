<?php
namespace Controllers;

use Src\Models\Client;
use Config\JwtConfig;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController {
    private Client $clientModel;

    public function __construct() {
        $this->clientModel = new Client();
    }

    /**
     * POST /api/register
     */
    public function register(): void {
        header('Content-Type: application/json; charset=utf-8');
        $data = json_decode(file_get_contents('php://input'), true);

        // Vérification des champs obligatoires
        if (
            empty($data['nom']) ||
            empty($data['prenom']) ||
            empty($data['email']) ||
            empty($data['numero_telephone']) ||
            empty($data['mot_de_passe'])
        ) {
            http_response_code(400);
            echo json_encode(['error' => 'Champs manquants']);
            return;
        }

        // Pas de doublon sur l’email
        if ($this->clientModel->findByEmail($data['email'])) {
            http_response_code(409);
            echo json_encode(['error' => 'Email déjà utilisé']);
            return;
        }

        // Hash du mot de passe
        $data['mot_de_passe'] = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);

        // Création du client
        $id = $this->clientModel->create([
            'nom'              => $data['nom'],
            'prenom'           => $data['prenom'],
            'email'            => $data['email'],
            'numero_telephone' => $data['numero_telephone'],
            'mot_de_passe'     => $data['mot_de_passe'],
            'role'             => $data['role'] ?? 'client',
            'photo_profil'     => $data['photo_profil'] ?? null,
            'description'      => $data['description']  ?? null,
        ]);

        if (!is_int($id)) {
            http_response_code(500);
            echo json_encode(['error' => 'Impossible de créer l’utilisateur']);
            return;
        }

        // Génération du token
        $payload = [
            'iss'   => 'your-app',
            'sub'   => $id,
            'email' => $data['email'],
            'iat'   => time(),
            'exp'   => time() + 3600,
        ];
        $token = JWT::encode($payload, JwtConfig::SECRET_KEY, 'HS256');

        http_response_code(201);
        echo json_encode([
            'message' => 'Inscription réussie',
            'token'   => $token
        ]);
    }

    /**
     * POST /api/login
     */
    public function login(): void {
        header('Content-Type: application/json; charset=utf-8');
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['email']) || empty($data['mot_de_passe'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Email et mot de passe obligatoires']);
            return;
        }

        $user = $this->clientModel->findByEmail($data['email']);
        if (!$user || !password_verify($data['mot_de_passe'], $user['mot_de_passe'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Identifiants invalides']);
            return;
        }

        $payload = [
            'iss'   => 'your-app',
            'sub'   => $user['id_client'],
            'email' => $user['email'],
            'iat'   => time(),
            'exp'   => time() + 3600,
        ];
        $token = JWT::encode($payload, JwtConfig::SECRET_KEY, 'HS256');

        echo json_encode(['token' => $token]);
    }
}
