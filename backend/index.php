<?php
// backend/index.php
// Active l'affichage des erreurs pour debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Inclusion manuelle des classes sans autoloader
// Chargement de la configuration DB
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

// Models
foreach (glob(__DIR__ . '/src/Models/*.php') as $modelFile) {
    require_once $modelFile;
}
// Core (Database)
// La classe Database est dans config/Database.php avec namespace Core;
// on l'a déjà chargé.

// Controllers
foreach (glob(__DIR__ . '/controllers/*.php') as $ctrlFile) {
    require_once $ctrlFile;
}

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

// Routeur minimal
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

function dispatch($pattern, $methods, $callback) {
    global $uri, $method;
    if (!in_array($method, (array)$methods)) return false;
    if (preg_match($pattern, $uri, $matches)) {
        array_shift($matches);
        call_user_func_array($callback, $matches);
        exit;
    }
    return false;
}

// CRUD routes
// CLIENT
dispatch('#^/client$#', ['GET'], function() { (new ClientController())->index(); });
dispatch('#^/client$#', ['POST'], function() { (new ClientController())->store(); });
dispatch('#^/client/(\d+)$#', ['GET'], function($id) { (new ClientController())->show((int)$id); });
dispatch('#^/client/(\d+)$#', ['PUT','PATCH'], function($id) { (new ClientController())->update((int)$id); });
dispatch('#^/client/(\d+)$#', ['DELETE'], function($id) { (new ClientController())->destroy((int)$id); });

// MESSAGE
dispatch('#^/message$#', ['GET'], function() { (new MessageController())->index(); });
dispatch('#^/message$#', ['POST'], function() { (new MessageController())->store(); });
dispatch('#^/message/(\d+)$#', ['GET'], function($id) { (new MessageController())->show((int)$id); });
dispatch('#^/message/(\d+)$#', ['PUT','PATCH'], function($id) { (new MessageController())->update((int)$id); });
dispatch('#^/message/(\d+)$#', ['DELETE'], function($id) { (new MessageController())->destroy((int)$id); });

// SIGNALER
dispatch('#^/signaler$#', ['GET'], function() { (new SignalerController())->index(); });
dispatch('#^/signaler$#', ['POST'], function() { (new SignalerController())->store(); });
dispatch('#^/signaler/(\d+)$#', ['GET'], function($id) { (new SignalerController())->show((int)$id); });
dispatch('#^/signaler/(\d+)$#', ['PUT','PATCH'], function($id) { (new SignalerController())->update((int)$id); });
dispatch('#^/signaler/(\d+)$#', ['DELETE'], function($id) { (new SignalerController())->destroy((int)$id); });

// PRODUIT
dispatch('#^/produit$#', ['GET'], function() { (new ProduitController())->index(); });
dispatch('#^/produit$#', ['POST'], function() { (new ProduitController())->store(); });
dispatch('#^/produit/(\d+)$#', ['GET'], function($id) { (new ProduitController())->show((int)$id); });
dispatch('#^/produit/(\d+)$#', ['PUT','PATCH'], function($id) { (new ProduitController())->update((int)$id); });
dispatch('#^/produit/(\d+)$#', ['DELETE'], function($id) { (new ProduitController())->destroy((int)$id); });

// IMAGE
dispatch('#^/image$#', ['GET'], function() { (new ImageController())->index(); });
dispatch('#^/image$#', ['POST'], function() { (new ImageController())->store(); });
dispatch('#^/image/(\d+)$#', ['GET'], function($id) { (new ImageController())->show((int)$id); });
dispatch('#^/image/(\d+)$#', ['PUT','PATCH'], function($id) { (new ImageController())->update((int)$id); });
dispatch('#^/image/(\d+)$#', ['DELETE'], function($id) { (new ImageController())->destroy((int)$id); });

// CATEGORIE
dispatch('#^/categorie$#', ['GET'], function() { (new CategorieController())->index(); });
dispatch('#^/categorie$#', ['POST'], function() { (new CategorieController())->store(); });
dispatch('#^/categorie/(\d+)$#', ['GET'], function($id) { (new CategorieController())->show((int)$id); });
dispatch('#^/categorie/(\d+)$#', ['PUT','PATCH'], function($id) { (new CategorieController())->update((int)$id); });
dispatch('#^/categorie/(\d+)$#', ['DELETE'], function($id) { (new CategorieController())->destroy((int)$id); });

// PANIER
dispatch('#^/panier$#', ['GET'], function() { (new PanierController())->index(); });
dispatch('#^/panier$#', ['POST'], function() { (new PanierController())->store(); });
dispatch('#^/panier/(\d+)$#', ['GET'], function($id) { (new PanierController())->show((int)$id); });
dispatch('#^/panier/(\d+)$#', ['PUT','PATCH'], function($id) { (new PanierController())->update((int)$id); });
dispatch('#^/panier/(\d+)$#', ['DELETE'], function($id) { (new PanierController())->destroy((int)$id); });

// TRANSACTION
dispatch('#^/transaction$#', ['GET'], function() { (new TransactionController())->index(); });
dispatch('#^/transaction$#', ['POST'], function() { (new TransactionController())->store(); });
dispatch('#^/transaction/(\d+)$#', ['GET'], function($id) { (new TransactionController())->show((int)$id); });
dispatch('#^/transaction/(\d+)$#', ['PUT','PATCH'], function($id) { (new TransactionController())->update((int)$id); });
dispatch('#^/transaction/(\d+)$#', ['DELETE'], function($id) { (new TransactionController())->destroy((int)$id); });

// PANIER_PRODUIT (pivot)
dispatch('#^/panier_produit$#', ['GET'], function() { (new PanierProduitController())->index(); });
dispatch('#^/panier_produit$#', ['POST'], function() { (new PanierProduitController())->store(); });
dispatch('#^/panier_produit/(\d+)/(\d+)$#', ['DELETE'], function($p, $pr) { (new PanierProduitController())->destroy((int)$p, (int)$pr); });

// PRODUIT_IMAGE (pivot)
dispatch('#^/produit_image$#', ['GET'], function() { (new ProduitImageController())->index(); });
dispatch('#^/produit_image$#', ['POST'], function() { (new ProduitImageController())->store(); });
dispatch('#^/produit_image/(\d+)/(\d+)$#', ['DELETE'], function($pr, $i) { (new ProduitImageController())->destroy((int)$pr, (int)$i); });

// TRANSACTION_PANIER (pivot)
dispatch('#^/transaction_panier$#', ['GET'], function() { (new TransactionPanierController())->index(); });
dispatch('#^/transaction_panier$#', ['POST'], function() { (new TransactionPanierController())->store(); });
dispatch('#^/transaction_panier/(\d+)/(\d+)$#', ['DELETE'], function($p, $t) { (new TransactionPanierController())->destroy((int)$p, (int)$t); });

// 404
http_response_code(404);
header('Content-Type: application/json');
echo json_encode(['error' => 'Route non trouvée']);
