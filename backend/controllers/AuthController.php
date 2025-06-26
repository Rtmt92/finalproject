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

        setcookie('token', $token, [
            'expires' => time() + 3600,
            'path' => '/',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        http_response_code(201);
        echo json_encode(['message' => 'Inscription réussie', 'token' => $token]);
    }

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
            'role'  => $user['role'],
            'iat'   => time(),
            'exp'   => time() + 3600,
        ];
        $token = JWT::encode($payload, JwtConfig::SECRET_KEY, 'HS256');

        setcookie('token', $token, [
            'expires' => time() + 3600,
            'path' => '/',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        echo json_encode(['token' => $token, 'role' => $user['role']]);
    }

    public function me(): void {
        header('Content-Type: application/json; charset=utf-8');

        if (!isset($_COOKIE['token'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Token manquant']);
            return;
        }

        try {
            $decoded = JWT::decode($_COOKIE['token'], new Key(JwtConfig::SECRET_KEY, 'HS256'));
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
