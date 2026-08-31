<?php
require_once __DIR__ . '/config.php';

function format_money(int $amount): string {
    return number_format($amount, 0, ',', ' ') . ' ' . CURRENCY_LABEL;
}

function new_ticket_token(): string {
    // Token unique, non devinable, utilisé dans le QR code de chaque billet.
    return bin2hex(random_bytes(16));
}

function pass_label(string $type): string {
    return $type === 'couple' ? 'Pass Couple' : 'Pass Solo';
}

function pass_price(string $type): int {
    return $type === 'couple' ? PRICE_COUPLE : PRICE_SOLO;
}

function format_event_date(): string {
    $start = new DateTime(EVENT_START);
    $end = new DateTime(EVENT_END);
    $mois = [1=>'janvier',2=>'février',3=>'mars',4=>'avril',5=>'mai',6=>'juin',
        7=>'juillet',8=>'août',9=>'septembre',10=>'octobre',11=>'novembre',12=>'décembre'];
    return $start->format('d') . ' ' . $mois[(int)$start->format('n')] . ' ' . $start->format('Y')
        . ' → 1er ' . $mois[(int)$end->format('n')] . ' ' . $end->format('Y');
}

function json_input(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function json_response(array $payload, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}
