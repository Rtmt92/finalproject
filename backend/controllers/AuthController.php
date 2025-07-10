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

    public function register(): void {
        header('Content-Type: application/json; charset=utf-8');
        $data = json_decode(file_get_contents('php://input'), true);

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

        if ($this->clientModel->findByEmail($data['email'])) {
            http_response_code(409);
            echo json_encode(['error' => 'Email déjà utilisé']);
            return;
        }

        $data['mot_de_passe'] = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);

        $id = $this->clientModel->create([
            'nom'              => $data['nom'],
            'prenom'           => $data['prenom'],
            'email'            => $data['email'],
            'numero_telephone' => $data['numero_telephone'],
            'mot_de_passe'     => $data['mot_de_passe'],
            'role'             => $data['role'] ?? 'client',
            'photo_profil'     => $data['photo_profil'] ?? null,
            'description'      => $data['description'] ?? null,
        ]);

        if (!is_int($id)) {
            http_response_code(500);
            echo json_encode(['error' => 'Impossible de créer l’utilisateur']);
            return;
        }

        $payload = [
            'iss'   => 'your-app',
            'sub'   => $id,
            'email' => $data['email'],
            'role'  => $data['role'] ?? 'client',
            'iat'   => time(),
            'exp'   => time() + 3600,
        ];

        $token = JWT::encode($payload, JwtConfig::SECRET_KEY, 'HS256');

        http_response_code(201);
        echo json_encode(['message' => 'Inscription réussie', 'token' => $token]);
    }

    public function login(): void {
        header('Content-Type: application/json; charset=utf-8');
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['email'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Veuillez entrer votre email']);
            return;
        }
        if (empty($data['mot_de_passe'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Veuillez entrer votre mot de passe']);
            return;
        }

        $user = $this->clientModel->findByEmail($data['email']);
        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Email ou mot de passe incorrect']);
            return;
        }

        if (!password_verify($data['mot_de_passe'], $user['mot_de_passe'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Email ou mot de passe incorrect']);
            return;
        }

        $payload = [
            'iss'   => 'DejaVu',
            'sub'   => $user['id_client'],
            'email' => $user['email'],
            'role'  => $user['role'],
            'iat'   => time(),
            'exp'   => time() + 3600,
        ];

        $token = JWT::encode($payload, JwtConfig::SECRET_KEY, 'HS256');
        echo json_encode(['message' => 'Connexion réussie', 'token' => $token, 'role' => $user['role']]);
    }


    public function me(): void {
    header('Content-Type: application/json; charset=utf-8');

    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

    if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        http_response_code(401);
        echo json_encode(['error' => 'Token manquant']);
        return;
    }

    try {
        $decoded = JWT::decode($matches[1], new Key(JwtConfig::SECRET_KEY, 'HS256'));
        $user = $this->clientModel->getById((int)$decoded->sub);

        if (!$user) {
            http_response_code(404);
            echo json_encode(['error' => 'Utilisateur non trouvé']);
            return;
        }

        unset($user['mot_de_passe']);
        echo json_encode($user);

    } catch (\Exception $e) {
        http_response_code(401);
        echo json_encode(['error' => 'Token invalide ou expiré']);
    }
}

}
