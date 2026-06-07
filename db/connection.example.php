<?php
// COPY THIS FILE TO connection.php and fill in your values
// DO NOT commit connection.php — it is in .gitignore

define('DB_HOST',    'localhost');
define('DB_NAME',    'cinebyte');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

define('TMDB_API_KEY',    'YOUR_TMDB_API_KEY_HERE');
define('TMDB_BASE_URL',   'https://api.themoviedb.org/3');
define('TMDB_IMAGE_BASE', 'https://image.tmdb.org/t/p/w500');

define('SESSION_LIFETIME', 60 * 60 * 24 * 7);

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('<p style="color:red;padding:40px;font-family:sans-serif;">DB connection failed: '
                . htmlspecialchars($e->getMessage()) . '</p>');
        }
    }
    return $pdo;
}
