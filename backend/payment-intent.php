<?php
require __DIR__ . '/vendor/autoload.php';

use Stripe\Stripe;
use Stripe\PaymentIntent;

header('Content-Type: application/json');

// 🔐 Clé secrète Stripe
Stripe::setApiKey('sk_test_51RcVcGPut8fuuvIhfsjBzBm8xrPKPP6LugDijy0RUsJDsdJZr2umABDkx78Fhl6zVdqChm5GGzFMRTJPQealR0gh005FxLmt32');

// 🔒 CORS pour autoriser requêtes frontend
$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
$allowedOrigins = [
    'http://localhost:3000',
    'http://localhost:5173'
];

if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
}
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 📥 Lire montant envoyé
$input = json_decode(file_get_contents("php://input"), true);
$amount = isset($input['amount']) ? (int)($input['amount'] * 100) : 0; // € vers centimes

if ($amount <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Montant invalide']);
    exit;
}

try {
    $intent = PaymentIntent::create([
        'amount' => $amount,
        'currency' => 'eur',
        'payment_method_types' => ['card'],
    ]);
    echo json_encode(['clientSecret' => $intent->client_secret]);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
