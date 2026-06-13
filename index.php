<?php
require_once 'includes/init.php';
$pageTitle = 'Homepage';

$data   = tmdbFetch('/movie/popular', ['page' => 1]);
$movies = array_slice($data['results'] ?? [], 0, 3);
?>
<?php include 'includes/header.php'; ?>
<!-- <link rel="stylesheet" href="style.css" /> -->

<main>
  <section class="hero">
    <div class="hero-content">
      <p class="tagline">Film Discovery &amp; Personal Tracking Platform</p>
      <h1>Track what you watch. Discover what to watch next.</h1>
      <p>CineByte helps users explore films and YouTube videos, create watchlists,
         write journals, and organize viewing activities in one place.</p>
      <div class="hero-actions">
        <a href="films.php" class="btn">Explore Films</a>
        <a href="#" class="btn btn-secondary" onclick="openJournalModal(); return false;">Add Journal</a>
      </div>
    </div>
  </section>

  <section class="content-section">
    <h2>Featured Content</h2>
    <div class="card-grid">
      <?php foreach ($movies as $movie): ?>
        <article class="movie-card">
          <img
            src="<?= $movie['poster_path'] ? e(TMDB_IMAGE_BASE . $movie['poster_path']) : 'https://via.placeholder.com/500x750?text=No+Poster' ?>"
            alt="<?= e($movie['title']) ?> poster"
          >
          <div>
            <h3><?= e($movie['title']) ?></h3>
            <p>&#11088; <?= number_format($movie['vote_average'], 1) ?> &nbsp;|&nbsp; <?= e(substr($movie['release_date'] ?? 'N/A', 0, 4)) ?></p>
            <a href="film-detail.php?id=<?= (int)$movie['id'] ?>" class="small-link">View Details &rarr;</a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>
</main>

<?php include 'includes/footer.php'; ?>
