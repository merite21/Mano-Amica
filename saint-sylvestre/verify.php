<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

$token = trim($_GET['token'] ?? '');
$db = get_db();

$stmt = $db->prepare('SELECT t.*, o.payment_status FROM tickets t JOIN orders o ON o.id = t.order_id WHERE t.token = ?');
$stmt->execute([$token]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

// Marque l'entrée comme "check-in" au premier scan valide (contrôle anti-réutilisation à la porte).
$justCheckedIn = false;
if ($ticket && $ticket['payment_status'] === 'paid' && empty($ticket['checked_in_at']) && ($_GET['checkin'] ?? '1') !== '0') {
    $upd = $db->prepare('UPDATE tickets SET checked_in_at = datetime("now") WHERE id = ?');
    $upd->execute([$ticket['id']]);
    $justCheckedIn = true;
    $ticket['checked_in_at'] = date('Y-m-d H:i:s');
}

$pageTitle = 'Validation du billet — ' . EVENT_NAME;
require __DIR__ . '/includes/header.php';
?>
<div class="verify-card">
<?php if (!$ticket): ?>
  <div class="verify-icon">✕</div>
  <div class="verify-status verify-bad">Billet invalide</div>
  <p>Ce QR code ne correspond à aucun billet connu.</p>

<?php elseif ($ticket['payment_status'] !== 'paid'): ?>
  <div class="verify-icon">⏳</div>
  <div class="verify-status verify-warn">Paiement non confirmé</div>
  <p>Ce billet existe mais son paiement n'a pas encore été validé.</p>

<?php elseif (!$justCheckedIn): ?>
  <div class="verify-icon">⚠</div>
  <div class="verify-status verify-warn">Billet déjà utilisé</div>
  <p>Ce billet a déjà été présenté à l'entrée le <?= htmlspecialchars(date('d/m/Y à H:i', strtotime($ticket['checked_in_at']))) ?>.</p>
  <div class="verify-details">
    <div><span>Titulaire</span><strong><?= htmlspecialchars($ticket['buyer_name']) ?></strong></div>
    <div><span>Type de pass</span><strong><?= htmlspecialchars(pass_label($ticket['pass_type'])) ?></strong></div>
  </div>

<?php else: ?>
  <div class="verify-icon">✓</div>
  <div class="verify-status verify-ok">Paiement validé</div>
  <p>Ce billet est authentique et l'entrée vient d'être enregistrée.</p>
  <div class="verify-details">
    <div><span>Titulaire</span><strong><?= htmlspecialchars($ticket['buyer_name']) ?></strong></div>
    <div><span>Type de pass</span><strong><?= htmlspecialchars(pass_label($ticket['pass_type'])) ?></strong></div>
    <div><span>Événement</span><strong><?= htmlspecialchars(EVENT_NAME) ?></strong></div>
    <div><span>Date</span><strong><?= format_event_date() ?></strong></div>
  </div>
<?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
