<?php

declare(strict_types=1);

define('APP_ROOT', __DIR__ . '/..');
define('APP_STORAGE', APP_ROOT . '/storage');
define('APP_STORAGE_DATA', APP_STORAGE . '/data');

$config = require APP_STORAGE . '/config/app.php';
define('APP_CONFIG', $config);

autoload_register();
require_once __DIR__ . '/lib/Security.php';
apply_security_headers();
start_secure_session();

function autoload_register(): void
{
    spl_autoload_register(static function (string $class): void {
        if (!str_starts_with($class, 'App\\Lib\\')) {
            return;
        }
        $relative = str_replace('App\\Lib\\', '', $class);
        $relative = str_replace('\\', '/', $relative);
        $path = __DIR__ . '/lib/' . $relative . '.php';
        if (is_file($path)) {
            require $path;
        }
    });
}

function apply_security_headers(): void
{
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data: https:; media-src 'self' https:; style-src 'self' 'unsafe-inline' https:; script-src 'self' 'unsafe-inline' https:; font-src 'self' https: data:; connect-src 'self'; frame-ancestors 'none'; base-uri 'self'; form-action 'self'");
}

function start_secure_session(): void
{
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? null) === 443);
    session_set_cookie_params([
        'httponly' => true,
        'secure' => $isHttps,
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}
