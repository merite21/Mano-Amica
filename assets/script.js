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

  // Bouton "Richiedi un aiuto" → ouvre l'email du visiteur avec un message pré-rempli,
  // adressé à la boîte de la fondation, pour demander un soutien (Mano Amica est
  // l'organisme qui accorde des aides, pas qui en reçoit).
  const AID_REQUEST_EMAIL = 'donazioni@mano-amica.org';

  const AID_REQUEST_MAIL_TEXT = {
    it: {
      subject: amount => `Richiesta di aiuto — ${amount} €`,
      body: amount => `Buongiorno,\n\nVorrei richiedere un aiuto di ${amount} € da parte della Fondazione Mano Amica.\n\nEcco la mia situazione:\n[descrivi qui la tua richiesta]\n\nResto a disposizione per ulteriori informazioni.\n\nGrazie mille,`
    },
    fr: {
      subject: amount => `Demande d'aide — ${amount} €`,
      body: amount => `Bonjour,\n\nJe souhaite demander une aide de ${amount} € de la part de la Fondation Mano Amica.\n\nVoici ma situation :\n[décrivez ici votre demande]\n\nJe reste à votre disposition pour toute information complémentaire.\n\nMerci beaucoup,`
    },
    en: {
      subject: amount => `Support request — €${amount}`,
      body: amount => `Hello,\n\nI would like to request support of €${amount} from the Mano Amica Foundation.\n\nHere is my situation:\n[describe your request here]\n\nI remain available for any further information.\n\nThank you,`
    }
  };

  const confirmBtn = document.getElementById('confirm-donation');
  if (confirmBtn) {
    confirmBtn.addEventListener('click', () => {
      const errorEl = document.getElementById('donation-error');
      errorEl.style.display = 'none';

      const customVal = parseFloat(document.getElementById('custom-amount').value);
      const activeBtn = document.querySelector('.amount-btn.active');
      const amount = customVal > 0 ? customVal : (activeBtn ? parseFloat(activeBtn.getAttribute('data-amount')) : 0);

      if (!amount || amount <= 0) {
        errorEl.textContent = translations['do_error_amount'] || 'Scegli o inserisci un importo valido.';
        errorEl.style.display = 'block';
        return;
      }

      const texts = AID_REQUEST_MAIL_TEXT[currentLang] || AID_REQUEST_MAIL_TEXT.it;
      const subject = encodeURIComponent(texts.subject(amount));
      const body = encodeURIComponent(texts.body(amount));
      window.location.href = `mailto:${AID_REQUEST_EMAIL}?subject=${subject}&body=${body}`;
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
