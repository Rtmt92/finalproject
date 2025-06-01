<?php
// 1) Affiche toutes les erreurs (pour dev)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2) Charge l’autoload de Composer
require_once __DIR__ . '/vendor/autoload.php';

use Controllers\ClientController;

// 3) Récupère le chemin demandé et la méthode HTTP
$path   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// 4) On retire les slashs superflus et on découpe
//    Exemple : "/client/5"  → ['client', '5']
$parts = array_values(array_filter(explode('/', $path), fn($p) => $p !== ''));

// 5) Routing basique pour /client
if (isset($parts[0]) && $parts[0] === 'client') {
    $controller = new ClientController();

    // /client
    if (count($parts) === 1) {
        switch ($method) {
            case 'GET':
                // Lire tous les clients
                $controller->index();
                exit;
            case 'POST':
                // Créer un nouveau client
                $controller->store();
                exit;
            default:
                http_response_code(405);
                echo json_encode(['error' => 'Méthode non autorisée']);
                exit;
        }
    }

    // /client/{id}  avec un ID numérique
    if (count($parts) === 2 && is_numeric($parts[1])) {
        $id = (int) $parts[1];
        switch ($method) {
            case 'GET':
                // Lire un client par ID
                $controller->show($id);
                exit;
            case 'PUT':
            case 'PATCH':
                // Mettre à jour le client {id}
                $controller->update($id);
                exit;
            case 'DELETE':
                // Supprimer le client {id}
                $controller->destroy($id);
                exit;
            default:
                http_response_code(405);
                echo json_encode(['error' => 'Méthode non autorisée']);
                exit;
        }
    }
}

// Si on arrive ici, c’est que la route n’existe pas
http_response_code(404);
header('Content-Type: application/json');
echo json_encode(['error' => 'Route non trouvée']);
