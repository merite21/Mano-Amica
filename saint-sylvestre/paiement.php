<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

$orderId = (int)($_GET['order'] ?? 0);
$db = get_db();
$stmt = $db->prepare('SELECT * FROM orders WHERE id = ?');
$stmt->execute([$orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    http_response_code(404);
    $pageTitle = 'Commande introuvable — ' . EVENT_NAME;
    require __DIR__ . '/includes/header.php';
    echo '<div class="verify-card"><div class="verify-status verify-bad">Commande introuvable</div><p>Le lien de paiement est invalide ou a expiré.</p><a href="billetterie.php" class="btn btn-gold">Retour à la billetterie</a></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

if ($order['payment_status'] === 'paid') {
    $t = $db->prepare('SELECT token FROM tickets WHERE order_id = ? LIMIT 1');
    $t->execute([$orderId]);
    $firstToken = $t->fetchColumn();
    header('Location: billet.php?token=' . urlencode($firstToken));
    exit;
}

$pageTitle = 'Paiement — ' . EVENT_NAME;
require __DIR__ . '/includes/header.php';
?>
<section>
  <div class="form-card" style="max-width:560px;">
    <div class="section-head" style="margin-bottom:26px;">
      <span class="section-label">Étape 2/2</span>
      <h2>Finaliser le paiement</h2>
    </div>

    <div class="order-total" style="border-top:none;padding-top:0;">
      <span><?= htmlspecialchars($order['buyer_name']) ?> — <?= (int)$order['qty_solo'] ?> Solo / <?= (int)$order['qty_couple'] ?> Couple</span>
      <span class="amount"><?= format_money((int)$order['total_amount']) ?></span>
    </div>

    <div id="payNotice"></div>

    <?php if (DEMO_MODE): ?>
      <div class="notice notice-demo">
        Mode démonstration active : aucune clé KkiaPay configurée dans <code>includes/config.php</code>.
        Cliquez ci-dessous pour simuler un paiement réussi et générer votre billet avec QR code.
      </div>
      <button class="btn btn-gold btn-block" id="demoPayBtn">Simuler le paiement (démo)</button>
    <?php else: ?>
      <script src="https://cdn.kkiapay.me/k.js"></script>
      <button class="btn btn-gold btn-block" id="kkiapayBtn">Payer <?= format_money((int)$order['total_amount']) ?></button>
    <?php endif; ?>
  </div>
</section>

<script>
  window.SS_ORDER = {
    id: <?= (int)$order['id'] ?>,
    amount: <?= (int)$order['total_amount'] ?>,
    demoMode: <?= DEMO_MODE ? 'true' : 'false' ?>,
    kkiapayPublicKey: <?= json_encode(KKIAPAY_PUBLIC_KEY) ?>,
    kkiapaySandbox: <?= KKIAPAY_SANDBOX ? 'true' : 'false' ?>,
    buyerPhone: <?= json_encode($order['buyer_phone']) ?>,
    buyerName: <?= json_encode($order['buyer_name']) ?>
  };
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
