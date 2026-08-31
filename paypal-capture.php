<?php
require_once __DIR__ . '/config.php';

$orderId = $_GET['token'] ?? null;
if (!$orderId) {
    header('Location: ' . SITE_URL . '/cancel.html');
    exit;
}

$base = (PAYPAL_MODE === 'live') ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';

// 1. Token OAuth
$ch = curl_init($base . '/v1/oauth2/token');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERPWD, PAYPAL_CLIENT_ID . ':' . PAYPAL_CLIENT_SECRET);
$tokenRes = curl_exec($ch);
curl_close($ch);
$tokenData = json_decode($tokenRes, true);
$accessToken = $tokenData['access_token'] ?? null;

if (!$accessToken) {
    header('Location: ' . SITE_URL . '/cancel.html');
    exit;
}

// 2. Capture réelle du paiement (c'est ici que l'argent est effectivement transféré)
$ch = curl_init($base . '/v2/checkout/orders/' . urlencode($orderId) . '/capture');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $accessToken,
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, '{}');
$captureRes = curl_exec($ch);
$captureHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$captureData = json_decode($captureRes, true);

if ($captureHttp === 201 && isset($captureData['status']) && $captureData['status'] === 'COMPLETED') {
    header('Location: ' . SITE_URL . '/success.html?method=paypal');
} else {
    header('Location: ' . SITE_URL . '/cancel.html');
}
exit;
