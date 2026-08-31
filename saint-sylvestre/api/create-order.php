<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'method_not_allowed'], 405);
}

$data = json_input();

$buyerName = trim($data['buyerName'] ?? '');
$buyerPhone = trim($data['buyerPhone'] ?? '');
$buyerEmail = trim($data['buyerEmail'] ?? '');
$qtySolo = max(0, (int)($data['qtySolo'] ?? 0));
$qtyCouple = max(0, (int)($data['qtyCouple'] ?? 0));
$payMethod = trim($data['payMethod'] ?? '');

if ($buyerName === '' || $buyerPhone === '') {
    json_response(['error' => 'missing_fields'], 400);
}
if ($qtySolo === 0 && $qtyCouple === 0) {
    json_response(['error' => 'no_tickets_selected'], 400);
}
if ($buyerEmail !== '' && !filter_var($buyerEmail, FILTER_VALIDATE_EMAIL)) {
    json_response(['error' => 'invalid_email'], 400);
}
$allowedMethods = ['momo', 'flooz', 'celtiis', 'card'];
if (!in_array($payMethod, $allowedMethods, true)) {
    $payMethod = 'momo';
}

$total = $qtySolo * PRICE_SOLO + $qtyCouple * PRICE_COUPLE;

$db = get_db();
$stmt = $db->prepare('
    INSERT INTO orders (buyer_name, buyer_phone, buyer_email, qty_solo, qty_couple, total_amount, payment_method, payment_status)
    VALUES (?, ?, ?, ?, ?, ?, ?, "pending")
');
$stmt->execute([$buyerName, $buyerPhone, $buyerEmail, $qtySolo, $qtyCouple, $total, $payMethod]);
$orderId = (int)$db->lastInsertId();

json_response([
    'success' => true,
    'orderId' => $orderId,
    'total' => $total,
    'redirect' => 'paiement.php?order=' . $orderId,
]);
