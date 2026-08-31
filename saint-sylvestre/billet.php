<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

$token = trim($_GET['token'] ?? '');
$db = get_db();
$stmt = $db->prepare('SELECT t.*, o.payment_status, o.payment_method FROM tickets t JOIN orders o ON o.id = t.order_id WHERE t.token = ?');
$stmt->execute([$token]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

$pageTitle = 'Mon billet — ' . EVENT_NAME;
require __DIR__ . '/includes/header.php';

if (!$ticket || $ticket['payment_status'] !== 'paid') {
    echo '<div class="verify-card"><div class="verify-icon">✕</div><div class="verify-status verify-bad">Billet introuvable</div><p>Ce billet n\'existe pas ou son paiement n\'a pas été confirmé.</p><a href="billetterie.php" class="btn btn-gold">Réserver un billet</a></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$verifyUrl = rtrim(SITE_URL, '/') . '/verify.php?token=' . urlencode($token);
?>

<section>
  <div class="ticket-wrap">
    <div class="notice notice-success no-print">Paiement confirmé — voici votre billet électronique.</div>

    <div class="ticket" id="ticketCard">
      <div class="ticket-head">
        <span class="eyebrow">✦ <?= htmlspecialchars(EVENT_NAME) ?></span>
        <h2><?= htmlspecialchars(pass_label($ticket['pass_type'])) ?></h2>
      </div>
      <div class="ticket-body">
        <div class="ticket-meta">
          <div><span class="lbl">Titulaire</span><div class="val"><?= htmlspecialchars($ticket['buyer_name']) ?></div></div>
          <div><span class="lbl">Date</span><div class="val"><?= format_event_date() ?></div></div>
          <div><span class="lbl">Lieu</span><div class="val"><?= htmlspecialchars(EVENT_VENUE) ?>, <?= htmlspecialchars(EVENT_CITY) ?></div></div>
          <div><span class="lbl">Prix</span><div class="val"><?= format_money((int)$ticket['price']) ?></div></div>
        </div>
        <div class="qr-box" id="qrBox"></div>
      </div>
      <div class="ticket-foot">
        N° billet : <?= htmlspecialchars(strtoupper(substr($token, 0, 12))) ?> · Ce QR code est unique et sera scanné à l'entrée pour valider votre accès.
      </div>
    </div>

    <div class="ticket-actions no-print">
      <button class="btn btn-gold" onclick="window.print()">Imprimer mon billet</button>
      <a href="index.php" class="btn btn-line">Retour à l'accueil</a>
    </div>
  </div>
</section>

<script src="assets/js/qrcode.js"></script>
<script>
  (function () {
    var qr = qrcode(0, 'M');
    qr.addData(<?= json_encode($verifyUrl) ?>);
    qr.make();
    document.getElementById('qrBox').innerHTML = qr.createSvgTag(5, 0);
  })();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
