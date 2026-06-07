<?php
require_once __DIR__ . '/../db/connection.php';

ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
session_set_cookie_params(SESSION_LIFETIME);
if (session_status() === PHP_SESSION_NONE) session_start();

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function currentUser(): ?array {
    if (!isLoggedIn()) return null;
    return [
        'id'       => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'email'    => $_SESSION['email'],
        'theme'    => $_SESSION['theme'] ?? 'dark',
    ];
}

function redirect(string $url): void {
    header("Location: $url");
    exit;
}

function requireLogin(): void {
    if (!isLoggedIn()) redirect('auth.php');
}

function tmdbFetch(string $endpoint, array $params = []): array {
    $params['api_key']  = TMDB_API_KEY;
    $params['language'] = 'en-US';
    $url = TMDB_BASE_URL . $endpoint . '?' . http_build_query($params);
    $ctx  = stream_context_create(['http' => ['timeout' => 5]]);
    $json = @file_get_contents($url, false, $ctx);
    if ($json === false) return [];
    return json_decode($json, true) ?? [];
}

function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
