<?php
namespace Controllers;

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Charge les variables d’environnement depuis le .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

class StripeController {
    // Crée et renvoie une session de paiement Stripe Checkout
    public function createCheckoutSession() {
        \Stripe\Stripe::setApiKey(getenv('STRIPE_SECRET_KEY'));
        header('Content-Type: application/json');

        $input  = json_decode(file_get_contents('php://input'), true);
        $amount = isset($input['amount']) ? (float)$input['amount'] : 0;

        try {
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency'     => 'eur',
                        'product_data' => ['name' => 'Paiement Panier'],
                        'unit_amount'  => (int)($amount * 100),
                    ],
                    'quantity' => 1,
                ]],
                'mode'        => 'payment',
                'success_url' => 'http://localhost:5173/success',
                'cancel_url'  => 'http://localhost:5173/cancel',
            ]);

            echo json_encode(['id' => $session->id]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
