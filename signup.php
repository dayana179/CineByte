<?php

session_start();
require_once "db.php";

$username = trim($_POST["username"]);
$email = trim($_POST["email"]);
$password = $_POST["password"];

$check = $conn->prepare(
"SELECT id FROM users WHERE email=?"
);

$check->bind_param("s",$email);
$check->execute();

$result = $check->get_result();

if($result->num_rows > 0)
{
    header("Location: signup.html?error=email");
    exit();
}

$passwordHash =
password_hash(
$password,
PASSWORD_DEFAULT
);

$stmt = $conn->prepare(
"INSERT INTO users(username,email,password_hash)
VALUES(?,?,?)"
);

$stmt->bind_param(
"sss",
$username,
$email,
$passwordHash
);

$stmt->execute();

$_SESSION["user_id"] =
$stmt->insert_id;

$_SESSION["username"] =
$username;

$_SESSION["email"] =
$email;

header("Location: login.html");
exit();

?>