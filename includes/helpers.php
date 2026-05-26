<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    ]);
    session_start();
}

function csrf_token(): string
{
    start_session();
    if (empty($_SESSION[CSRF_TOKEN_KEY])) {
        $_SESSION[CSRF_TOKEN_KEY] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_KEY];
}

function csrf_field(): string
{
    return '<input type="hidden" name="' . CSRF_TOKEN_KEY . '" value="' . e(csrf_token()) . '">';
}

function verify_csrf(?string $token): bool
{
    start_session();
    return is_string($token)
        && !empty($_SESSION[CSRF_TOKEN_KEY])
        && hash_equals($_SESSION[CSRF_TOKEN_KEY], $token);
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

function is_admin_logged_in(): bool
{
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
    start_session();
    if ($message !== null) {
        $_SESSION['_flash'][$key] = $message;
        return null;
    }
    $val = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
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
