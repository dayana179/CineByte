<?php
require_once 'includes/init.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) redirect('films.php');

$movie  = tmdbFetch("/movie/$id");
$videos = tmdbFetch("/movie/$id/videos");

if (empty($movie) || isset($movie['status_code'])) redirect('films.php');

// Find first official YouTube trailer
$trailer = null;
foreach ($videos['results'] ?? [] as $v) {
    if ($v['site'] === 'YouTube' && $v['type'] === 'Trailer') {
        $trailer = $v['key'];
        break;
    }
}

$pageTitle   = $movie['title'] ?? 'Film Detail';
$genres      = implode(' &bull; ', array_column($movie['genres'] ?? [], 'name'));
$watchlistMsg = '';

// Handle Add to Watchlist
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_watchlist') {
    if (!isLoggedIn()) redirect('login.php');
    $db   = getDB();
    $stmt = $db->prepare(
        'INSERT IGNORE INTO watchlist (user_id, tmdb_id, title, poster_path) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([
        $_SESSION['user_id'], $id,
        $movie['title'], $movie['poster_path'] ?? ''
    ]);
    $watchlistMsg = e($movie['title']) . ' added to your watchlist!';
}
?>
<?php include 'includes/header.php'; ?>

<main>
  <?php if ($watchlistMsg): ?>
    <p style="margin:20px 7%;padding:12px;background:#1a3a1a;border:1px solid #4caf50;border-radius:8px;color:#a5d6a7;">
      <?= $watchlistMsg ?>
    </p>
  <?php endif; ?>

  <section class="detail-layout">
    <div>
      <img class="detail-poster"
        src="<?= $movie['poster_path'] ? e(TMDB_IMAGE_BASE . $movie['poster_path']) : 'https://via.placeholder.com/500x750?text=No+Poster' ?>"
        alt="<?= e($movie['title']) ?> poster"
      >
    </div>

    <div class="detail-info">
      <h1><?= e($movie['title']) ?></h1>
      <p><?= $genres ?></p>
      <p style="color:#aaa;">
        &#11088; <?= number_format($movie['vote_average'] ?? 0, 1) ?> &nbsp;&bull;&nbsp;
        <?= e(substr($movie['release_date'] ?? '', 0, 4)) ?> &nbsp;&bull;&nbsp;
        <?= (int)($movie['runtime'] ?? 0) ?> min
      </p>
      <p><?= e($movie['overview'] ?? '') ?></p>

      <?php if ($trailer): ?>
        <div class="video-box">
          <iframe src="https://www.youtube.com/embed/<?= e($trailer) ?>"
                  title="YouTube trailer" allowfullscreen></iframe>
        </div>
      <?php endif; ?>

      <form method="POST" style="display:inline;">
        <input type="hidden" name="action" value="add_watchlist">
        <button class="btn" type="submit">+ Add to Watchlist</button>
      </form>

      <a href="journal.php?tmdb_id=<?= $id ?>&title=<?= urlencode($movie['title']) ?>"
         class="btn btn-secondary">Write Journal</a>
    </div>
  </section>
</main>

<?php include 'includes/footer.php'; ?>
