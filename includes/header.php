<?php
declare(strict_types=1);
require_once __DIR__ . '/helpers.php';
$currentPage = $currentPage ?? 'home';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e(APP_TAGLINE) ?> — Candidature en ligne pour les écoles du réseau FIGS Education.">
    <title><?= e($pageTitle ?? APP_NAME) ?></title>
    <link rel="icon" href="<?= e(($basePath ?? '') . APP_LOGO) ?>" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e($basePath ?? '') ?>assets/css/style.css">
</head>
<body>
    <a class="skip-link" href="#contenu">Aller au contenu</a>
    <header class="site-header" id="top">
        <div class="container header-inner">
            <a href="#top" class="logo" aria-label="<?= e(APP_NAME) ?> — Accueil">
                <img src="<?= e(($basePath ?? '') . APP_LOGO) ?>" alt="" class="logo-img" width="52" height="52" decoding="async">
                <span class="logo-text"><?= e(APP_NAME) ?></span>
            </a>
            <button type="button" class="nav-toggle" aria-expanded="false" aria-controls="nav-menu" aria-label="Menu">
                <span></span><span></span><span></span>
            </button>
            <nav class="nav" id="nav-menu" aria-label="Navigation principale">
                <a href="#accueil">Accueil</a>
                <a href="#partenaire">FIGS Education</a>
                <a href="#ecoles">Nos écoles</a>
                <a href="#campus">Campus</a>
                <a href="#logement">Logement</a>
                <a href="#procedure">Procédure</a>
                <a href="#candidature" class="nav-cta">Candidater</a>
            </nav>
        </div>
    </header>
    <main id="contenu">
