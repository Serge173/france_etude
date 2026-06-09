<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url_path(string $path): string
{
    return '/' . ltrim($path, '/');
}

function app_secret(): string
{
    return APP_SECRET;
}

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
}

function start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    if (getenv('VERCEL')) {
        ini_set('session.save_path', '/tmp');
    }
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => is_https(),
    ]);
    session_start();
}

function csrf_token(): string
{
    $bucket = gmdate('Y-m-d-H');
    return hash_hmac('sha256', 'csrf:' . $bucket, app_secret());
}

function csrf_field(): string
{
    return '<input type="hidden" name="' . CSRF_TOKEN_KEY . '" value="' . e(csrf_token()) . '">';
}

function verify_csrf(?string $token): bool
{
    if (!is_string($token) || $token === '') {
        return false;
    }
    $current = csrf_token();
    if (hash_equals($current, $token)) {
        return true;
    }
    $previous = hash_hmac('sha256', 'csrf:' . gmdate('Y-m-d-H', time() - 3600), app_secret());
    return hash_equals($previous, $token);
}

function json_response(array $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function generate_reference(): string
{
    return 'FE-' . strtoupper(bin2hex(random_bytes(4))) . '-' . date('ymd');
}

function admin_cookie_name(): string
{
    return 'france_etude_admin';
}

function sign_admin_cookie(int $id, string $name, string $email): string
{
    $payload = json_encode([
        'id' => $id,
        'name' => $name,
        'email' => $email,
        'exp' => time() + 60 * 60 * 24 * 7,
    ], JSON_THROW_ON_ERROR);
    $encoded = rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
    $sig = hash_hmac('sha256', $encoded, app_secret());
    return $encoded . '.' . $sig;
}

function parse_admin_cookie(): ?array
{
    $raw = $_COOKIE[admin_cookie_name()] ?? '';
    if ($raw === '' || !str_contains($raw, '.')) {
        return null;
    }
    [$encoded, $sig] = explode('.', $raw, 2);
    $expected = hash_hmac('sha256', $encoded, app_secret());
    if (!hash_equals($expected, $sig)) {
        return null;
    }
    $json = base64_decode(strtr($encoded, '-_', '+/'), true);
    if ($json === false) {
        return null;
    }
    $data = json_decode($json, true);
    if (!is_array($data) || empty($data['id']) || empty($data['exp']) || $data['exp'] < time()) {
        return null;
    }
    return $data;
}

function set_admin_auth(int $id, string $name, string $email): void
{
    $value = sign_admin_cookie($id, $name, $email);
    setcookie(admin_cookie_name(), $value, [
        'expires' => time() + 60 * 60 * 24 * 7,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => is_https(),
    ]);
    start_session();
    $_SESSION['admin_id'] = $id;
    $_SESSION['admin_name'] = $name;
    $_SESSION['admin_email'] = $email;
}

function clear_admin_auth(): void
{
    setcookie(admin_cookie_name(), '', [
        'expires' => time() - 3600,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => is_https(),
    ]);
    start_session();
    unset($_SESSION['admin_id'], $_SESSION['admin_name'], $_SESSION['admin_email']);
}

function sync_admin_session(): void
{
    $admin = parse_admin_cookie();
    if (!$admin) {
        return;
    }
    start_session();
    $_SESSION['admin_id'] = (int) $admin['id'];
    $_SESSION['admin_name'] = (string) $admin['name'];
    $_SESSION['admin_email'] = (string) $admin['email'];
}

function is_admin_logged_in(): bool
{
    sync_admin_session();
    start_session();
    return !empty($_SESSION['admin_id']);
}

function require_admin(): void
{
    if (!is_admin_logged_in()) {
        header('Location: index.php');
        exit;
    }
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function flash(string $key, ?string $message = null): ?string
{
    $cookieKey = '_flash_' . preg_replace('/[^a-z0-9_]/i', '', $key);
    if ($message !== null) {
        setcookie($cookieKey, $message, [
            'expires' => time() + 120,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure' => is_https(),
        ]);
        return null;
    }
    $val = $_COOKIE[$cookieKey] ?? null;
    if ($val !== null) {
        setcookie($cookieKey, '', [
            'expires' => time() - 3600,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure' => is_https(),
        ]);
    }
    return $val;
}

function sanitize_string(string $value, int $max = 500): string
{
    $value = trim(strip_tags($value));
    if (mb_strlen($value) > $max) {
        $value = mb_substr($value, 0, $max);
    }
    return $value;
}

function validate_email(string $email): bool
{
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

function whatsapp_url(?string $message = null): string
{
    $number = defined('CONTACT_WHATSAPP')
        ? CONTACT_WHATSAPP
        : preg_replace('/\D+/', '', CONTACT_PHONE_TEL);
    $text = $message ?? (defined('CONTACT_WHATSAPP_MESSAGE') ? CONTACT_WHATSAPP_MESSAGE : '');
    $url = 'https://wa.me/' . $number;
    if ($text !== '') {
        $url .= '?text=' . rawurlencode($text);
    }
    return $url;
}
