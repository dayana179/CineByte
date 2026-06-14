<?php
require_once 'includes/init.php';

$pageTitle = 'Journal';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$db = getDB();

$userId = $_SESSION["user_id"];

$tmdbId = isset($_GET["tmdb_id"]) ? (int) $_GET["tmdb_id"] : null;
$youtubeId = $_GET["youtube_id"] ?? ($_GET["youtube"] ?? null);
$contentType = $_GET["type"] ?? (!empty($youtubeId) ? "video" : "film");

$title = $_GET["title"] ?? "";
$poster = $_GET["poster"] ?? "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $contentType = $_POST["content_type"] ?? "film";
    $title = trim($_POST["title"] ?? "");
    $rating = (int) ($_POST["rating"] ?? 0);
    $review = trim($_POST["review"] ?? "");

    $tmdbId = !empty($_POST["tmdb_id"]) ? (int) $_POST["tmdb_id"] : null;
    $youtubeId = !empty($_POST["youtube_id"]) ? $_POST["youtube_id"] : null;

    if ($title === "" || $rating < 1 || $rating > 10 || $review === "") {
        $error = "Please complete all fields correctly.";
    } else {
        $stmt = $db->prepare("
            INSERT INTO journals
            (user_id, tmdb_id, youtube_id, content_type, title, rating, review)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $userId,
            $tmdbId,
            $youtubeId,
            $contentType,
            $title,
            $rating,
            $review
        ]);

        header("Location: profile.php#journal");
        exit();
    }
}

if ($contentType === "video") {
    $displayPoster = !empty($youtubeId)
        ? "https://img.youtube.com/vi/" . $youtubeId . "/hqdefault.jpg"
        : "https://via.placeholder.com/500x750?text=No+Thumbnail";
} else {
    $displayPoster = !empty($poster)
        ? "https://image.tmdb.org/t/p/w500" . $poster
        : "https://via.placeholder.com/500x750?text=No+Poster";
}
?>

<?php include 'includes/header.php'; ?>

<main>
  <section class="page-header">
    <h1>Add Journal</h1>
    <p>Write a review and rating for a film or video.</p>
  </section>

  <section class="content-section journal-layout">
    <div class="box journal-form-box journal-full-width">
      <?php if (!empty($error)): ?>
        <div class="auth-message">
          <?= e($error) ?>
        </div>
      <?php endif; ?>

      <div class="journal-content">
        <div>
          <img
            src="<?= e($displayPoster) ?>"
            alt="<?= e($title ?: 'Selected title') ?>"
            class="journal-poster"
          />
        </div>

        <div class="journal-form-area">
          <h2><?= e($title ?: 'Selected Title') ?></h2>

          <form method="POST">
            <input
              type="hidden"
              name="content_type"
              value="<?= e($contentType) ?>"
            />

            <input
              type="hidden"
              name="tmdb_id"
              value="<?= e($tmdbId ?? '') ?>"
            />

            <input
              type="hidden"
              name="youtube_id"
              value="<?= e($youtubeId ?? '') ?>"
            />

            <input
              type="hidden"
              name="title"
              value="<?= e($title) ?>"
            />

            <label for="rating">Rating out of 10</label>
            <input
              type="number"
              id="rating"
              name="rating"
              min="1"
              max="10"
              placeholder="e.g. 8"
              required
            />

            <label for="review">Review</label>
            <textarea
              id="review"
              name="review"
              placeholder="Write your thoughts here..."
              required
            ></textarea>

            <button type="submit" class="btn">Save Journal</button>
          </form>
        </div>
      </div>
    </div>
  </section>
</main>

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

<?php include 'includes/footer.php'; ?>