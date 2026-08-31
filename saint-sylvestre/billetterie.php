<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Réserver un billet — ' . EVENT_NAME;
require __DIR__ . '/includes/header.php';
?>

<section>
  <div class="section-head">
    <span class="section-label">Billetterie en ligne</span>
    <h2>Réservez vos places pour la Saint-Sylvestre.</h2>
    <p>Renseignez vos informations, choisissez vos Pass, puis payez par Mobile Money ou carte bancaire. Votre billet (avec QR code unique) sera généré immédiatement après paiement.</p>
  </div>

  <?php if (DEMO_MODE): ?>
  <div class="notice notice-demo" style="max-width:640px;margin:0 auto 30px;">
    Mode démonstration : aucune clé de paiement KkiaPay n'est configurée. Vous pourrez simuler
    un paiement réussi pour tester tout le parcours (billet + QR code + validation à l'entrée).
  </div>
  <?php endif; ?>

  <form class="form-card" id="orderForm">
    <div class="qty-picker">
      <div class="info"><strong>Pass Solo</strong><span><?= format_money(PRICE_SOLO) ?> / personne</span></div>
      <div class="qty-controls">
        <button type="button" data-step="-1" data-target="solo">–</button>
        <span class="qty-val" id="qtySoloVal">0</span>
        <button type="button" data-step="1" data-target="solo">+</button>
      </div>
    </div>
    <div class="qty-picker">
      <div class="info"><strong>Pass Couple</strong><span><?= format_money(PRICE_COUPLE) ?> / 2 personnes</span></div>
      <div class="qty-controls">
        <button type="button" data-step="-1" data-target="couple">–</button>
        <span class="qty-val" id="qtyCoupleVal">0</span>
        <button type="button" data-step="1" data-target="couple">+</button>
      </div>
    </div>

    <div class="field-row">
      <div class="field">
        <label for="buyerName">Nom complet</label>
        <input type="text" id="buyerName" required placeholder="Ex : Ama Koffi">
      </div>
      <div class="field">
        <label for="buyerPhone">Téléphone (Mobile Money)</label>
        <input type="tel" id="buyerPhone" required placeholder="Ex : 90 00 00 00">
      </div>
    </div>
    <div class="field">
      <label for="buyerEmail">Email (pour recevoir votre billet)</label>
      <input type="email" id="buyerEmail" required placeholder="vous@example.com">
    </div>

    <div class="field">
      <label>Moyen de paiement</label>
      <div class="pay-methods">
        <label class="pay-chip"><input type="radio" name="payMethod" value="momo" checked style="margin-right:8px;">MTN Momo</label>
        <label class="pay-chip"><input type="radio" name="payMethod" value="flooz" style="margin-right:8px;">Moov Flooz</label>
        <label class="pay-chip"><input type="radio" name="payMethod" value="celtiis" style="margin-right:8px;">Celtiis Cash</label>
        <label class="pay-chip"><input type="radio" name="payMethod" value="card" style="margin-right:8px;">Carte bancaire</label>
      </div>
    </div>

    <div class="order-total">
      <span>Total à payer</span>
      <span class="amount" id="orderTotal">0 <?= htmlspecialchars(CURRENCY_LABEL) ?></span>
    </div>

    <div id="formError"></div>

    <button type="submit" class="btn btn-gold btn-block" id="submitOrder">Continuer vers le paiement</button>
  </form>
</section>

<?php
$configScript = sprintf(
    "window.SS_CONFIG = %s;",
    json_encode(['priceSolo' => PRICE_SOLO, 'priceCouple' => PRICE_COUPLE, 'currency' => CURRENCY_LABEL], JSON_UNESCAPED_UNICODE)
);
echo "<script>$configScript</script>";
?>

<?php
$prefill = ($_GET['type'] ?? '') === 'couple' ? 'couple' : (($_GET['type'] ?? '') === 'solo' ? 'solo' : '');
if ($prefill !== '') {
    echo '<script>window.SS_PREFILL=' . json_encode($prefill) . ';</script>';
}
require __DIR__ . '/includes/footer.php';
?>
