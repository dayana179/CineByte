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
      <a href="#" id="authNav" class="journal-open-btn" onclick="openJournalModal(); return false;">+ Journal</a>
    <?php else: ?>
      <a href="login.html" id="authNav">Login</a>
    <?php endif; ?>
    </nav>
</header>
