<?php

declare(strict_types=1);

// --- Affichage des erreurs (dev) ---
error_reporting(E_ALL);
ini_set('display_errors', '1');

$possiblePaths = [
    __DIR__ . '/core/Database.php',
    __DIR__ . '/config/DatabaseConfig.php',
    __DIR__ . '/src/Core/Database.php',
];

$loaded = false;
foreach ($possiblePaths as $dbPath) {
    if (file_exists($dbPath)) {
        require_once $dbPath;
        $loaded = true;
        break;
    }
}
if (!$loaded) {
    http_response_code(500);
    echo json_encode(['error' => 'Database.php introuvable']);
    exit;
}

foreach (glob(__DIR__ . '/src/Models/*.php') as $f) require_once $f;
foreach (glob(__DIR__ . '/controllers/*.php') as $f) require_once $f;
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/core/Router.php';

use Config\JwtConfig;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
$allowedOrigins = [
    'http://localhost:3000',
    'http://4.233.136.179:8080'
];

if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
}

header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

function authenticate(): object {
    $h = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/Bearer\s(\S+)/', $h, $m)) {
        http_response_code(401);
        echo json_encode(['error' => 'Token manquant']);
        exit;
    }
    try {
        return JWT::decode($m[1], new Key(JwtConfig::SECRET_KEY, 'HS256'));
    } catch (\Exception $e) {
        http_response_code(401);
        echo json_encode(['error' => 'Token invalide']);
        exit;
    }
}

$url = $_GET['url'] ?? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$router = new Router($url);

$router->put('/client/:id/password', fn($id) => (new Controllers\ClientController())->updatePassword((int)$id));

$router->post('/api/register', fn() => (new Controllers\AuthController())->register());
$router->post('/api/login', fn() => (new Controllers\AuthController())->login());
$router->get('/api/me', fn() => (new Controllers\AuthController())->me());

$router->get('/client', fn() => (new Controllers\ClientController())->index());
$router->post('/client', fn() => (new Controllers\ClientController())->store());
$router->get('/client/:id', fn($id) => (new Controllers\ClientController())->show((int)$id));
$router->put('/client/:id', fn($id) => (new Controllers\ClientController())->update((int)$id));
$router->delete('/client/:id', fn($id) => (new Controllers\ClientController())->destroy((int)$id));
$router->post('/upload-photo', fn() => require __DIR__ . '/uploads/upload-photo.php');

$router->get('/message', fn() => (new Controllers\MessageController())->index());
$router->post('/message', fn() => (new Controllers\MessageController())->store());
$router->get('/message/:id', fn($id) => (new Controllers\MessageController())->show((int)$id));
$router->put('/message/:id', fn($id) => (new Controllers\MessageController())->update((int)$id));
$router->delete('/message/:id', fn($id) => (new Controllers\MessageController())->destroy((int)$id));

$router->get('/signaler', fn() => (new Controllers\SignalerController())->index());
$router->post('/signaler', fn() => (new Controllers\SignalerController())->store());
$router->get('/signaler/:id', fn($id) => (new Controllers\SignalerController())->show((int)$id));
$router->put('/signaler/:id', fn($id) => (new Controllers\SignalerController())->update((int)$id));
$router->delete('/signaler/:id', fn($id) => (new Controllers\SignalerController())->destroy((int)$id));

$router->get('/api/produit', fn() => (new Controllers\ProduitController())->index());
$router->post('/api/produit', fn() => (new Controllers\ProduitController())->store());
$router->get('/api/produit/:id', fn($id) => (new Controllers\ProduitController())->show((int)$id));
$router->put('/api/produit/:id', fn($id) => (new Controllers\ProduitController())->update((int)$id));
$router->delete('/api/produit/:id', fn($id) => (new Controllers\ProduitController())->destroy((int)$id));
$router->delete('/api/produit/:pid/image/:iid', fn($pid, $iid) => (new Controllers\ProduitController())->deleteImage((int)$pid, (int)$iid));
$router->post('/api/produit/:id/image', fn($id) => (new Controllers\ProduitController())->uploadImage((int)$id));
$router->get('/api/produit/random', fn() => (new Controllers\ProduitController())->random());

$router->get('/image', fn() => (new Controllers\ImageController())->index());
$router->post('/image', fn() => (new Controllers\ImageController())->store());
$router->get('/image/:id', fn($id) => (new Controllers\ImageController())->show((int)$id));
$router->put('/image/:id', fn($id) => (new Controllers\ImageController())->update((int)$id));
$router->delete('/image/:id', fn($id) => (new Controllers\ImageController())->destroy((int)$id));

$router->get('/categorie', fn() => (new Controllers\CategorieController())->index());
$router->post('/categorie', fn() => (new Controllers\CategorieController())->store());
$router->get('/categorie/:id', fn($id) => (new Controllers\CategorieController())->show((int)$id));
$router->put('/categorie/:id', fn($id) => (new Controllers\CategorieController())->update((int)$id));
$router->delete('/categorie/:id', fn($id) => (new Controllers\CategorieController())->destroy((int)$id));

$router->get('/panier', fn() => authenticate() && (new Controllers\PanierController())->getMyPanier());
$router->get('/panier/:id', fn($id) => (new Controllers\PanierController())->show((int)$id));
$router->put('/panier/:id', fn($id) => (new Controllers\PanierController())->update((int)$id));
$router->delete('/panier/:id', fn($id) => (new Controllers\PanierController())->destroy((int)$id));
$router->delete('/panier/:id/vider', fn($id) => (new Controllers\PanierController())->vider((int)$id));

$router->get('/transaction', fn() => (new Controllers\TransactionController())->index());
$router->post('/transaction', fn() => (new Controllers\TransactionController())->store());
$router->get('/transaction/:id', fn($id) => (new Controllers\TransactionController())->show((int)$id));
$router->put('/transaction/:id', fn($id) => (new Controllers\TransactionController())->update((int)$id));
$router->delete('/transaction/:id', fn($id) => (new Controllers\TransactionController())->destroy((int)$id));

$router->get('/panier_produit', fn() => (new Controllers\PanierProduitController())->index());
$router->post('/panier_produit', fn() => (new Controllers\PanierProduitController())->store());
$router->delete('/panier_produit/:p/:pr', fn($p, $pr) => (new Controllers\PanierProduitController())->destroy((int)$p, (int)$pr));
$router->post('/panier_produit', fn() => (new Controllers\PanierController())->ajouterProduit());
$router->get('/fake-login', fn() => (new Controllers\PanierProduitController())->testLogin());

$router->get('/produit_image', fn() => (new Controllers\ProduitImageController())->index());
$router->post('/produit_image', fn() => (new Controllers\ProduitImageController())->store());
$router->delete('/produit_image/:pr/:i', fn($pr, $i) => (new Controllers\ProduitImageController())->destroy((int)$pr, (int)$i));

$router->get('/transaction_panier', fn() => (new Controllers\TransactionPanierController())->index());
$router->post('/transaction_panier', fn() => (new Controllers\TransactionPanierController())->store());
$router->delete('/transaction_panier/:p/:t', fn($p, $t) => (new Controllers\TransactionPanierController())->destroy((int)$p, (int)$t));
$router->post('/enregistrer-transaction', fn() => require __DIR__ . '/save-transaction.php');

$router->get('/api/me', function() {
    $payload = authenticate();
    $client = (new Src\Models\Client())->getById((int)$payload->sub);
    if (!$client) {
        http_response_code(404);
        echo json_encode(['error' => 'Utilisateur non trouvé']);
        return;
    }
    echo json_encode($client);
});

$router->post('/create-checkout-session', fn() => (new Controllers\StripeController())->createCheckoutSession());
$router->post('/payment-intent', fn() => require __DIR__ . '/payment-intent.php');
$router->post('/api/upload-images', fn() => (new Controllers\UploadImageController())->uploadMultiple());

$router->run();
