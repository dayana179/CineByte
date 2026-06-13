<?php
// ─── EDIT THESE TO MATCH YOUR LOCAL SERVER ───────────────────────────────────
define('DB_HOST',    'localhost');
define('DB_NAME',    'cinebyte');
define('DB_USER',    'root');       // your MySQL username
define('DB_PASS',    '');           // your MySQL password
define('DB_CHARSET', 'utf8mb4');

define('TMDB_API_KEY',    'YOUR_TMDB_API_KEY_HERE');
define('TMDB_BASE_URL',   'https://api.themoviedb.org/3');
define('TMDB_IMAGE_BASE', 'https://image.tmdb.org/t/p/w500');

define('SESSION_LIFETIME', 60 * 60 * 24 * 7); // 7 days
// ─────────────────────────────────────────────────────────────────────────────

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
            die('<p style="font-family:sans-serif;color:red;padding:40px;">
                 Database connection failed. Please check <code>db/connection.php</code>.<br><br>
                 Error: ' . htmlspecialchars($e->getMessage()) . '</p>');
        }
    }
    return $pdo;
}
