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

    if (!is_array($data)) {
        return [];
    }

    return $data;
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

if ($method === "GET" && $action === "get") {
    $userId = requireLogin();

    $stmt = $conn->prepare("
        SELECT id, list_name, created_at
        FROM user_lists
        WHERE user_id = ?
        ORDER BY created_at DESC
    ");

    if (!$stmt) {
        sendJson([
            "success" => false,
            "message" => "Prepare failed: " . $conn->error
        ]);
    }

    $stmt->bind_param("i", $userId);
    $stmt->execute();

    $result = $stmt->get_result();
    $lists = [];

    while ($list = $result->fetch_assoc()) {
        $movieStmt = $conn->prepare("
            SELECT tmdb_id, title, poster_path, release_date, vote_average, added_at
            FROM list_movies
            WHERE list_id = ?
            ORDER BY added_at DESC
        ");

        if (!$movieStmt) {
            sendJson([
                "success" => false,
                "message" => "Movie prepare failed: " . $conn->error
            ]);
        }

        $listId = (int) $list["id"];
        $movieStmt->bind_param("i", $listId);
        $movieStmt->execute();

        $movieResult = $movieStmt->get_result();
        $movies = [];

        while ($movie = $movieResult->fetch_assoc()) {
            $movies[] = $movie;
        }

        $list["movies"] = $movies;
        $lists[] = $list;
    }

    sendJson([
        "success" => true,
        "lists" => $lists
    ]);
}

if ($method === "POST" && $action === "create") {
    $userId = requireLogin();
    $data = getJsonInput();

    $listName = trim($data["list_name"] ?? "");

    if ($listName === "") {
        sendJson([
            "success" => false,
            "message" => "List name is required."
        ]);
    }

    $stmt = $conn->prepare("
        INSERT INTO user_lists (user_id, list_name)
        VALUES (?, ?)
    ");

    if (!$stmt) {
        sendJson([
            "success" => false,
            "message" => "Prepare failed: " . $conn->error
        ]);
    }

    $stmt->bind_param("is", $userId, $listName);

    if (!$stmt->execute()) {
        sendJson([
            "success" => false,
            "message" => "Execute failed: " . $stmt->error
        ]);
    }

    sendJson([
        "success" => true,
        "message" => "List created successfully.",
        "list_id" => $conn->insert_id
    ]);
}

if ($method === "POST" && $action === "add_movie") {
    requireLogin();
    $data = getJsonInput();

    $listId = (int) ($data["list_id"] ?? 0);
    $tmdbId = (int) ($data["tmdb_id"] ?? 0);
    $title = trim($data["title"] ?? "");
    $posterPath = $data["poster_path"] ?? null;
    $releaseDate = $data["release_date"] ?? null;
    $voteAverage = $data["vote_average"] ?? null;

    if (!$listId || !$tmdbId || $title === "") {
        sendJson([
            "success" => false,
            "message" => "Missing movie information."
        ]);
    }

    $stmt = $conn->prepare("
        INSERT INTO list_movies
        (list_id, tmdb_id, title, poster_path, release_date, vote_average)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        sendJson([
            "success" => false,
            "message" => "Prepare failed: " . $conn->error
        ]);
    }

    $stmt->bind_param(
        "iisssd",
        $listId,
        $tmdbId,
        $title,
        $posterPath,
        $releaseDate,
        $voteAverage
    );

    if (!$stmt->execute()) {
        sendJson([
            "success" => false,
            "message" => "This movie is already in that list, or insert failed: " . $stmt->error
        ]);
    }

    sendJson([
        "success" => true,
        "message" => "Movie added to list."
    ]);
}

if ($method === "POST" && $action === "delete") {
    $userId = requireLogin();
    $data = getJsonInput();

    $listId = (int) ($data["list_id"] ?? 0);

    if (!$listId) {
        sendJson([
            "success" => false,
            "message" => "Missing list ID."
        ]);
    }

    $stmt = $conn->prepare("
        DELETE FROM user_lists
        WHERE id = ? AND user_id = ?
    ");

    if (!$stmt) {
        sendJson([
            "success" => false,
            "message" => "Prepare failed: " . $conn->error
        ]);
    }

    $stmt->bind_param("ii", $listId, $userId);

    if (!$stmt->execute()) {
        sendJson([
            "success" => false,
            "message" => "Delete failed: " . $stmt->error
        ]);
    }

    sendJson([
        "success" => true,
        "message" => "List deleted."
    ]);
}

if ($method === "POST" && $action === "remove_movie") {
    $userId = requireLogin();
    $data = getJsonInput();

    $listId = (int) ($data["list_id"] ?? 0);
    $tmdbId = (int) ($data["tmdb_id"] ?? 0);

    if (!$listId || !$tmdbId) {
        sendJson([
            "success" => false,
            "message" => "Missing list or movie ID."
        ]);
    }

    $stmt = $conn->prepare("
        DELETE lm
        FROM list_movies lm
        INNER JOIN user_lists ul ON lm.list_id = ul.id
        WHERE lm.list_id = ?
        AND lm.tmdb_id = ?
        AND ul.user_id = ?
    ");

    if (!$stmt) {
        sendJson([
            "success" => false,
            "message" => "Prepare failed: " . $conn->error
        ]);
    }

    $stmt->bind_param("iii", $listId, $tmdbId, $userId);

    if (!$stmt->execute()) {
        sendJson([
            "success" => false,
            "message" => "Remove failed: " . $stmt->error
        ]);
    }

    sendJson([
        "success" => true,
        "message" => "Movie removed from list."
    ]);
}

if ($method === "POST" && $action === "create_with_movies") {
    $userId = requireLogin();
    $data = getJsonInput();

    $listName = trim($data["list_name"] ?? "");
    $movies = $data["movies"] ?? [];

    if ($listName === "") {
        sendJson([
            "success" => false,
            "message" => "List name is required."
        ]);
    }

    if (!is_array($movies) || count($movies) === 0) {
        sendJson([
            "success" => false,
            "message" => "Please add at least one movie to the list."
        ]);
    }

    $stmt = $conn->prepare("
        INSERT INTO user_lists (user_id, list_name)
        VALUES (?, ?)
    ");

    if (!$stmt) {
        sendJson([
            "success" => false,
            "message" => "Prepare failed: " . $conn->error
        ]);
    }

    $stmt->bind_param("is", $userId, $listName);

    if (!$stmt->execute()) {
        sendJson([
            "success" => false,
            "message" => "List insert failed: " . $stmt->error
        ]);
    }

    $listId = $conn->insert_id;

    $movieStmt = $conn->prepare("
        INSERT INTO list_movies
        (list_id, tmdb_id, title, poster_path, release_date, vote_average)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    if (!$movieStmt) {
        sendJson([
            "success" => false,
            "message" => "Movie prepare failed: " . $conn->error
        ]);
    }

    foreach ($movies as $movie) {
        $tmdbId = (int) ($movie["id"] ?? 0);
        $title = trim($movie["title"] ?? "");
        $posterPath = $movie["poster_path"] ?? null;
        $releaseDate = $movie["release_date"] ?? null;
        $voteAverage = $movie["vote_average"] ?? null;

        if (!$tmdbId || $title === "") {
            continue;
        }

        $movieStmt->bind_param(
            "iisssd",
            $listId,
            $tmdbId,
            $title,
            $posterPath,
            $releaseDate,
            $voteAverage
        );

        $movieStmt->execute();
    }

    sendJson([
        "success" => true,
        "message" => "List created successfully.",
        "list_id" => $listId
    ]);
}

sendJson([
    "success" => false,
    "message" => "Invalid request."
]);
?>