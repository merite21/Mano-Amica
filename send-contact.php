<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$message = trim($data['message'] ?? '');
$honeypot = trim($data['website'] ?? ''); // champ piège anti-spam, doit rester vide

// Anti-spam basique : si le champ invisible est rempli, c'est un bot
if ($honeypot !== '') {
    echo json_encode(['success' => true]); // on répond OK sans rien envoyer, pour ne pas alerter le bot
    exit;
}

if ($name === '' || $email === '' || $message === '') {
    http_response_code(400);
    echo json_encode(['error' => 'missing_fields']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid_email']);
    exit;
}

if (mb_strlen($message) > 5000) {
    http_response_code(400);
    echo json_encode(['error' => 'message_too_long']);
    exit;
}

$safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
$safeMessage = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));

$subject = 'Nuovo messaggio dal sito — ' . $name;

$body = "Nuovo messaggio ricevuto dal modulo di contatto del sito Mano Amica.\n\n";
$body .= "Nome: $name\n";
$body .= "Email: $email\n\n";
$body .= "Messaggio:\n$message\n";

$headers = [];
$headers[] = 'MIME-Version: 1.0';
$headers[] = 'Content-Type: text/plain; charset=UTF-8';
$headers[] = 'From: sito@' . parse_url(SITE_URL, PHP_URL_HOST);
$headers[] = 'Reply-To: ' . $email;

$sent = @mail(CONTACT_EMAIL, $subject, $body, implode("\r\n", $headers));

if ($sent) {
    echo json_encode(['success' => true]);
} else {
    // En local (MAMP / php -S), il n'y a généralement pas de serveur mail configuré :
    // c'est normal que ça échoue ici. Une fois sur un vrai hébergeur, ça enverra pour de vrai.
    http_response_code(500);
    echo json_encode(['error' => 'mail_failed']);
}
