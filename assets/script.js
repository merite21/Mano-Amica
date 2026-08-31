let currentLang = localStorage.getItem('manoamica_lang') || 'it';
let translations = {};

async function loadTranslations(lang) {
  const res = await fetch(`assets/lang/${lang}.json`);
  translations = await res.json();
  applyTranslations();
}

function applyTranslations() {
  document.documentElement.setAttribute('lang', currentLang);

  document.querySelectorAll('[data-i18n]').forEach(el => {
    const key = el.getAttribute('data-i18n');
    if (translations[key] !== undefined) el.textContent = translations[key];
  });

  document.querySelectorAll('[data-i18n-html]').forEach(el => {
    const key = el.getAttribute('data-i18n-html');
    if (translations[key] !== undefined) el.innerHTML = translations[key];
  });

  document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
    const key = el.getAttribute('data-i18n-placeholder');
    if (translations[key] !== undefined) el.setAttribute('placeholder', translations[key]);
  });

  document.querySelectorAll('.lang-pill button').forEach(btn => {
    btn.classList.toggle('active', btn.getAttribute('data-lang') === currentLang);
  });

  const cookieText = document.getElementById('cookie-banner-text');
  if (cookieText && translations['cookie_banner_text']) {
    cookieText.innerHTML = translations['cookie_banner_text'];
  }
  const cookieBtn = document.getElementById('cookie-banner-accept');
  if (cookieBtn && translations['cookie_banner_accept']) {
    cookieBtn.textContent = translations['cookie_banner_accept'];
  }
}

function setLanguage(lang) {
  currentLang = lang;
  localStorage.setItem('manoamica_lang', lang);
  loadTranslations(lang);
}

document.addEventListener('DOMContentLoaded', () => {

  // ===== Bannière cookies (RGPD) =====
  if (!localStorage.getItem('manoamica_cookie_consent')) {
    const banner = document.createElement('div');
    banner.id = 'cookie-banner';
    banner.innerHTML = `
      <p id="cookie-banner-text">Questo sito utilizza cookie tecnici necessari al funzionamento. Continuando la navigazione accetti la nostra <a href="privacy.html">Privacy Policy</a> e <a href="cookie-policy.html">Cookie Policy</a>.</p>
      <button id="cookie-banner-accept">Ho capito</button>
    `;
    document.body.appendChild(banner);
    document.getElementById('cookie-banner-accept').addEventListener('click', () => {
      localStorage.setItem('manoamica_cookie_consent', '1');
      banner.remove();
    });
  }

  loadTranslations(currentLang);

  const toggle = document.getElementById('toggle');
  const veil = document.getElementById('veil');
  if (toggle && veil) {
    toggle.addEventListener('click', () => {
      toggle.classList.toggle('open');
      veil.classList.toggle('open');
    });
    document.querySelectorAll('.nav-list a').forEach(a => {
      a.addEventListener('click', () => {
        toggle.classList.remove('open');
        veil.classList.remove('open');
      });
    });
  }

  document.querySelectorAll('.lang-pill button').forEach(btn => {
    btn.addEventListener('click', () => setLanguage(btn.getAttribute('data-lang')));
  });

  // Sélecteur de montant (page Dona ora)
  document.querySelectorAll('.amount-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.amount-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const custom = document.getElementById('custom-amount');
      if (custom) custom.value = '';
    });
  });

  // Effacer la sélection de montant prédéfini si l'utilisateur tape un montant perso
  const customInput = document.getElementById('custom-amount');
  if (customInput) {
    customInput.addEventListener('input', () => {
      if (customInput.value) {
        document.querySelectorAll('.amount-btn').forEach(b => b.classList.remove('active'));
      }
    });
  }

  // Sélection de la méthode de paiement (page Dona ora)
  document.querySelectorAll('.pay-method-item').forEach(item => {
    item.addEventListener('click', () => {
      if (item.classList.contains('disabled')) return;
      document.querySelectorAll('.pay-method-item').forEach(i => i.classList.remove('active'));
      item.classList.add('active');
    });
  });

  // Le paiement par CARTE utilise le vrai Stripe (create-checkout-session.php).
  // PayPal / Satispay / SEPA restent simulés en attendant leur intégration dédiée.
  const SIMULATE_NON_CARD_METHODS = true;

  let orgInfo = null;
  fetch('assets/org-info.json').then(r => r.json()).then(d => { orgInfo = d; }).catch(() => {});

  const confirmBtn = document.getElementById('confirm-donation');
  const modal = document.getElementById('pay-modal');
  const modalClose = document.getElementById('pay-modal-close');
  const modalPayBtn = document.getElementById('modal-pay-btn');
  let selectedAmount = 0;
  let selectedMethod = 'card';

  function openMethodPanel(method) {
    document.querySelectorAll('.pay-modal-method').forEach(p => p.style.display = 'none');
    const panel = document.getElementById('method-' + method);
    if (panel) panel.style.display = 'block';

    if (orgInfo) {
      if (method === 'satispay') {
        const el = document.getElementById('satispay-recipient');
        if (el) el.value = orgInfo.satispayNumber || '';
      }
      if (method === 'sepa') {
        document.getElementById('sepa-iban').textContent = orgInfo.iban || '-';
        document.getElementById('sepa-bic').textContent = orgInfo.bic || '-';
        document.getElementById('sepa-name').textContent = orgInfo.recipientName || '-';
      }
    }
  }

  if (confirmBtn) {
    confirmBtn.addEventListener('click', () => {
      const errorEl = document.getElementById('donation-error');
      errorEl.style.display = 'none';

      const customVal = parseFloat(document.getElementById('custom-amount').value);
      const activeBtn = document.querySelector('.amount-btn.active');
      const amount = customVal > 0 ? customVal : (activeBtn ? parseFloat(activeBtn.getAttribute('data-amount')) : 0);
      const methodEl = document.querySelector('.pay-method-item.active');
      const method = methodEl ? methodEl.getAttribute('data-method') : 'card';

      if (!amount || amount <= 0) {
        errorEl.textContent = translations['do_error_amount'] || 'Scegli o inserisci un importo valido.';
        errorEl.style.display = 'block';
        return;
      }

      selectedAmount = amount;
      selectedMethod = method;

      document.getElementById('modal-recipient').textContent = (orgInfo && orgInfo.recipientName) || 'Fondazione Mano Amica';
      document.getElementById('modal-amount').textContent = amount + ' €';
      document.getElementById('modal-error').style.display = 'none';
      modalPayBtn.disabled = false;
      modalPayBtn.textContent = translations['modal_pay_btn'] || 'Paga ora';

      openMethodPanel(method);
      modal.classList.add('open');
    });
  }

  function closeModal() { modal.classList.remove('open'); }
  if (modalClose) modalClose.addEventListener('click', closeModal);
  if (modal) modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });

  function showModalError(msg) {
    const el = document.getElementById('modal-error');
    el.textContent = msg;
    el.style.display = 'block';
  }

  function validateModalFields(method) {
    // La carta e PayPal non sono validées ici : Stripe/PayPal se ne occupano sulla loro pagina sicura.
    if (method === 'satispay') {
      const phone = document.getElementById('satispay-phone').value.trim();
      if (phone.length < 6) return translations['modal_err_phone'] || 'Numero di telefono non valido.';
    }
    return null;
  }

  if (modalPayBtn) {
    modalPayBtn.addEventListener('click', async () => {
      document.getElementById('modal-error').style.display = 'none';
      const errMsg = validateModalFields(selectedMethod);
      if (errMsg) { showModalError(errMsg); return; }

      const originalText = modalPayBtn.textContent;
      modalPayBtn.textContent = translations['do_processing'] || 'Attendere...';
      modalPayBtn.disabled = true;

      // ==== CARTA : vrai Stripe, toujours (jamais simulé) ====
      if (selectedMethod === 'card') {
        try {
          const res = await fetch('create-checkout-session.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ amount: selectedAmount, method: selectedMethod, locale: currentLang })
          });
          const data = await res.json();
          if (data.url) {
            window.location.href = data.url;
          } else {
            throw new Error(data.error || 'Errore sconosciuto');
          }
        } catch (err) {
          showModalError(translations['do_error_generic'] || 'Si è verificato un errore. Riprova tra poco.');
          modalPayBtn.textContent = originalText;
          modalPayBtn.disabled = false;
        }
        return;
      }

      // ==== PAYPAL : vraie API PayPal, toujours (jamais simulé) ====
      if (selectedMethod === 'paypal') {
        try {
          const res = await fetch('create-paypal-order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ amount: selectedAmount, locale: currentLang })
          });
          const data = await res.json();
          if (data.url) {
            window.location.href = data.url;
          } else {
            throw new Error(data.error || 'Errore sconosciuto');
          }
        } catch (err) {
          showModalError(translations['do_error_generic'] || 'Si è verificato un errore. Riprova tra poco.');
          modalPayBtn.textContent = originalText;
          modalPayBtn.disabled = false;
        }
        return;
      }

      // ==== SATISPAY / SEPA : en attente d'intégration dédiée (voir notes dans la fenêtre) ====
      if (SIMULATE_NON_CARD_METHODS) {
        setTimeout(() => {
          window.location.href = `success.html?amount=${selectedAmount}&method=${selectedMethod}&simulated=1`;
        }, 1500);
      }
    });
  }

  // Formulaire de contact → envoi réel (send-contact.php)
  const contactForm = document.getElementById('contact-form');
  if (contactForm) {
    contactForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const statusEl = document.getElementById('contact-status');
      const submitBtn = document.getElementById('contact-submit-btn');
      statusEl.style.display = 'none';

      const name = document.getElementById('nome').value.trim();
      const email = document.getElementById('email').value.trim();
      const message = document.getElementById('msg').value.trim();
      const website = document.getElementById('website').value; // honeypot

      if (!name || !email || !message) {
        statusEl.textContent = translations['contact_err_fields'] || 'Compila tutti i campi.';
        statusEl.style.color = 'var(--clay)';
        statusEl.style.display = 'block';
        return;
      }

      const originalText = submitBtn.textContent;
      submitBtn.textContent = translations['contact_sending'] || 'Invio in corso...';
      submitBtn.disabled = true;

      try {
        const res = await fetch('send-contact.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ name, email, message, website })
        });
        const data = await res.json();

        if (res.ok && data.success) {
          statusEl.textContent = translations['contact_success'] || 'Messaggio inviato! Ti risponderemo presto.';
          statusEl.style.color = 'var(--forest)';
          statusEl.style.display = 'block';
          contactForm.reset();
        } else {
          throw new Error(data.error || 'unknown');
        }
      } catch (err) {
        statusEl.textContent = translations['contact_error'] || 'Invio non riuscito. Riprova più tardi o scrivici direttamente via email.';
        statusEl.style.color = 'var(--clay)';
        statusEl.style.display = 'block';
      } finally {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
      }
    });
  }
});
