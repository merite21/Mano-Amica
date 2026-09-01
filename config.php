<?php
// URL de base de ton site. En local avec le serveur PHP, c'est ça.
// Quand tu passeras sur ton vrai domaine (ex: https://manoamica.org), change cette ligne.
define('SITE_URL', 'http://localhost:8000');

// ⚠️ Email qui recevra les messages envoyés depuis le formulaire de contact.
// ATTENTION : en local (MAMP / php -S), l'envoi d'email réel ne fonctionne généralement pas
// (il n'y a pas de serveur mail configuré sur ta machine) — c'est normal de voir une erreur
// en local. Une fois hébergé chez un vrai hébergeur PHP, ça enverra pour de vrai.
define('CONTACT_EMAIL', 'donazioni@mano-amica.org');
