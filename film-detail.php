<?php
require_once 'includes/init.php';

$type = $_GET['type'] ?? 'film';
$youtubeId = $_GET['youtube'] ?? '';
$id = (int)($_GET['id'] ?? 0);

$pageTitle = 'Content Detail';
$watchlistMsg = '';
$isVideo = ($type === 'video' && !empty($youtubeId));

if ($isVideo) {
    $videoTitle = $_GET['title'] ?? 'YouTube Video';
    $pageTitle = $videoTitle;
    $videoUrl = "https://www.youtube.com/watch?v=" . urlencode($youtubeId);
    $thumbnail = "https://img.youtube.com/vi/" . e($youtubeId) . "/hqdefault.jpg";
} else {
    if ($id <= 0) redirect('films.php');

    $movie  = tmdbFetch("/movie/$id");
    $videos = tmdbFetch("/movie/$id/videos");

    if (empty($movie) || isset($movie['status_code'])) redirect('films.php');

    $trailer = null;
    foreach ($videos['results'] ?? [] as $v) {
        if ($v['site'] === 'YouTube' && $v['type'] === 'Trailer') {
            $trailer = $v['key'];
            break;
        }
    }

    $pageTitle = $movie['title'] ?? 'Film Detail';
    $genres = implode(' &bull; ', array_column($movie['genres'] ?? [], 'name'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_watchlist') {
    if (!isLoggedIn()) redirect('login.php');

    $db = getDB();

    if ($isVideo) {
        $stmt = $db->prepare(
            'INSERT IGNORE INTO watchlist (user_id, content_type, youtube_id, title, poster_path)
             VALUES (?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $_SESSION['user_id'],
            'video',
            $youtubeId,
            $videoTitle,
            $thumbnail
        ]);

        $watchlistMsg = e($videoTitle) . ' added to your watchlist!';
    } else {
        $stmt = $db->prepare(
            'INSERT IGNORE INTO watchlist (user_id, content_type, tmdb_id, title, poster_path)
             VALUES (?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $_SESSION['user_id'],
            'film',
            $id,
            $movie['title'],
            $movie['poster_path'] ?? ''
        ]);

        $watchlistMsg = e($movie['title']) . ' added to your watchlist!';
    }
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
      <?php if ($isVideo): ?>
        <img
          class="detail-poster"
          src="<?= $thumbnail ?>"
          alt="<?= e($videoTitle) ?> thumbnail"
        >
      <?php else: ?>
        <img
          class="detail-poster"
          src="<?= $movie['poster_path'] ? e(TMDB_IMAGE_BASE . $movie['poster_path']) : 'https://via.placeholder.com/500x750?text=No+Poster' ?>"
          alt="<?= e($movie['title']) ?> poster"
        >
      <?php endif; ?>
    </div>

    <div class="detail-info">
      <?php if ($isVideo): ?>
        <h1><?= e($videoTitle) ?></h1>
        <p>YouTube Video</p>
        <p style="color:#aaa;">Saved from YouTube</p>
        <p>This page displays the selected YouTube video details.</p>

        <div class="video-box">
          <iframe
            src="https://www.youtube.com/embed/<?= e($youtubeId) ?>"
            title="<?= e($videoTitle) ?>"
            allowfullscreen>
          </iframe>
        </div>

      <?php else: ?>
        <h1><?= e($movie['title']) ?></h1>
        <p><?= $genres ?></p>

        <p style="color:#aaa;">
          &#11088; <?= number_format($movie['vote_average'] ?? 0, 1) ?>
          &nbsp;&bull;&nbsp;
          <?= e(substr($movie['release_date'] ?? '', 0, 4)) ?>
          &nbsp;&bull;&nbsp;
          <?= (int)($movie['runtime'] ?? 0) ?> min
        </p>

        <p><?= e($movie['overview'] ?? '') ?></p>

        <?php if ($trailer): ?>
          <div class="video-box">
            <iframe
              src="https://www.youtube.com/embed/<?= e($trailer) ?>"
              title="YouTube trailer"
              allowfullscreen>
            </iframe>
          </div>
        <?php endif; ?>
      <?php endif; ?>

      <form method="POST" style="display:inline;">
        <input type="hidden" name="action" value="add_watchlist">
        <button class="btn" type="submit">+ Add to Watchlist</button>
      </form>

      <?php if ($isVideo): ?>
        <a
          href="journal.php?type=video&youtube=<?= urlencode($youtubeId) ?>&title=<?= urlencode($videoTitle) ?>&poster=<?= urlencode($thumbnail) ?>"
          class="btn btn-secondary">
          Write Journal
        </a>
      <?php else: ?>
        <a
          href="journal.php?type=film&tmdb_id=<?= $id ?>&title=<?= urlencode($movie['title']) ?>&poster=<?= urlencode($movie['poster_path'] ?? '') ?>"
          class="btn btn-secondary">
          Write Journal
        </a> 
      <?php endif; ?>
    </div>
  </section>

  <section class="content-section">
    <h2>User Reviews</h2>

    <article class="review-box">
      <strong>Sample User</strong>
      <p>Great visual style and strong storytelling. Worth watching.</p>
    </article>
  </section>
</main>

<?php include 'includes/footer.php'; ?>