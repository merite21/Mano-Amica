<?php
// ⚠️ Remplace la valeur ci-dessous par ta vraie clé secrète Stripe en mode TEST.
// Tu la trouves sur : https://dashboard.stripe.com/test/apikeys
// Elle commence toujours par "sk_test_..."
// Ne mets JAMAIS ta clé "live" (sk_live_...) tant que le site n'est pas en ligne sur un vrai serveur HTTPS.

define('STRIPE_SECRET_KEY', 'sk_test_METTI_QUI_LA_TUA_CHIAVE');

// ⚠️ Identifiants PayPal (app REST) — à récupérer sur https://developer.paypal.com/dashboard/applications
// Commence en mode "sandbox" (bac à sable, faux argent) avant de passer en "live".
define('PAYPAL_CLIENT_ID', 'METTI_QUI_IL_TUO_PAYPAL_CLIENT_ID');
define('PAYPAL_CLIENT_SECRET', 'METTI_QUI_IL_TUO_PAYPAL_CLIENT_SECRET');
define('PAYPAL_MODE', 'sandbox'); // 'sandbox' pendant les tests, 'live' pour le vrai argent

// URL de base de ton site. En local avec le serveur PHP, c'est ça.
// Quand tu passeras sur ton vrai domaine (ex: https://manoamica.org), change cette ligne.
define('SITE_URL', 'http://localhost:8000');

// ⚠️ Email qui recevra les messages envoyés depuis le formulaire de contact.
// ATTENTION : en local (MAMP / php -S), l'envoi d'email réel ne fonctionne généralement pas
// (il n'y a pas de serveur mail configuré sur ta machine) — c'est normal de voir une erreur
// en local. Une fois hébergé chez un vrai hébergeur PHP, ça enverra pour de vrai.
define('CONTACT_EMAIL', 'info@manoamica.org');
