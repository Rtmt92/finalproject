<?php
declare(strict_types=1);

// --- Affichage des erreurs (dev) ---
error_reporting(E_ALL);
ini_set('display_errors', '1');

// --- Chargement de la config DB ---
$possiblePaths = [
    __DIR__ . '/config/DatabaseConfig.php',
    __DIR__ . '/src/Core/Database.php'
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

// --- Chargement des Models et Controllers ---
foreach (glob(__DIR__ . '/src/Models/*.php') as $f) require_once $f;
foreach (glob(__DIR__ . '/controllers/*.php') as $f) require_once $f;

// --- Composer & JWT ---
require_once __DIR__ . '/vendor/autoload.php';
use Config\JwtConfig;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Controllers\AuthController;
use Controllers\ClientController;
use Controllers\MessageController;
use Controllers\SignalerController;
use Controllers\ProduitController;
use Controllers\ImageController;
use Controllers\CategorieController;
use Controllers\PanierController;
use Controllers\TransactionController;
use Controllers\PanierProduitController;
use Controllers\ProduitImageController;
use Controllers\TransactionPanierController;

// --- CORS & JSON headers ---
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

/** Vérifie le Bearer JWT, ou renvoie 401 */
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

/** Dispatch simplifié */
function dispatch(string $pattern, array|string $methods, callable $cb): bool {
    global $uri, $method;
    if (!in_array($method, (array)$methods)) return false;
    if (preg_match($pattern, $uri, $m)) {
        array_shift($m);
        call_user_func_array($cb, $m);
        exit;
    }
    return false;
}

// --- ENDPOINTS AUTH (/api) ---
dispatch('#^/api/register$#', ['POST'], fn()=> (new AuthController())->register());
dispatch('#^/api/login$#',    ['POST'], fn()=> (new AuthController())->login());
dispatch('#^/api/me$#', ['GET'], function() {
  $payload = authenticate();
  $client  = (new \Src\Models\Client())->getById((int)$payload->sub);
  if (!$client) {
    http_response_code(404);
    echo json_encode(['error'=>'Utilisateur non trouvé']);
    return;
  }
  header('Content-Type: application/json');
  echo json_encode($client);
});


// --- CRUD CLIENT ---
dispatch('#^/client$#',         ['GET'],         fn()=> (new ClientController())->index());
dispatch('#^/client$#',         ['POST'],        fn()=> (new ClientController())->store());
dispatch('#^/client/(\d+)$#',    ['GET'],         fn($i)=>(new ClientController())->show((int)$i));
dispatch('#^/client/(\d+)$#',    ['PUT','PATCH'], fn($i)=>(new ClientController())->update((int)$i));
dispatch('#^/client/(\d+)$#',    ['DELETE'],      fn($i)=>(new ClientController())->destroy((int)$i));

// --- CRUD MESSAGE ---
dispatch('#^/message$#',         ['GET'],         fn()=> (new MessageController())->index());
dispatch('#^/message$#',         ['POST'],        fn()=> (new MessageController())->store());
dispatch('#^/message/(\d+)$#',    ['GET'],         fn($i)=>(new MessageController())->show((int)$i));
dispatch('#^/message/(\d+)$#',    ['PUT','PATCH'], fn($i)=>(new MessageController())->update((int)$i));
dispatch('#^/message/(\d+)$#',    ['DELETE'],      fn($i)=>(new MessageController())->destroy((int)$i));

// --- CRUD SIGNALEMENT ---
dispatch('#^/signaler$#',        ['GET'],         fn()=> (new SignalerController())->index());
dispatch('#^/signaler$#',        ['POST'],        fn()=> (new SignalerController())->store());
dispatch('#^/signaler/(\d+)$#',   ['GET'],         fn($i)=>(new SignalerController())->show((int)$i));
dispatch('#^/signaler/(\d+)$#',   ['PUT','PATCH'], fn($i)=>(new SignalerController())->update((int)$i));
dispatch('#^/signaler/(\d+)$#',   ['DELETE'],      fn($i)=>(new SignalerController())->destroy((int)$i));

// --- CRUD PRODUIT ---
// CRUD PRODUIT AVEC PRÉFIXE /api
dispatch('#^/api/produit$#',         ['GET'],    fn() => (new ProduitController())->index());
dispatch('#^/api/produit$#',         ['POST'],   fn() => (new ProduitController())->store());
dispatch('#^/api/produit/(\d+)$#',   ['GET'],    fn($i)=>(new ProduitController())->show((int)$i));
dispatch('#^/api/produit/(\d+)$#',   ['PUT','PATCH'], fn($i)=>(new ProduitController())->update((int)$i));
dispatch('#^/api/produit/(\d+)$#',   ['DELETE'], fn($i)=>(new ProduitController())->destroy((int)$i));

// --- ENDPOINT PRODUIT RANDOM ---
dispatch('#^/api/produit/random$#',['GET'], fn()=> (new ProduitController())->random());

// --- CRUD IMAGE ---
dispatch('#^/image$#',           ['GET'],         fn()=> (new ImageController())->index());
dispatch('#^/image$#',           ['POST'],        fn()=> (new ImageController())->store());
dispatch('#^/image/(\d+)$#',      ['GET'],         fn($i)=>(new ImageController())->show((int)$i));
dispatch('#^/image/(\d+)$#',      ['PUT','PATCH'], fn($i)=>(new ImageController())->update((int)$i));
dispatch('#^/image/(\d+)$#',      ['DELETE'],      fn($i)=>(new ImageController())->destroy((int)$i));

// --- CRUD CATEGORIE ---
dispatch('#^/categorie$#',       ['GET'],         fn()=> (new CategorieController())->index());
dispatch('#^/categorie$#',       ['POST'],        fn()=> (new CategorieController())->store());
dispatch('#^/categorie/(\d+)$#',  ['GET'],         fn($i)=>(new CategorieController())->show((int)$i));
dispatch('#^/categorie/(\d+)$#',  ['PUT','PATCH'], fn($i)=>(new CategorieController())->update((int)$i));
dispatch('#^/categorie/(\d+)$#',  ['DELETE'],      fn($i)=>(new CategorieController())->destroy((int)$i));

// --- CRUD PANIER ---
dispatch('#^/panier$#',          ['GET'],         fn()=> (new PanierController())->index());
dispatch('#^/panier$#',          ['POST'],        fn()=> (new PanierController())->store());
dispatch('#^/panier/(\d+)$#',     ['GET'],         fn($i)=>(new PanierController())->show((int)$i));
dispatch('#^/panier/(\d+)$#',     ['PUT','PATCH'], fn($i)=>(new PanierController())->update((int)$i));
dispatch('#^/panier/(\d+)$#',     ['DELETE'],      fn($i)=>(new PanierController())->destroy((int)$i));

// --- CRUD TRANSACTION ---
dispatch('#^/transaction$#',      ['GET'],         fn()=> (new TransactionController())->index());
dispatch('#^/transaction$#',      ['POST'],        fn()=> (new TransactionController())->store());
dispatch('#^/transaction/(\d+)$#', ['GET'],        fn($i)=>(new TransactionController())->show((int)$i));
dispatch('#^/transaction/(\d+)$#', ['PUT','PATCH'],fn($i)=>(new TransactionController())->update((int)$i));
dispatch('#^/transaction/(\d+)$#', ['DELETE'],     fn($i)=>(new TransactionController())->destroy((int)$i));

// --- PIVOTS ---
dispatch('#^/panier_produit$#',             ['GET'],  fn()=> (new PanierProduitController())->index());
dispatch('#^/panier_produit$#',             ['POST'], fn()=> (new PanierProduitController())->store());
dispatch('#^/panier_produit/(\d+)/(\d+)$#', ['DELETE'], fn($p,$pr)=>(new PanierProduitController())->destroy((int)$p,(int)$pr));

dispatch('#^/produit_image$#',              ['GET'],  fn()=> (new ProduitImageController())->index());
dispatch('#^/produit_image$#',              ['POST'], fn()=> (new ProduitImageController())->store());
dispatch('#^/produit_image/(\d+)/(\d+)$#',   ['DELETE'], fn($pr,$i)=>(new ProduitImageController())->destroy((int)$pr,(int)$i));

dispatch('#^/transaction_panier$#',         ['GET'],  fn()=> (new TransactionPanierController())->index());
dispatch('#^/transaction_panier$#',         ['POST'], fn()=> (new TransactionPanierController())->store());
dispatch('#^/transaction_panier/(\d+)/(\d+)$#',['DELETE'],fn($p,$t)=>(new TransactionPanierController())->destroy((int)$p,(int)$t));

dispatch('#^/api/me$#', ['GET'], function() {
    $payload = authenticate();
    // Récupère l’utilisateur complet pour photo & description
    $client = (new \Src\Models\Client())->getById((int)$payload->sub);
    if (!$client) {
        http_response_code(404);
        echo json_encode(['error' => 'Utilisateur non trouvé']);
        return;
    }
    header('Content-Type: application/json');
    echo json_encode($client);
});

// 404 par défaut
http_response_code(404);
echo json_encode(['error' => 'Route non trouvée']);
