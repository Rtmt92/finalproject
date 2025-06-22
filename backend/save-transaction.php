<?php

require_once __DIR__ . '/vendor/autoload.php';

use Src\Models\Transaction;
use Src\Models\TransactionPanier;

// 🔐 Sécurité CORS
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 📥 Lecture du JSON reçu
$data = json_decode(file_get_contents("php://input"), true);

$montant = $data['amount'] ?? null;
$id_client = $data['id_client'] ?? null;
$id_panier = $data['id_panier'] ?? null;

if (!$montant || !$id_client || !$id_panier) {
    http_response_code(400);
    echo json_encode(['error' => 'Données manquantes']);
    exit;
}

try {
    // 🧾 Enregistrement de la transaction
    $transactionModel = new Transaction();
    $transactionId = $transactionModel->create([
        'montant_total' => $montant,
        'date_transaction' => date('Y-m-d H:i:s'),
        'id_client' => $id_client
    ]);

    if (!$transactionId) {
        throw new Exception("Échec de création de la transaction.");
    }

    // 🔗 Lien avec le panier
    $linkModel = new TransactionPanier();
    $ok = $linkModel->create([
        'id_panier' => $id_panier,
        'id_transaction' => $transactionId
    ]);

    if (!$ok) {
        throw new Exception("Échec de liaison panier-transaction.");
    }

    http_response_code(201);
    echo json_encode([
        'message' => 'Transaction enregistrée avec succès',
        'id_transaction' => $transactionId
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
