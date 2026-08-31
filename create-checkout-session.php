<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$amount = isset($data['amount']) ? (float) $data['amount'] : 0;
$locale = isset($data['locale']) ? preg_replace('/[^a-z]/', '', strtolower($data['locale'])) : 'it';
$allowedLocales = ['it', 'fr', 'en'];
if (!in_array($locale, $allowedLocales, true)) $locale = 'auto';

if ($amount <= 0 || $amount > 100000) {
    http_response_code(400);
    echo json_encode(['error' => 'Importo non valido.']);
    exit;
}

$amountCents = (int) round($amount * 100);

$postFields = [
    'mode' => 'payment',
    'success_url' => SITE_URL . '/success.html?session_id={CHECKOUT_SESSION_ID}',
    'cancel_url'  => SITE_URL . '/cancel.html',
    'line_items[0][price_data][currency]' => 'eur',
    'line_items[0][price_data][product_data][name]' => 'Donazione a Mano Amica',
    'line_items[0][price_data][unit_amount]' => $amountCents,
    'line_items[0][quantity]' => 1,
    // Laisse Stripe proposer automatiquement tous les moyens de paiement disponibles
    // (carte, Apple Pay, Google Pay, et les virements/portefeuilles locaux activés
    // dans ton tableau de bord Stripe selon les pays — SEPA, iDEAL, Bancontact, etc.)
    // au lieu de coder chaque méthode une par une.
    'automatic_payment_methods[enabled]' => 'true',
    // Adapte la langue de la page de paiement Stripe à celle choisie sur le site
    'locale' => $locale,
    // Laisse le donateur indiquer son pays de facturation (utile pour un don international)
    'billing_address_collection' => 'auto',
];

$ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERPWD, STRIPE_SECRET_KEY . ':');

$response = curl_exec($ch);
$curlError = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($curlError) {
    http_response_code(500);
    echo json_encode(['error' => 'Errore di connessione: ' . $curlError]);
    exit;
}

$result = json_decode($response, true);

if ($httpCode !== 200 || !isset($result['url'])) {
    http_response_code(500);
    $msg = $result['error']['message'] ?? 'Errore Stripe sconosciuto.';
    echo json_encode(['error' => $msg]);
    exit;
}

echo json_encode(['url' => $result['url']]);
