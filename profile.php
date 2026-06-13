<?php
require_once 'includes/init.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$db = getDB();

$userId = $_SESSION["user_id"];
$username = $_SESSION["username"] ?? "User";
$email = $_SESSION["email"] ?? "";

/* Remove from watchlist */
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "remove_watchlist") {
    $contentType = $_POST["content_type"] ?? "film";

    if ($contentType === "video") {
        $youtubeId = $_POST["youtube_id"] ?? "";

        $stmt = $db->prepare("
            DELETE FROM watchlist
            WHERE user_id = ? AND content_type = 'video' AND youtube_id = ?
        ");
        $stmt->execute([$userId, $youtubeId]);
    } else {
        $tmdbId = (int)($_POST["tmdb_id"] ?? 0);

        $stmt = $db->prepare("
            DELETE FROM watchlist
            WHERE user_id = ? AND content_type = 'film' AND tmdb_id = ?
        ");
        $stmt->execute([$userId, $tmdbId]);
    }

    header("Location: profile.php#watchlist");
    exit();
}

/* User created lists */
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
$listStmt->execute([$userId]);
$userLists = $listStmt->fetchAll();

/* Watchlist, supports films and videos */
$watchStmt = $db->prepare("
    SELECT 
        tmdb_id,
        youtube_id,
        content_type,
        title,
        poster_path,
        added_at
    FROM watchlist
    WHERE user_id = ?
    ORDER BY added_at DESC
");
$watchStmt->execute([$userId]);
$watchlist = $watchStmt->fetchAll();

/* Journals, supports films and videos */
$journalStmt = $db->prepare("
    SELECT *
    FROM journals
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$journalStmt->execute([$userId]);
$journals = $journalStmt->fetchAll();
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
    <a href="films.php">Films</a>
    <a href="videos.php">Videos</a>
    <a href="lists.html">Lists</a>
    <a href="profile.php">Profile</a>

    <div class="header-search">
      <button type="button" id="headerSearchToggle" class="header-search-btn" aria-label="Open search">
        <svg class="search-icon" viewBox="0 0 24 24" aria-hidden="true">
          <path d="M10.8 5.2a5.6 5.6 0 1 1 0 11.2a5.6 5.6 0 0 1 0-11.2Zm0-1.7a7.3 7.3 0 1 0 4.55 13.02l3.56 3.56a.9.9 0 0 0 1.27-1.27l-3.56-3.56A7.3 7.3 0 0 0 10.8 3.5Z"/>
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
      <a href="#accountSettings">Settings</a>
    </aside>

    <div class="profile-content">

      <section id="profileInfo" class="box">
        <h2>Active Account</h2>
        <p><strong>Username:</strong> <?= e($username) ?></p>
        <p><strong>Email:</strong> <?= e($email) ?></p>
        <p class="user-status">Status: Logged in</p>
      </section>

      <section id="accountSettings" class="box">
        <h2>Account Settings</h2>
        <p class="muted-text">Change your username and password.</p>
        <a href="settings.php" class="btn">Go to Settings</a>
      </section>

      <section id="watchlist" class="box">
        <div class="profile-section-header">
          <div>
            <h2>To Watch List</h2>
            <p class="muted-text">Films and YouTube videos you saved to watch later.</p>
          </div>

          <div>
            <a href="films.php" class="btn btn-secondary">Browse Films</a>
            <a href="videos.php" class="btn btn-secondary">Browse Videos</a>
          </div>
        </div>

        <?php if (empty($watchlist)): ?>
          <p>Your saved watchlist will appear here.</p>
        <?php else: ?>
          <div class="profile-watchlist-grid">
            <?php foreach ($watchlist as $item): ?>
              <?php
                $type = $item["content_type"] ?? "film";

                if ($type === "video") {
                    $poster = !empty($item["youtube_id"])
                        ? "https://img.youtube.com/vi/" . $item["youtube_id"] . "/hqdefault.jpg"
                        : "https://via.placeholder.com/500x750?text=No+Thumbnail";

                    $detailLink = "film-detail.php?type=video&youtube=" . urlencode($item["youtube_id"]) . "&title=" . urlencode($item["title"]);
                } else {
                    $poster = !empty($item["poster_path"])
                        ? "https://image.tmdb.org/t/p/w500" . $item["poster_path"]
                        : "https://via.placeholder.com/500x750?text=No+Poster";

                    $detailLink = "film-detail.php?id=" . urlencode($item["tmdb_id"]);
                }
              ?>

              <article class="profile-watchlist-card">
                <a href="<?= e($detailLink) ?>">
                  <img src="<?= e($poster) ?>" alt="<?= e($item["title"]) ?>">
                  <h3><?= e($item["title"]) ?></h3>
                  <p class="muted-text"><?= $type === "video" ? "YouTube Video" : "Film" ?></p>
                </a>

                <form method="POST" onsubmit="return confirm('Remove this item from your watchlist?');">
                <input type="hidden" name="action" value="remove_watchlist">
                <input type="hidden" name="content_type" value="<?= e($type) ?>">

                <?php if ($type === "video"): ?>
                  <input type="hidden" name="youtube_id" value="<?= e($item["youtube_id"]) ?>">
                <?php else: ?>
                  <input type="hidden" name="tmdb_id" value="<?= e($item["tmdb_id"]) ?>">
                <?php endif; ?>

                <button type="submit" class="remove-watchlist-btn">
                  Remove
                </button>
              </form> 
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

                      <a href="film-detail.php?id=<?= e($movie["tmdb_id"]) ?>">
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

        <?php if (empty($journals)): ?>
          <p>Your journal entries will appear here.</p>
        <?php else: ?>
          <?php foreach ($journals as $j): ?>
            <?php
              $journalType = $j["content_type"] ?? "film";

              if ($journalType === "video") {
                  $journalLink = "film-detail.php?type=video&youtube=" . urlencode($j["youtube_id"] ?? "") . "&title=" . urlencode($j["title"]);
              } else {
                  $journalLink = "film-detail.php?id=" . urlencode($j["tmdb_id"] ?? "");
              }
            ?>

            <div class="review-box">
              <h3>
                <a href="<?= e($journalLink) ?>">
                  <?= e($j["title"]) ?>
                </a>
              </h3>

              <p class="muted-text">
                <?= $journalType === "video" ? "YouTube Video Review" : "Film Review" ?>
              </p>

              <p>⭐ <?= e($j["rating"]) ?>/10</p>
              <p><?= e($j["review"]) ?></p>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </section>

      <section class="box">
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