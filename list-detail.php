<?php
require_once 'includes/init.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit();
}

$db = getDB();

$listId = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

$listStmt = $db->prepare("
    SELECT id, list_name, created_at
    FROM user_lists
    WHERE id = ? AND user_id = ?
");

$listStmt->execute([$listId, $_SESSION["user_id"]]);
$list = $listStmt->fetch();

if (!$list) {
    echo "List not found.";
    exit();
}

$movieStmt = $db->prepare("
    SELECT tmdb_id, title, poster_path, release_date, vote_average, added_at
    FROM list_movies
    WHERE list_id = ?
    ORDER BY added_at DESC
");

$movieStmt->execute([$listId]);
$movies = $movieStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <title>CineByte | <?= e($list["list_name"]) ?></title>
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
        <svg class="search-icon" viewBox="0 0 24 24" aria-hidden="true">
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
    <p class="tagline">Movie List</p>
    <h1><?= e($list["list_name"]) ?></h1>
    <p><?= count($movies) ?> movie<?= count($movies) === 1 ? "" : "s" ?> in this list.</p>

    <div class="hero-actions">
      <a href="lists.html#createdListsSection" class="btn btn-secondary">Back to Lists</a>
    </div>
  </section>

  <section class="content-section list-detail-layout">
    <section class="box">
      <h2>Edit List</h2>

      <form id="editListNameForm">
        <input
          type="hidden"
          id="editListId"
          value="<?= e($list["id"]) ?>"
        />

        <label for="editListNameInput">List name</label>
        <input
          type="text"
          id="editListNameInput"
          value="<?= e($list["list_name"]) ?>"
          required
        />

        <button type="submit" class="btn">Save Changes</button>
      </form>

      <div class="add-movies-to-list">
        <h2>Add Movies</h2>

        <form id="editListMovieSearchForm" class="film-main-search">
          <input
            type="text"
            id="editListMovieSearchInput"
            placeholder="Search for a movie..."
            autocomplete="off"
          />

          <button type="submit" class="btn btn-secondary">Search</button>
        </form>

        <p id="editListSearchStatus" class="muted-text"></p>

        <div id="editListSearchResults" class="list-search-results"></div>
      </div>
    </section>

    <section class="box">
      <h2>Movies in this List</h2>

      <?php if (empty($movies)): ?>
        <p class="muted-text">No movies added yet.</p>
      <?php else: ?>
        <div class="list-detail-movie-grid">
          <?php foreach ($movies as $movie): ?>
            <?php
              $poster = !empty($movie["poster_path"])
                ? "https://image.tmdb.org/t/p/w500" . $movie["poster_path"]
                : "https://via.placeholder.com/500x750?text=No+Poster";
            ?>

            <article class="list-detail-movie-card">
              <a href="film-detail.html?id=<?= e($movie["tmdb_id"]) ?>">
                <img src="<?= e($poster) ?>" alt="<?= e($movie["title"]) ?>">
                <h3><?= e($movie["title"]) ?></h3>
              </a>

              <p>
                <?= e($movie["release_date"] ? substr($movie["release_date"], 0, 4) : "Unknown") ?>
                <?php if ($movie["vote_average"] !== null): ?>
                  • ⭐ <?= e($movie["vote_average"]) ?>
                <?php endif; ?>
              </p>

              <button
                type="button"
                class="remove-detail-movie-btn"
                data-list-id="<?= e($list["id"]) ?>"
                data-tmdb-id="<?= e($movie["tmdb_id"]) ?>"
              >
                Remove
              </button>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
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