<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'method_not_allowed'], 405);
}

$data = json_input();
$orderId = (int)($data['orderId'] ?? 0);
$isDemo = !empty($data['demo']);
$transactionId = trim($data['transactionId'] ?? '');

$db = get_db();
$stmt = $db->prepare('SELECT * FROM orders WHERE id = ?');
$stmt->execute([$orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    json_response(['error' => 'order_not_found'], 404);
}

if ($order['payment_status'] === 'paid') {
    $t = $db->prepare('SELECT token FROM tickets WHERE order_id = ? LIMIT 1');
    $t->execute([$orderId]);
    json_response(['success' => true, 'redirect' => 'billet.php?token=' . urlencode($t->fetchColumn())]);
}

// --- Vérification du paiement ---------------------------------------------
if (DEMO_MODE || $isDemo) {
    // Mode démonstration : aucune clé KkiaPay configurée, on accepte la simulation
    // pour permettre de tester tout le parcours (billet + QR code + validation).
    $paymentConfirmed = true;
} else {
    if ($transactionId === '') {
        json_response(['error' => 'missing_transaction_id'], 400);
    }
    $paymentConfirmed = verify_kkiapay_transaction($transactionId, (int)$order['total_amount']);
}

if (!$paymentConfirmed) {
    json_response(['error' => 'payment_not_confirmed'], 402);
}

// --- Paiement confirmé : on génère les billets (un par pass acheté) -------
$db->beginTransaction();
try {
    $db->prepare('UPDATE orders SET payment_status = "paid", paid_at = datetime("now"), kkiapay_transaction_id = ? WHERE id = ?')
       ->execute([$transactionId ?: null, $orderId]);

    $insertTicket = $db->prepare('
        INSERT INTO tickets (order_id, token, pass_type, buyer_name, price)
        VALUES (?, ?, ?, ?, ?)
    ');

    $firstToken = null;
    for ($i = 0; $i < (int)$order['qty_solo']; $i++) {
        $token = new_ticket_token();
        $insertTicket->execute([$orderId, $token, 'solo', $order['buyer_name'], PRICE_SOLO]);
        $firstToken = $firstToken ?? $token;
    }
    for ($i = 0; $i < (int)$order['qty_couple']; $i++) {
        $token = new_ticket_token();
        $insertTicket->execute([$orderId, $token, 'couple', $order['buyer_name'], PRICE_COUPLE]);
        $firstToken = $firstToken ?? $token;
    }

    $db->commit();
} catch (Exception $e) {
    $db->rollBack();
    json_response(['error' => 'ticket_generation_failed'], 500);
}

json_response(['success' => true, 'redirect' => 'billet.php?token=' . urlencode($firstToken)]);

/**
 * Vérifie une transaction KkiaPay côté serveur (jamais faire confiance au seul retour client).
 */
function verify_kkiapay_transaction(string $transactionId, int $expectedAmount): bool {
    $endpoint = KKIAPAY_SANDBOX
        ? 'https://api-sandbox.kkiapay.me/api/v1/transactions/status'
        : 'https://api.kkiapay.me/api/v1/transactions/status';

    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode(['transactionId' => $transactionId]),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'x-api-key: ' . KKIAPAY_PUBLIC_KEY,
            'x-private-key: ' . KKIAPAY_PRIVATE_KEY,
        ],
        CURLOPT_TIMEOUT => 15,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $httpCode !== 200) {
        return false;
    }

    $result = json_decode($response, true);
    $status = strtoupper($result['status'] ?? '');
    $amount = (int)($result['amount'] ?? 0);

    return $status === 'SUCCESS' && $amount >= $expectedAmount;
}
