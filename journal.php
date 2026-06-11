<?php
require_once 'includes/init.php';
requireLogin();

$pageTitle = 'Journal';

$db = getDB();
$msg = '';

$tmdb_id = (int)($_GET['tmdb_id'] ?? $_POST['tmdb_id'] ?? 0);
$movie = [];

if ($tmdb_id > 0) {
    $movie = tmdbFetch("/movie/$tmdb_id");
}

$selectedTitle = $movie['title'] ?? ($_GET['title'] ?? $_POST['title'] ?? '');
$selectedPoster = $movie['poster_path'] ?? ($_GET['poster'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $rating = (int)($_POST['rating'] ?? 0);
    $review = trim($_POST['review'] ?? '');
    $tmdb_id = (int)($_POST['tmdb_id'] ?? 0);

    if ($title === '' || $tmdb_id <= 0 || $rating < 1 || $rating > 10 || $review === '') {
        $msg = "Please choose a movie and complete all journal fields.";
    } else {
        $stmt = $db->prepare(
            "INSERT INTO journals 
            (user_id, tmdb_id, title, rating, review)
            VALUES (?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $_SESSION['user_id'],
            $tmdb_id,
            $title,
            $rating,
            $review
        ]);

        $msg = "Journal added!";
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
          <img
            class="journal-poster"
            src="<?= e(TMDB_IMAGE_BASE . $selectedPoster) ?>"
            alt="<?= e($selectedTitle) ?>"
          >
        <?php else: ?>
          <div class="journal-poster-placeholder">
            No Poster
          </div>
        <?php endif; ?>

      </div>

      <div class="journal-form-area">

        <h2>Add Journal</h2>

        <label>Movie</label>

        <input
          type="text"
          value="<?= e($selectedTitle) ?>"
          readonly
        >

        <input
          type="hidden"
          name="title"
          value="<?= e($selectedTitle) ?>"
        >

        <input
          type="hidden"
          name="tmdb_id"
          value="<?= $tmdb_id ?>"
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