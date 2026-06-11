<?php
require_once 'includes/init.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit();
}

$username = $_SESSION["username"];
$email = $_SESSION["email"];

$db = getDB();

$listStmt = $db->prepare("
    SELECT 
        ul.id,
        ul.list_name,
        ul.created_at,
        COUNT(lm.id) AS movie_count
    FROM user_lists ul
    LEFT JOIN list_movies lm ON ul.id = lm.list_id
    WHERE ul.user_id = ?
    GROUP BY ul.id, ul.list_name, ul.created_at
    ORDER BY ul.created_at DESC
");

$listStmt->execute([$_SESSION["user_id"]]);
$userLists = $listStmt->fetchAll();

$watchStmt = $db->prepare("
    SELECT tmdb_id, title, poster_path, added_at
    FROM watchlist
    WHERE user_id = ?
    ORDER BY added_at DESC
");

$watchStmt->execute([$_SESSION["user_id"]]);
$watchlist = $watchStmt->fetchAll();
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

    <a href="login.html" id="authNav">Login</a>
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

      <section id="settings" class="box">
          <h2>Account Settings</h2>
          <p class="muted-text">Change your username and password.</p>
          <a href="settings.php" class="btn">Go to Settings</a>
      </section>

      <section id="watchlist" class="box">
        <div class="profile-section-header">
          <div>
            <h2>To Watch List</h2>
            <p class="muted-text">Movies you saved to watch later.</p>
          </div>

          <a href="films.html" class="btn btn-secondary">Browse Films</a>
        </div>

        <?php if (empty($watchlist)): ?>
          <p>Your saved watchlist will appear here.</p>
        <?php else: ?>
          <div class="profile-watchlist-grid">
            <?php foreach ($watchlist as $movie): ?>
              <?php
                $poster = !empty($movie["poster_path"])
                  ? "https://image.tmdb.org/t/p/w500" . $movie["poster_path"]
                  : "https://via.placeholder.com/500x750?text=No+Poster";
              ?>

            <article class="profile-watchlist-card">
              <a href="film-detail.html?id=<?= e($movie["tmdb_id"]) ?>">
                <img src="<?= e($poster) ?>" alt="<?= e($movie["title"]) ?>">
                <h3><?= e($movie["title"]) ?></h3>
              </a>

              <button
                type="button"
                class="remove-watchlist-btn"
                data-tmdb-id="<?= e($movie["tmdb_id"]) ?>"
              >
                Remove
              </button>
            </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>

      <section id="created" class="box">
        <div class="profile-section-header">
          <div>
            <h2>Created List</h2>
            <p class="muted-text">Your movie lists created in CineByte.</p>
          </div>

          <a href="create-list.php" class="btn btn-secondary">Create List</a>
        </div>

        <?php if (empty($userLists)): ?>
          <p>Your created lists will appear here.</p>
        <?php else: ?>
          <div class="profile-list-grid">
            <?php foreach ($userLists as $list): ?>
              <?php
                $movieStmt = $db->prepare("
                    SELECT tmdb_id, title, poster_path
                    FROM list_movies
                    WHERE list_id = ?
                    ORDER BY added_at DESC
                    LIMIT 4
                ");

                $movieStmt->execute([$list["id"]]);
                $movies = $movieStmt->fetchAll();
              ?>

              <article class="profile-list-card">
                <h3><?= e($list["list_name"]) ?></h3>
                <p class="user-list-meta">
                  <?= e($list["movie_count"]) ?> movie<?= $list["movie_count"] == 1 ? "" : "s" ?>
                </p>

                <?php if (empty($movies)): ?>
                  <p class="muted-text">No movies added yet.</p>
                <?php else: ?>
                  <div class="profile-list-posters">
                    <?php foreach ($movies as $movie): ?>
                      <?php
                        $poster = !empty($movie["poster_path"])
                          ? "https://image.tmdb.org/t/p/w500" . $movie["poster_path"]
                          : "https://via.placeholder.com/500x750?text=No+Poster";
                      ?>

                      <a href="film-detail.html?id=<?= e($movie["tmdb_id"]) ?>">
                        <img src="<?= e($poster) ?>" alt="<?= e($movie["title"]) ?>">
                      </a>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>

                <a class="small-link" href="list-detail.php?id=<?= e($list["id"]) ?>">
                  View / Edit List
                </a>
                </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>

      <section id="journal" class="box">
        <h2>Added Journal</h2>

        <?php
        $stmt = $db->prepare(
            "SELECT * FROM journals WHERE user_id = ? ORDER BY created_at DESC"
        );

        $stmt->execute([$_SESSION['user_id']]);
        $journals = $stmt->fetchAll();

        if (empty($journals)):
        ?>
          <p>Your journal entries will appear here.</p>
        <?php else: ?>
          <?php foreach ($journals as $j): ?>
            <div class="review-box">
              <h3><?= e($j['title']) ?></h3>
              <p>⭐ <?= e($j['rating']) ?>/10</p>
              <p><?= e($j['review']) ?></p>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </section>

      <section id="settings" class="box">
        <a class="btn" href="logout.php">Logout</a>
      </section>
    </div>
  </section>
</main>

<footer>
  <p>&copy; 2026 CineByte. Web Design Project.</p>
</footer>

  <div id="journalSearchModal" class="journal-modal">
    <div class="journal-modal-box">
      <div class="journal-modal-header">
        <h2>Add to your journal...</h2>
        <button id="journalModalClose" class="journal-modal-close" type="button">×</button>
      </div>

      <div class="journal-modal-body">
        <input
          type="text"
          id="journalSearchInput"
          placeholder="Search for film..."
          autocomplete="off"
        />

        <div id="journalSearchResults" class="journal-modal-results"></div>
      </div>
    </div>
  </div>

<script src="script.js"></script>
</body>
</html>