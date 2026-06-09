<?php
/**
 * Configuration — France Étude / FIGS Education
 * Copiez config.example.php en config.local.php pour la production.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/env.php';

define('APP_NAME', 'France Étude');
define('APP_TAGLINE', 'Votre passerelle vers les universités privées françaises');
define('APP_LOGO', 'assets/img/logo.png');
define('FIGS_LOGO', 'assets/img/figs-logo.png');
define('FIGS_URL', 'https://www.figs-education.com/');
define('FIGS_PARTNER', 'FIGS Education — Réseau Compétences & Développement');

define('CONTACT_EMAIL', 'adonfanny94400@gmail.com');
define('CONTACT_PHONE', '+33 6 50 30 98 98');
define('CONTACT_PHONE_TEL', '+33650309898');
define('CONTACT_WHATSAPP', '33650309898');
define('CONTACT_WHATSAPP_MESSAGE', 'Bonjour, je souhaite obtenir des informations sur une candidature pour étudier en France.');

// Base de données : sqlite (local), mysql ou pgsql (Vercel / production)
$dbDriver = env_var('DB_DRIVER') ?: '';
if ($dbDriver === '' && postgres_available()) {
    $dbDriver = 'pgsql';
}
if ($dbDriver === '') {
    $dbDriver = 'sqlite';
}
define('DB_DRIVER', $dbDriver);

$secret = env_var('SECRET_KEY') ?: env_var('VERCEL_GIT_COMMIT_SHA') ?: '';
if ($secret === '' && env_var('VERCEL')) {
    $secret = hash('sha256', (env_var('VERCEL_URL') ?: 'france-etude') . '-vercel-fallback');
}
define('APP_SECRET', $secret !== '' ? $secret : 'dev-local-secret-change-in-production');

define('DB_SQLITE_PATH', __DIR__ . '/../data/france_etude.sqlite');

define('DB_MYSQL_HOST', env_var('DB_HOST') ?: '127.0.0.1');
define('DB_MYSQL_NAME', env_var('DB_NAME') ?: 'france_etude');
define('DB_MYSQL_USER', env_var('DB_USER') ?: 'root');
define('DB_MYSQL_PASS', env_var('DB_PASS') ?: '');

// Session & sécurité
define('SESSION_NAME', 'france_etude_sess');
define('CSRF_TOKEN_KEY', '_csrf_token');

// Fuseau horaire
date_default_timezone_set('Europe/Paris');

// Charger surcharge locale si présente
$localConfig = __DIR__ . '/config.local.php';
if (is_file($localConfig)) {
    require $localConfig;
}
