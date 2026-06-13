<?php
require_once 'includes/init.php';
requireLogin();

$pageTitle = 'Journal';

$db = getDB();
$msg = '';

$contentType = $_GET['type'] ?? $_POST['content_type'] ?? 'film';

$tmdb_id = (int)($_GET['tmdb_id'] ?? $_POST['tmdb_id'] ?? 0);
$youtube_id = $_GET['youtube'] ?? $_POST['youtube_id'] ?? '';

$movie = [];

if ($contentType === 'film' && $tmdb_id > 0) {
    $movie = tmdbFetch("/movie/$tmdb_id");
}

if ($contentType === 'video') {
    $selectedTitle = $_GET['title'] ?? $_POST['title'] ?? 'YouTube Video';
    $selectedPoster = $_GET['poster'] ?? $_POST['poster_path'] ?? '';

    if ($selectedPoster === '' && $youtube_id !== '') {
        $selectedPoster = "https://img.youtube.com/vi/" . $youtube_id . "/hqdefault.jpg";
    }
} else {
    $selectedTitle = $movie['title'] ?? ($_GET['title'] ?? $_POST['title'] ?? '');
    $selectedPoster = $movie['poster_path'] ?? ($_GET['poster'] ?? $_POST['poster_path'] ?? '');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentType = $_POST['content_type'] ?? 'film';
    $title = trim($_POST['title'] ?? '');
    $rating = (int)($_POST['rating'] ?? 0);
    $review = trim($_POST['review'] ?? '');
    $tmdb_id = (int)($_POST['tmdb_id'] ?? 0);
    $youtube_id = trim($_POST['youtube_id'] ?? '');
    $poster_path = trim($_POST['poster_path'] ?? '');

    if ($contentType === 'film') {
        if ($title === '' || $tmdb_id <= 0 || $rating < 1 || $rating > 10 || $review === '') {
            $msg = "Please choose a movie and complete all journal fields.";
        } else {
            $stmt = $db->prepare("
                INSERT INTO journals
                (user_id, content_type, tmdb_id, youtube_id, title, poster_path, rating, review)
                VALUES (?, ?, ?, NULL, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $_SESSION['user_id'],
                'film',
                $tmdb_id,
                $title,
                $poster_path,
                $rating,
                $review
            ]);

            $msg = "Journal added!";
        }
    }

    if ($contentType === 'video') {
        if ($title === '' || $youtube_id === '' || $rating < 1 || $rating > 10 || $review === '') {
            $msg = "Please choose a YouTube video and complete all journal fields.";
        } else {
            $stmt = $db->prepare("
                INSERT INTO journals
                (user_id, content_type, tmdb_id, youtube_id, title, poster_path, rating, review)
                VALUES (?, ?, NULL, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $_SESSION['user_id'],
                'video',
                $youtube_id,
                $title,
                $poster_path,
                $rating,
                $review
            ]);

            $msg = "Journal added!";
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<main>
  <section class="page-header">
    <h1>Journal</h1>
    <p>Record what you watched and write your personal viewing notes.</p>
  </section>

  <section class="content-section journal-layout journal-full-width">
    <form method="POST" class="box journal-form-box">

      <?php if (!empty($msg)): ?>
        <p class="auth-message"><?= e($msg) ?></p>
      <?php endif; ?>

      <div class="journal-content">

        <div class="journal-poster-area">
          <?php if (!empty($selectedPoster)): ?>

            <?php if ($contentType === 'video'): ?>
              <img
                class="journal-poster"
                src="<?= e($selectedPoster) ?>"
                alt="<?= e($selectedTitle) ?>"
              >
            <?php else: ?>
              <img
                class="journal-poster"
                src="<?= e(TMDB_IMAGE_BASE . $selectedPoster) ?>"
                alt="<?= e($selectedTitle) ?>"
              >
            <?php endif; ?>

          <?php else: ?>
            <div class="journal-poster-placeholder">
              No Poster
            </div>
          <?php endif; ?>
        </div>

        <div class="journal-form-area">

          <h2>
            Add <?= $contentType === 'video' ? 'YouTube Video' : 'Film' ?> Journal
          </h2>

          <label>
            <?= $contentType === 'video' ? 'YouTube Video' : 'Movie' ?>
          </label>

          <input
            type="text"
            value="<?= e($selectedTitle) ?>"
            readonly
          >

          <input
            type="hidden"
            name="content_type"
            value="<?= e($contentType) ?>"
          >

          <input
            type="hidden"
            name="title"
            value="<?= e($selectedTitle) ?>"
          >

          <input
            type="hidden"
            name="tmdb_id"
            value="<?= (int)$tmdb_id ?>"
          >

          <input
            type="hidden"
            name="youtube_id"
            value="<?= e($youtube_id) ?>"
          >

          <input
            type="hidden"
            name="poster_path"
            value="<?= e($selectedPoster) ?>"
          >

          <label>Your Rating (/10)</label>

          <input
            type="number"
            name="rating"
            min="1"
            max="10"
            required
          >

          <label>Review</label>

          <textarea
            name="review"
            rows="6"
            required
          ></textarea>

          <button class="btn" type="submit">
            Save Journal
          </button>

        </div>

      </div>

    </form>
  </section>
</main>

<?php include 'includes/footer.php'; ?>