<?php
require_once 'includes/init.php';

requireLogin();

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
    $pageTitle = 'List Not Found';
    include 'includes/header.php';
    ?>

    <main>
      <section class="page-header">
        <p class="tagline">Movie List</p>
        <h1>List Not Found</h1>
        <p>This list does not exist or you do not have permission to view it.</p>

        <div class="hero-actions">
          <a href="lists.php#createdListsSection" class="btn btn-secondary">Back to Lists</a>
        </div>
      </section>
    </main>

    <?php
    include 'includes/footer.php';
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

$pageTitle = $list["list_name"];
?>

<?php include 'includes/header.php'; ?>

<main>
  <section class="page-header">
    <p class="tagline">Movie List</p>
    <h1><?= e($list["list_name"]) ?></h1>
    <p><?= count($movies) ?> movie<?= count($movies) === 1 ? "" : "s" ?> in this list.</p>

    <div class="hero-actions">
      <a href="lists.php#createdListsSection" class="btn btn-secondary">Back to Lists</a>
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
          autocomplete="off"
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

              $year = !empty($movie["release_date"])
                ? substr($movie["release_date"], 0, 4)
                : "Unknown";
            ?>

            <article class="list-detail-movie-card">
              <a href="film-detail.php?type=film&id=<?= e($movie["tmdb_id"]) ?>">
                <img src="<?= e($poster) ?>" alt="<?= e($movie["title"]) ?>">
                <h3><?= e($movie["title"]) ?></h3>
              </a>

              <p>
                <?= e($year) ?>
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

<?php include 'includes/footer.php'; ?>