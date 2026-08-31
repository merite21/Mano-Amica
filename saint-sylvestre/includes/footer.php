  <footer class="site-footer">
    <div class="footer-inner">
      <strong>✦ <?= htmlspecialchars(EVENT_NAME) ?></strong>
      <p><?= htmlspecialchars(EVENT_VENUE) ?> — <?= htmlspecialchars(EVENT_CITY) ?></p>
      <p><?= format_event_date() ?></p>
      <p class="footer-contact">Contact : <a href="mailto:<?= htmlspecialchars(CONTACT_EMAIL) ?>"><?= htmlspecialchars(CONTACT_EMAIL) ?></a></p>
      <span class="footer-copy">© <?= date('Y') ?> · Billet nominatif, non remboursable, à présenter (imprimé ou sur mobile) à l'entrée.</span>
    </div>
  </footer>
<script src="assets/js/main.js"></script>
</body>
</html>
