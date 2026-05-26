<?php
/**
 * Configuration — France Étude / FIGS Education
 * Copiez config.example.php en config.local.php pour la production.
 */

declare(strict_types=1);

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

// Base de données : sqlite (défaut) ou mysql
define('DB_DRIVER', getenv('DB_DRIVER') ?: 'sqlite');

define('DB_SQLITE_PATH', __DIR__ . '/../data/france_etude.sqlite');

define('DB_MYSQL_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_MYSQL_NAME', getenv('DB_NAME') ?: 'france_etude');
define('DB_MYSQL_USER', getenv('DB_USER') ?: 'root');
define('DB_MYSQL_PASS', getenv('DB_PASS') ?: '');

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
