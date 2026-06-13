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

// POST Handling (Watchlist and Comments)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_watchlist') {
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
    // Handle adding comments
    elseif ($action === 'add_comment') {
        if (!isLoggedIn()) redirect('login.php');

        $commentText = trim($_POST['comment_text'] ?? '');
        if (!empty($commentText)) {
            $db = getDB();
            $contentId = $isVideo ? $youtubeId : (string)$id;

            $stmt = $db->prepare(
                'INSERT INTO comments (user_id, content_type, content_id, comment_text)
                 VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([
                $_SESSION['user_id'],
                $type,
                $contentId,
                $commentText
            ]);

            // Redirect back to the exact same page state to cleanly refresh and show the comment
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
        }
    }
}

// Fetch dynamic comments for this specific item
$db = getDB();
$contentId = $isVideo ? $youtubeId : (string)$id;
$commentStmt = $db->prepare(
    'SELECT comments.*, users.username 
     FROM comments 
     JOIN users ON comments.user_id = users.id 
     WHERE comments.content_type = ? AND comments.content_id = ? 
     ORDER BY comments.created_at DESC'
);
$commentStmt->execute([$type, $contentId]);
$comments = $commentStmt->fetchAll();
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
          </iframe >
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

  <section class="content-section" style="margin-top: 40px;">
    <h2>Comments (<?= count($comments) ?>)</h2>

    <div class="comment-input-area" style="margin-bottom: 30px;">
      <?php if (isLoggedIn()): ?>
        <form method="POST" action="">
          <input type="hidden" name="action" value="add_comment">
          <div style="display: flex; gap: 15px; align-items: flex-start;">
            <div style="width: 40px; height: 40px; background: #e91e63; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 18px; flex-shrink: 0;">
              <?= e(strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1))) ?>
            </div>
            <div style="flex-grow: 1;">
              <textarea 
                name="comment_text" 
                placeholder="Add a comment..." 
                required 
                style="width: 100%; min-height: 40px; background: transparent; border: none; border-bottom: 2px solid #444; color: #fff; padding: 5px 0; resize: vertical; outline: none; font-family: inherit; transition: border-color 0.2s;"
                onfocus="this.style.borderColor='#fff'"
                onblur="this.style.borderColor='#444'"
              ></textarea>
              <div style="text-align: right; margin-top: 8px;">
                <button type="submit" class="btn" style="padding: 6px 14px; font-size: 13px;">Comment</button>
              </div>
            </div>
          </div>
        </form>
      <?php else: ?>
        <p style="color: #aaa; background: #222; padding: 15px; border-radius: 6px;">
          Please <a href="login.php" style="color: #4caf50; text-decoration: underline;">log in</a> to participate in the discussion.
        </p>
      <?php endif; ?>
    </div>

    <div class="comments-display-list">
      <?php if (empty($comments)): ?>
        <p style="color: #666; font-style: italic;">No comments yet. Start the conversation!</p>
      <?php else: ?>
        <?php foreach ($comments as $comment): ?>
          <article class="review-box" style="display: flex; gap: 15px; margin-bottom: 20px; border-bottom: 1px solid #222; padding-bottom: 15px;">
            <div style="width: 40px; height: 40px; background: #555; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; flex-shrink: 0;">
              <?= e(strtoupper(substr($comment['username'], 0, 1))) ?>
            </div>
            <div>
              <div style="margin-bottom: 4px;">
                <strong style="color: #fff; font-size: 14px;"><?= e($comment['username']) ?></strong>
                <span style="color: #666; font-size: 11px; margin-left: 8px;">
                  <?= date('M d, Y', strtotime($comment['created_at'])) ?>
                </span>
              </div>
              <p style="margin: 0; color: #ccc; font-size: 14px; white-space: pre-wrap; line-height: 1.4;">
                <?= e($comment['comment_text']) ?>
              </p>
            </div>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>
</main>

<?php include 'includes/footer.php'; ?>