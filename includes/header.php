<?php $user = currentUser(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>CineByte | <?= e($pageTitle ?? 'Home') ?></title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
<header class="site-header">
  <a href="index.php" class="logo">CineByte</a>
  <button class="menu-toggle" onclick="toggleMenu()">☰</button>
  <nav id="mainNav">
    <a href="index.php">Home</a>
    <a href="films.php">Films / Videos</a>
    <a href="lists.php">Lists</a>
    <a href="journal.php">Journal</a>
    <a href="profile.php">Profile</a>
    <?php if ($user): ?>
      <a href="auth.php?action=logout" style="color:#ff3c3c;">Logout</a>
    <?php else: ?>
      <a href="auth.php">Login</a>
    <?php endif; ?>
  </nav>
</header>
