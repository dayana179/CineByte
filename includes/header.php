<?php
if (!isset($pageTitle)) {
    $pageTitle = 'Home';
}

$user = function_exists('currentUser') ? currentUser() : null;
$currentPage = basename($_SERVER['PHP_SELF']);

function activeNav($pageName, $currentPage) {
    return $pageName === $currentPage ? 'active-nav' : '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <title>CineByte | <?= e($pageTitle) ?></title>

  <link rel="stylesheet" href="style.css" />
</head>

<body>
<header class="site-header">
  <a href="index.php" class="logo">CineByte</a>

  <button class="menu-toggle" type="button" onclick="toggleMenu()">☰</button>

  <nav id="mainNav">
    <a href="index.php" class="<?= activeNav('index.php', $currentPage) ?>">Home</a>
    <a href="films.php" class="<?= activeNav('films.php', $currentPage) ?>">Films</a>
    <a href="videos.php" class="<?= activeNav('videos.php', $currentPage) ?>">Videos</a>
    <a href="lists.php" class="<?= activeNav('lists.php', $currentPage) ?>">Lists</a>

    <?php if ($user): ?>
      <a href="profile.php" class="<?= activeNav('profile.php', $currentPage) ?>">Profile</a>
    <?php endif; ?>

    <div class="header-search">
      <button type="button" id="headerSearchToggle" class="header-search-btn" aria-label="Open search">
        <svg
          class="search-icon"
          viewBox="0 0 24 24"
          aria-hidden="true"
        >
          <path
            d="M10.8 5.2a5.6 5.6 0 1 1 0 11.2a5.6 5.6 0 0 1 0-11.2Zm0-1.7a7.3 7.3 0 1 0 4.55 13.02l3.56 3.56a.9.9 0 0 0 1.27-1.27l-3.56-3.56A7.3 7.3 0 0 0 10.8 3.5Z"
          />
        </svg>
      </button>

      <div id="headerSearchBox" class="header-search-box">
        <input
          type="text"
          id="headerSearchInput"
          placeholder="Search films..."
          autocomplete="off"
        />

        <div id="headerSearchResults" class="header-search-results"></div>
      </div>
    </div>

    <?php if ($user): ?>
      <a
        href="#"
        id="authNav"
        class="journal-open-btn"
        onclick="openJournalModal(); return false;"
      >
        + Journal
      </a>
    <?php else: ?>
      <a href="login.php" id="authNav">Login</a>
    <?php endif; ?>
  </nav>
</header>