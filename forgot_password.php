<?php
session_start();
require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: forgot_password.html");
    exit();
}

$email = trim($_POST["email"]);

$stmt = $conn->prepare("SELECT id, email FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: forgot_password.html?error=notfound");
    exit();
}

$_SESSION["reset_email"] = $email;

header("Location: reset_password.html");
exit();
?>