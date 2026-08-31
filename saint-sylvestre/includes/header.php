<?php
/** @var string $pageTitle */
/** @var string $pageDescription */
$pageTitle = $pageTitle ?? EVENT_NAME;
$pageDescription = $pageDescription ?? 'Réveillon de la Saint-Sylvestre — billetterie en ligne, pass solo et pass couple.';
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
<meta name="description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>">
<link rel="icon" href="assets/favicon.svg" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,500&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <div class="topbar">
    <a href="index.php" class="logo">✦ <?= htmlspecialchars(EVENT_NAME) ?></a>
    <button class="nav-toggle" id="navToggle" aria-label="Ouvrir le menu"><span></span><span></span><span></span></button>
    <nav class="main-nav" id="mainNav">
      <a href="index.php">Accueil</a>
      <a href="index.php#programme">Programme</a>
      <a href="index.php#pass">Les Pass</a>
      <a href="billetterie.php" class="btn btn-gold btn-sm">Réserver mon billet</a>
    </nav>
  </div>
