<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = EVENT_NAME . ' — ' . EVENT_TAGLINE;
$pageDescription = 'Réveillon de la Saint-Sylvestre : billetterie en ligne, Pass Solo et Pass Couple, paiement Momo, Flooz, Celtiis Cash ou carte bancaire, billet avec QR code.';
require __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <div class="slideshow" id="slideshow">
    <div class="slide slide-1 active"></div>
    <div class="slide slide-2"></div>
    <div class="slide slide-3"></div>
  </div>
  <div class="confetti" id="confetti"></div>

  <div class="hero-content">
    <span class="hero-eyebrow">✦ <?= htmlspecialchars(EVENT_CITY) ?> · <?= format_event_date() ?></span>
    <h1>Passez le cap de minuit <em>en grande pompe</em>.</h1>
    <p class="hero-sub">
      <?= htmlspecialchars(EVENT_NAME) ?> vous invite à une nuit de Saint-Sylvestre inoubliable :
      musique live, DJ, dîner de gala, animations et feux d'artifice pour accueillir la nouvelle année.
    </p>
    <div class="hero-cta">
      <a href="billetterie.php" class="btn btn-gold">Réserver mon billet</a>
      <a href="#programme" class="btn btn-line">Voir le programme</a>
    </div>

    <div class="countdown" id="countdown" data-target="<?= (new DateTime(EVENT_MIDNIGHT))->format(DATE_ATOM) ?>">
      <div class="cell"><span class="num" id="cd-days">00</span><span class="lbl">Jours</span></div>
      <div class="cell"><span class="num" id="cd-hours">00</span><span class="lbl">Heures</span></div>
      <div class="cell"><span class="num" id="cd-min">00</span><span class="lbl">Minutes</span></div>
      <div class="cell"><span class="num" id="cd-sec">00</span><span class="lbl">Secondes</span></div>
    </div>
  </div>
</section>

<section id="programme">
  <div class="section-head">
    <span class="section-label">Déroulé de la soirée</span>
    <h2>Une nuit pensée dans les moindres détails.</h2>
  </div>
  <ul class="programme-list">
    <li><span class="time">21h00</span><div><h3>Accueil & cocktail</h3><p>Réception des invités, welcome drink et photobooth.</p></div></li>
    <li><span class="time">22h00</span><div><h3>Dîner de gala</h3><p>Menu festif assis, service à table, ambiance musicale live.</p></div></li>
    <li><span class="time">00h00</span><div><h3>Passage à la nouvelle année</h3><p>Compte à rebours, feux d'artifice et toast de minuit.</p></div></li>
    <li><span class="time">00h30</span><div><h3>Soirée dansante</h3><p>DJ set jusqu'au bout de la nuit, animations surprises.</p></div></li>
  </ul>
</section>

<section id="pass">
  <div class="section-head">
    <span class="section-label">Billetterie</span>
    <h2>Choisissez votre Pass.</h2>
    <p>Paiement sécurisé par Mobile Money (MTN Momo, Moov Flooz, Celtiis Cash) ou carte bancaire. Billet électronique avec QR code unique, imprimable ou à présenter sur votre téléphone.</p>
  </div>

  <div class="pass-grid">
    <div class="pass-card">
      <h3>Pass Solo</h3>
      <p class="price"><?= format_money(PRICE_SOLO) ?> <small>/ personne</small></p>
      <ul>
        <li>Accès à toute la soirée (21h – 5h)</li>
        <li>Dîner de gala inclus</li>
        <li>1 coupe de champagne au passage de minuit</li>
        <li>Billet électronique avec QR code unique</li>
      </ul>
      <a href="billetterie.php?type=solo" class="btn btn-line btn-block">Choisir Pass Solo</a>
    </div>
    <div class="pass-card featured">
      <h3>Pass Couple</h3>
      <p class="price"><?= format_money(PRICE_COUPLE) ?> <small>/ 2 personnes</small></p>
      <ul>
        <li>Accès à toute la soirée pour 2 personnes</li>
        <li>Dîner de gala inclus pour 2</li>
        <li>2 coupes de champagne au passage de minuit</li>
        <li>Un seul billet / QR code pour les 2 invités</li>
      </ul>
      <a href="billetterie.php?type=couple" class="btn btn-gold btn-block">Choisir Pass Couple</a>
    </div>
  </div>

  <div class="pay-methods">
    <span class="pay-chip"><span class="dot"></span>MTN Momo</span>
    <span class="pay-chip"><span class="dot"></span>Moov Flooz</span>
    <span class="pay-chip"><span class="dot"></span>Celtiis Cash</span>
    <span class="pay-chip"><span class="dot"></span>Carte bancaire</span>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
