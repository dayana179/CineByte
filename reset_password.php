<?php
session_start();
require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: reset_password.html");
    exit();
}

if (!isset($_SESSION["reset_email"])) {
    header("Location: reset_password.html?error=expired");
    exit();
}

$newPassword = $_POST["new_password"];
$confirmPassword = $_POST["confirm_password"];

if (strlen($newPassword) < 6) {
    header("Location: reset_password.html?error=short");
    exit();
}

if ($newPassword !== $confirmPassword) {
    header("Location: reset_password.html?error=mismatch");
    exit();
}

$email = $_SESSION["reset_email"];
$passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
$stmt->bind_param("ss", $passwordHash, $email);
$stmt->execute();

unset($_SESSION["reset_email"]);

header("Location: login.html?reset=success");
exit();
?>

