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

function tmdbFetch($endpoint, $params = [])
{
    $apiKey = "ff3e44517aba3b3a85c940344177b06d";
    $baseUrl = "https://api.themoviedb.org/3";

    if (!str_starts_with($endpoint, "/")) {
        $endpoint = "/" . $endpoint;
    }

    $params["api_key"] = $apiKey;

    $url = $baseUrl . $endpoint . "?" . http_build_query($params);

    $response = @file_get_contents($url);

    if ($response === false) {
        return [
            "status_code" => 500,
            "status_message" => "Unable to connect to TMDB"
        ];
    }

    $data = json_decode($response, true);

    if (!is_array($data)) {
        return [
            "status_code" => 500,
            "status_message" => "Invalid TMDB response"
        ];
    }

    return $data;
}

function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
