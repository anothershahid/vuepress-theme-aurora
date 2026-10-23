<?php

declare(strict_types=1);

namespace App\Lib;

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function verify_csrf_or_fail(string $token): void
{
    $sessionToken = (string)($_SESSION['csrf'] ?? '');
    if ($sessionToken === '' || !hash_equals($sessionToken, $token)) {
        http_response_code(400);
        exit('অবৈধ CSRF টোকেন');
    }
}

function safe_redirect(string $path): void
{
    if (!str_starts_with($path, '/')) {
        $path = '/';
    }
    header('Location: ' . $path, true, 302);
    exit;
}

function render_markdown(string $markdown): string
{
    $escaped = e($markdown);
    $escaped = preg_replace('/^###\s+(.*)$/m', '<h3>$1</h3>', $escaped) ?? $escaped;
    $escaped = preg_replace('/^##\s+(.*)$/m', '<h2>$1</h2>', $escaped) ?? $escaped;
    $escaped = preg_replace('/^#\s+(.*)$/m', '<h1>$1</h1>', $escaped) ?? $escaped;
    $escaped = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $escaped) ?? $escaped;
    $escaped = preg_replace('/\[(.*?)\]\((https?:\/\/[^\s)]+)\)/', '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>', $escaped) ?? $escaped;
    $lines = array_map('trim', explode("\n", $escaped));
    $html = '';
    foreach ($lines as $line) {
        if ($line === '') {
            continue;
        }
        if (preg_match('/^<h[1-3]>.*<\/h[1-3]>$/', $line) === 1) {
            $html .= $line;
        } else {
            $html .= '<p>' . $line . '</p>';
        }
    }
    return $html;
}

function is_admin(): bool
{
    return !empty($_SESSION['is_admin']);
}

function require_admin(): void
{
    if (!is_admin()) {
        http_response_code(403);
        exit('অনুমতি নেই');
    }
}
