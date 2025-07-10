<?php
namespace Controllers;

use Src\Models\Client;
use Config\JwtConfig;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController {
    /**  
     * Modèle Client pour interagir avec la table des utilisateurs  
     * @var Client  
     */
    private Client $clientModel;

    /**
     * Constructeur : instancie le modèle Client
     */
    public function __construct() {
        $this->clientModel = new Client();
    }

    // Inscription d’un nouvel utilisateur
    public function register(): void {
        header('Content-Type: application/json; charset=utf-8');
        // Récupère les données JSON envoyées
        $data = json_decode(file_get_contents('php://input'), true);

        // Validation des champs obligatoires
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

        // Vérification d’un compte existant avec le même email
        if ($this->clientModel->findByEmail($data['email'])) {
            http_response_code(409);
            echo json_encode(['error' => 'Email déjà utilisé']);
            return;
        }

        // Hachage sécurisé du mot de passe
        $data['mot_de_passe'] = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);

        // Création du client en base
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

        // En cas d’erreur à la création
        if (!is_int($id)) {
            http_response_code(500);
            echo json_encode(['error' => 'Impossible de créer l’utilisateur']);
            return;
        }

        // Préparation du payload JWT
        $payload = [
            'iss'   => 'your-app',         // Émetteur du token
            'sub'   => $id,                // Identifiant de l’utilisateur
            'email' => $data['email'],     // Adresse email
            'role'  => $data['role'] ?? 'client',
            'iat'   => time(),             // Date d’émission
            'exp'   => time() + 3600,      // Expiration dans 1 heure
        ];

        // Encodage du token avec la clé secrète
        $token = JWT::encode($payload, JwtConfig::SECRET_KEY, 'HS256');

        // Réponse côté client
        http_response_code(201);
        echo json_encode([
            'message' => 'Inscription réussie',
            'token'   => $token
        ]);
    }

    //Authentification (login)
    public function login(): void {
        header('Content-Type: application/json; charset=utf-8');
        $data = json_decode(file_get_contents('php://input'), true);

        // Vérification des champs requis
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

        // Recherche de l’utilisateur par email
        $user = $this->clientModel->findByEmail($data['email']);
        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Email ou mot de passe incorrect']);
            return;
        }

        // Vérification du mot de passe
        if (!password_verify($data['mot_de_passe'], $user['mot_de_passe'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Email ou mot de passe incorrect']);
            return;
        }

        // Préparation du payload JWT
        $payload = [
            'iss'   => 'DejaVu',
            'sub'   => $user['id_client'],
            'email' => $user['email'],
            'role'  => $user['role'],
            'iat'   => time(),
            'exp'   => time() + 3600,
        ];

        // Génération et envoi du token
        $token = JWT::encode($payload, JwtConfig::SECRET_KEY, 'HS256');
        echo json_encode([
            'message' => 'Connexion réussie',
            'token'   => $token,
            'role'    => $user['role']
        ]);
    }

    //Récupérer les informations de l’utilisateur connecté
     
    public function me(): void {
        header('Content-Type: application/json; charset=utf-8');

        // Récupère le header Authorization
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            http_response_code(401);
            echo json_encode(['error' => 'Token manquant']);
            return;
        }

        try {
            // Décodage du JWT
            $decoded = JWT::decode(
                $matches[1],
                new Key(JwtConfig::SECRET_KEY, 'HS256')
            );

            // Recherche de l’utilisateur en base
            $user = $this->clientModel->getById((int)$decoded->sub);
            if (!$user) {
                http_response_code(404);
                echo json_encode(['error' => 'Utilisateur non trouvé']);
                return;
            }

            // On retire le mot de passe des données retournées
            unset($user['mot_de_passe']);
            echo json_encode($user);

        } catch (\Exception $e) {
            http_response_code(401);
            echo json_encode(['error' => 'Token invalide ou expiré']);
        }
    }
}
