<?php
session_start();
header("Content-Type: application/json");

require_once "../db.php";

$method = $_SERVER["REQUEST_METHOD"];
$action = $_GET["action"] ?? "";

function sendJson($data) {
    echo json_encode($data);
    exit;
}

function getJsonInput() {
    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);

    return is_array($data) ? $data : [];
}

function requireLogin() {
    if (!isset($_SESSION["user_id"])) {
        sendJson([
            "success" => false,
            "message" => "Please login first."
        ]);
    }

    return (int) $_SESSION["user_id"];
}

if ($method === "POST" && $action === "add") {
    $userId = requireLogin();
    $data = getJsonInput();

    $tmdbId = (int) ($data["tmdb_id"] ?? 0);
    $title = trim($data["title"] ?? "");
    $posterPath = $data["poster_path"] ?? null;

    if (!$tmdbId || $title === "") {
        sendJson([
            "success" => false,
            "message" => "Missing movie information."
        ]);
    }

    $stmt = $conn->prepare("
        INSERT INTO watchlist (user_id, tmdb_id, title, poster_path)
        VALUES (?, ?, ?, ?)
    ");

    if (!$stmt) {
        sendJson([
            "success" => false,
            "message" => "Prepare failed: " . $conn->error
        ]);
    }

    $stmt->bind_param("iiss", $userId, $tmdbId, $title, $posterPath);

    if (!$stmt->execute()) {
        sendJson([
            "success" => false,
            "message" => "This movie is already in your watchlist."
        ]);
    }

    sendJson([
        "success" => true,
        "message" => "Movie added to watchlist."
    ]);
}

if ($method === "POST" && $action === "remove") {
    $userId = requireLogin();
    $data = getJsonInput();

    $tmdbId = (int) ($data["tmdb_id"] ?? 0);

    if (!$tmdbId) {
        sendJson([
            "success" => false,
            "message" => "Missing movie ID."
        ]);
    }

    $stmt = $conn->prepare("
        DELETE FROM watchlist
        WHERE user_id = ? AND tmdb_id = ?
    ");

    if (!$stmt) {
        sendJson([
            "success" => false,
            "message" => "Prepare failed: " . $conn->error
        ]);
    }

    $stmt->bind_param("ii", $userId, $tmdbId);
    $stmt->execute();

    sendJson([
        "success" => true,
        "message" => "Movie removed from watchlist."
    ]);
}

sendJson([
    "success" => false,
    "message" => "Invalid request."
]);
?>