(function () {
  'use strict';

  // ---------- Mobile nav ----------
  var toggle = document.getElementById('navToggle');
  var nav = document.getElementById('mainNav');
  if (toggle && nav) {
    toggle.addEventListener('click', function () {
      nav.classList.toggle('open');
    });
  }

  // ---------- Hero slideshow ----------
  var slides = document.querySelectorAll('.slide');
  if (slides.length) {
    var current = 0;
    setInterval(function () {
      slides[current].classList.remove('active');
      current = (current + 1) % slides.length;
      slides[current].classList.add('active');
    }, 6000);
  }

  // ---------- Confetti ----------
  var confettiBox = document.getElementById('confetti');
  if (confettiBox) {
    var colors = ['#e8c46a', '#f5e2a8', '#d98c9a', '#f5efe0'];
    for (var i = 0; i < 40; i++) {
      var span = document.createElement('span');
      span.style.left = Math.random() * 100 + '%';
      span.style.background = colors[i % colors.length];
      span.style.animationDuration = (6 + Math.random() * 8) + 's';
      span.style.animationDelay = (Math.random() * 10) + 's';
      span.style.width = span.style.height = (4 + Math.random() * 6) + 'px';
      confettiBox.appendChild(span);
    }
  }

  // ---------- Countdown ----------
  var countdown = document.getElementById('countdown');
  if (countdown) {
    var target = new Date(countdown.getAttribute('data-target')).getTime();
    var elD = document.getElementById('cd-days');
    var elH = document.getElementById('cd-hours');
    var elM = document.getElementById('cd-min');
    var elS = document.getElementById('cd-sec');

    function pad(n) { return String(n).padStart(2, '0'); }

    function tick() {
      var diff = target - Date.now();
      if (diff < 0) diff = 0;
      var s = Math.floor(diff / 1000);
      var days = Math.floor(s / 86400);
      var hours = Math.floor((s % 86400) / 3600);
      var mins = Math.floor((s % 3600) / 60);
      var secs = s % 60;
      elD.textContent = pad(days);
      elH.textContent = pad(hours);
      elM.textContent = pad(mins);
      elS.textContent = pad(secs);
    }
    tick();
    setInterval(tick, 1000);
  }

  // ---------- Billetterie form ----------
  var orderForm = document.getElementById('orderForm');
  if (orderForm && window.SS_CONFIG) {
    var qty = { solo: 0, couple: 0 };

    if (window.SS_PREFILL === 'solo') qty.solo = 1;
    if (window.SS_PREFILL === 'couple') qty.couple = 1;

    var soloValEl = document.getElementById('qtySoloVal');
    var coupleValEl = document.getElementById('qtyCoupleVal');
    var totalEl = document.getElementById('orderTotal');

    function renderQty() {
      soloValEl.textContent = qty.solo;
      coupleValEl.textContent = qty.couple;
      var total = qty.solo * window.SS_CONFIG.priceSolo + qty.couple * window.SS_CONFIG.priceCouple;
      totalEl.textContent = total.toLocaleString('fr-FR') + ' ' + window.SS_CONFIG.currency;
    }
    renderQty();

    document.querySelectorAll('.qty-controls button').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var key = btn.getAttribute('data-target');
        var step = parseInt(btn.getAttribute('data-step'), 10);
        qty[key] = Math.max(0, Math.min(20, qty[key] + step));
        renderQty();
      });
    });

    orderForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var errorBox = document.getElementById('formError');
      errorBox.innerHTML = '';

      if (qty.solo === 0 && qty.couple === 0) {
        errorBox.innerHTML = '<div class="notice notice-error">Sélectionnez au moins un Pass.</div>';
        return;
      }

      var submitBtn = document.getElementById('submitOrder');
      submitBtn.disabled = true;
      submitBtn.textContent = 'Veuillez patienter…';

      var payMethod = document.querySelector('input[name="payMethod"]:checked');

      fetch('api/create-order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          buyerName: document.getElementById('buyerName').value,
          buyerPhone: document.getElementById('buyerPhone').value,
          buyerEmail: document.getElementById('buyerEmail').value,
          qtySolo: qty.solo,
          qtyCouple: qty.couple,
          payMethod: payMethod ? payMethod.value : 'momo',
        }),
      })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (res.success) {
            window.location.href = res.redirect;
          } else {
            errorBox.innerHTML = '<div class="notice notice-error">Une erreur est survenue. Vérifiez vos informations.</div>';
            submitBtn.disabled = false;
            submitBtn.textContent = 'Continuer vers le paiement';
          }
        })
        .catch(function () {
          errorBox.innerHTML = '<div class="notice notice-error">Impossible de contacter le serveur. Réessayez.</div>';
          submitBtn.disabled = false;
          submitBtn.textContent = 'Continuer vers le paiement';
        });
    });
  }

  // ---------- Paiement (page paiement.php) ----------
  if (window.SS_ORDER) {
    var order = window.SS_ORDER;
    var notice = document.getElementById('payNotice');

    function confirmPayment(payload) {
      fetch('api/confirm-payment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(Object.assign({ orderId: order.id }, payload)),
      })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (res.success) {
            window.location.href = res.redirect;
          } else {
            notice.innerHTML = '<div class="notice notice-error">Paiement non confirmé. Réessayez ou contactez l\'organisation.</div>';
          }
        })
        .catch(function () {
          notice.innerHTML = '<div class="notice notice-error">Impossible de vérifier le paiement pour le moment.</div>';
        });
    }

    var demoBtn = document.getElementById('demoPayBtn');
    if (demoBtn) {
      demoBtn.addEventListener('click', function () {
        demoBtn.disabled = true;
        demoBtn.textContent = 'Génération du billet…';
        confirmPayment({ demo: true });
      });
    }

    var kkiapayBtn = document.getElementById('kkiapayBtn');
    if (kkiapayBtn && window.openKkiapayWidget) {
      kkiapayBtn.addEventListener('click', function () {
        window.openKkiapayWidget({
          amount: order.amount,
          key: order.kkiapayPublicKey,
          sandbox: order.kkiapaySandbox,
          phone: order.buyerPhone,
          name: order.buyerName,
        });
      });

      window.addEventListener('success.kkiapay', function (evt) {
        var transactionId = evt && evt.detail ? evt.detail.transactionId : null;
        if (transactionId) {
          confirmPayment({ transactionId: transactionId });
        }
      });

      window.addEventListener('failed.kkiapay', function () {
        notice.innerHTML = '<div class="notice notice-error">Le paiement a échoué ou a été annulé.</div>';
      });
    }
  }
})();
