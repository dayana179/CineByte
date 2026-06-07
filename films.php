<?php
require_once 'includes/init.php';
$pageTitle = 'Films / Videos';

$page   = max(1, (int)($_GET['page'] ?? 1));
$search = trim($_GET['q'] ?? '');

if ($search !== '') {
    $data = tmdbFetch('/search/movie', ['query' => $search, 'page' => $page]);
} else {
    $data = tmdbFetch('/movie/popular', ['page' => $page]);
}

$movies     = $data['results']    ?? [];
$totalPages = min($data['total_pages'] ?? 1, 500);
?>
<?php include 'includes/header.php'; ?>

<main>
  <section class="page-header">
    <h1>Films &amp; Videos</h1>
    <p>Browse popular films or search for something specific.</p>
  </section>

  <section class="content-section">
    <form method="GET" action="films.php" style="margin-bottom:30px;display:flex;gap:12px;flex-wrap:wrap;">
      <input type="text" name="q" placeholder="Search films..."
             value="<?= e($search) ?>" style="max-width:400px;margin-bottom:0;">
      <button class="btn" type="submit">Search</button>
      <?php if ($search): ?>
        <a href="films.php" class="btn btn-secondary">Clear</a>
      <?php endif; ?>
    </form>

    <?php if (empty($movies)): ?>
      <p>No results found<?= $search ? ' for "' . e($search) . '"' : '' ?>.</p>
    <?php else: ?>
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

      <div style="margin-top:40px;display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
        <?php if ($page > 1): ?>
          <a href="films.php?q=<?= urlencode($search) ?>&page=<?= $page - 1 ?>" class="btn btn-secondary">&larr; Prev</a>
        <?php endif; ?>
        <span style="color:#aaa;">Page <?= $page ?> of <?= $totalPages ?></span>
        <?php if ($page < $totalPages): ?>
          <a href="films.php?q=<?= urlencode($search) ?>&page=<?= $page + 1 ?>" class="btn">Next &rarr;</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </section>
</main>

<?php include 'includes/footer.php'; ?>
