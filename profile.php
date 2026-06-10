<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit();
}

$username = $_SESSION["username"];
$email = $_SESSION["email"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>CineByte | Profile</title>
  <link rel="stylesheet" href="style.css" />
</head>

<body>
<header class="site-header">
  <a href="index.html" class="logo">CineByte</a>
  <button class="menu-toggle" onclick="toggleMenu()">☰</button>

  <nav id="mainNav">
    <a href="index.html">Home</a>
    <a href="films.html">Films</a>
    <a href="lists.html">Lists</a>
    <a href="journal.html">Journal</a>
    <a href="profile.php">Profile</a>
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

    <a href="logout.php" id="authNav">Logout</a>
  </nav>
</header>

<main>
  <section class="page-header">
    <h1>User Profile</h1>
    <p>Manage your CineByte account and personalised content.</p>
  </section>

  <section class="content-section profile-layout">
    <aside class="profile-sidebar">
      <h2>Profile Menu</h2>
      <a href="#profileInfo">Profile</a>
      <a href="#watchlist">To Watch List</a>
      <a href="#created">Created List</a>
      <a href="#journal">Added Journal</a>
      <a href="#settings">Settings</a>
    </aside>

    <div class="profile-content">
      <section id="profileInfo" class="box">
        <h2>Active Account</h2>
        <p><strong>Username:</strong> <?php echo htmlspecialchars($username); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
        <p class="user-status">Status: Logged in</p>
      </section>

      <section id="watchlist" class="box">
        <h2>To Watch List</h2>
        <p>Your saved watchlist will appear here.</p>
      </section>

      <section id="created" class="box">
        <h2>Created List</h2>
        <p>Your created lists will appear here.</p>
      </section>

      <section id="journal" class="box">
        <h2>Added Journal</h2>
        <p>Your journal entries will appear here.</p>
      </section>

      <section id="settings" class="box">
        <h2>Settings</h2>
        <a class="btn" href="logout.php">Logout</a>
      </section>
    </div>
  </section>
</main>

<footer>
  <p>&copy; 2026 CineByte. Web Design Project.</p>
</footer>

<script src="script.js"></script>
</body>
</html>