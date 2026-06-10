<?php
session_start();
require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.html");
    exit();
}

$email = trim($_POST["email"]);
$password = $_POST["password"];

$stmt = $conn->prepare("SELECT id, username, email, password_hash FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: login.html?error=notfound");
    exit();
}

$user = $result->fetch_assoc();

if (!password_verify($password, $user["password_hash"])) {
    header("Location: login.html?error=password");
    exit();
}

$_SESSION["user_id"] = $user["id"];
$_SESSION["username"] = $user["username"];
$_SESSION["email"] = $user["email"];

header("Location: profile.php");
exit();
?>