<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$amount = isset($data['amount']) ? (float) $data['amount'] : 0;

$localeMap = ['it' => 'it-IT', 'fr' => 'fr-FR', 'en' => 'en-US'];
$lang = isset($data['locale']) ? preg_replace('/[^a-z]/', '', strtolower($data['locale'])) : 'it';
$paypalLocale = $localeMap[$lang] ?? 'it-IT';

if ($amount <= 0 || $amount > 100000) {
    http_response_code(400);
    echo json_encode(['error' => 'Importo non valido.']);
    exit;
}

$base = (PAYPAL_MODE === 'live') ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';

// 1. Ottieni un token OAuth
$ch = curl_init($base . '/v1/oauth2/token');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERPWD, PAYPAL_CLIENT_ID . ':' . PAYPAL_CLIENT_SECRET);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json', 'Accept-Language: en_US']);
$tokenRes = curl_exec($ch);
$tokenHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$tokenErr = curl_error($ch);
curl_close($ch);

if ($tokenErr) {
    http_response_code(500);
    echo json_encode(['error' => 'Errore di connessione a PayPal: ' . $tokenErr]);
    exit;
}

$tokenData = json_decode($tokenRes, true);
if ($tokenHttp !== 200 || !isset($tokenData['access_token'])) {
    http_response_code(500);
    echo json_encode(['error' => 'Autenticazione PayPal fallita. Controlla PAYPAL_CLIENT_ID e PAYPAL_CLIENT_SECRET in config.php.']);
    exit;
}
$accessToken = $tokenData['access_token'];

// 2. Crea l'ordine
$orderBody = [
    'intent' => 'CAPTURE',
    'purchase_units' => [[
        'amount' => [
            'currency_code' => 'EUR',
            'value' => number_format($amount, 2, '.', ''),
        ],
        'description' => 'Donazione a Mano Amica',
    ]],
    'application_context' => [
        'return_url' => SITE_URL . '/paypal-capture.php',
        'cancel_url' => SITE_URL . '/cancel.html',
        'brand_name' => 'Mano Amica',
        'user_action' => 'PAY_NOW',
        'locale' => $paypalLocale,
    ],
];

$ch = curl_init($base . '/v2/checkout/orders');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($orderBody));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $accessToken,
]);
$orderRes = curl_exec($ch);
$orderHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$orderData = json_decode($orderRes, true);

if ($orderHttp !== 201 || !isset($orderData['links'])) {
    http_response_code(500);
    $msg = $orderData['message'] ?? 'Errore nella creazione dell\'ordine PayPal.';
    echo json_encode(['error' => $msg]);
    exit;
}

$approveUrl = null;
foreach ($orderData['links'] as $link) {
    if ($link['rel'] === 'approve') {
        $approveUrl = $link['href'];
        break;
    }
}

if (!$approveUrl) {
    http_response_code(500);
    echo json_encode(['error' => 'Link di approvazione PayPal non trovato.']);
    exit;
}

echo json_encode(['url' => $approveUrl]);
