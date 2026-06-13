<?php
require_once 'includes/init.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$db = getDB();
$userId = $_SESSION['user_id'];
$pageTitle = 'YouTube Videos';
$message = '';

function extractYouTubeIdPHP($url) {
    $url = trim($url);

    if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $url)) {
        return $url;
    }

    $parts = parse_url($url);

    if (!$parts || empty($parts['host'])) {
        return null;
    }

    $host = str_replace('www.', '', $parts['host']);
    $path = $parts['path'] ?? '';

    if (($host === 'youtube.com' || $host === 'm.youtube.com') && isset($parts['query'])) {
        parse_str($parts['query'], $query);

        if (!empty($query['v']) && preg_match('/^[a-zA-Z0-9_-]{11}$/', $query['v'])) {
            return $query['v'];
        }
    }

    if ($host === 'youtu.be') {
        $id = trim($path, '/');

        if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $id)) {
            return $id;
        }
    }

    if ($host === 'youtube.com' || $host === 'm.youtube.com') {
        if (preg_match('/\/(embed|shorts)\/([a-zA-Z0-9_-]{11})/', $path, $matches)) {
            return $matches[2];
        }
    }

    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_video') {
        $url = trim($_POST['youtube_url'] ?? '');
        $title = trim($_POST['youtube_title'] ?? '');
        $youtubeId = extractYouTubeIdPHP($url);

        if (!$youtubeId) {
            $message = 'Please paste a valid YouTube URL.';
        } else {
            if ($title === '') {
                $title = 'Untitled YouTube Video';
            }

            $watchUrl = 'https://www.youtube.com/watch?v=' . $youtubeId;

            $stmt = $db->prepare("
                SELECT video_id
                FROM videos
                WHERE user_id = ? AND youtube_id = ?
            ");
            $stmt->execute([$userId, $youtubeId]);
            $existing = $stmt->fetch();

            if ($existing) {
                $stmt = $db->prepare("
                    UPDATE videos
                    SET views_tracked = views_tracked + 1,
                        last_opened = NOW()
                    WHERE user_id = ? AND youtube_id = ?
                ");
                $stmt->execute([$userId, $youtubeId]);

                $message = 'This video is already tracked. Count updated.';
            } else {
                $stmt = $db->prepare("
                    INSERT INTO videos 
                    (user_id, youtube_id, title, url, watched, views_tracked)
                    VALUES (?, ?, ?, ?, 0, 1)
                ");
                $stmt->execute([$userId, $youtubeId, $title, $watchUrl]);

                $message = 'Video added successfully.';
            }
        }
    }

    if ($action === 'toggle_watched') {
        $videoId = (int)($_POST['video_id'] ?? 0);

        $stmt = $db->prepare("
            UPDATE videos
            SET watched = CASE 
                WHEN watched = 1 THEN 0 
                ELSE 1 
            END
            WHERE video_id = ? AND user_id = ?
        ");
        $stmt->execute([$videoId, $userId]);
    }

    if ($action === 'delete_video') {
        $videoId = (int)($_POST['video_id'] ?? 0);

        $stmt = $db->prepare("
            DELETE FROM videos
            WHERE video_id = ? AND user_id = ?
        ");
        $stmt->execute([$videoId, $userId]);
    }
}

$playId = $_GET['play'] ?? '';

if ($playId !== '') {
    $stmt = $db->prepare("
        SELECT *
        FROM videos
        WHERE user_id = ? AND youtube_id = ?
    ");
    $stmt->execute([$userId, $playId]);
    $activeVideo = $stmt->fetch();

    if ($activeVideo) {
        $stmt = $db->prepare("
            UPDATE videos
            SET views_tracked = views_tracked + 1,
                last_opened = NOW()
            WHERE user_id = ? AND youtube_id = ?
        ");
        $stmt->execute([$userId, $playId]);
    }
}

$stmt = $db->prepare("
    SELECT *
    FROM videos
    WHERE user_id = ?
    ORDER BY added_at DESC
");
$stmt->execute([$userId]);
$videos = $stmt->fetchAll();

if (empty($activeVideo) && !empty($videos)) {
    $activeVideo = $videos[0];
}
?>

<?php include 'includes/header.php'; ?>

<main>
  <section class="page-header">
    <h1>YouTube Videos</h1>
    <p>Paste a YouTube link to preview, track, and review videos.</p>
  </section>

  <section class="content-section">
    <h2>Track YouTube Video</h2>

    <?php if ($message): ?>
      <p class="form-message"><?= e($message) ?></p>
    <?php endif; ?>

    <form class="box youtube-form" method="POST" action="videos.php">
      <input type="hidden" name="action" value="add_video">

      <label for="youtube_url">YouTube URL</label>
      <input
        type="url"
        id="youtube_url"
        name="youtube_url"
        placeholder="Paste YouTube URL"
        required
      >

      <label for="youtube_title">Video title / note</label>
      <input
        type="text"
        id="youtube_title"
        name="youtube_title"
        placeholder="Example: Movie review, trailer, essay video"
      >

      <button class="btn" type="submit">Track Video</button>
    </form>

    <div class="video-box youtube-player">
      <?php if (!empty($activeVideo)): ?>
        <iframe
          src="https://www.youtube.com/embed/<?= e($activeVideo['youtube_id']) ?>"
          title="<?= e($activeVideo['title']) ?>"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
          allowfullscreen>
        </iframe>

        <p class="youtube-help">
          If the video does not load,
          <a href="<?= e($activeVideo['url']) ?>" target="_blank" rel="noopener">
            watch it on YouTube
          </a>.
        </p>
      <?php else: ?>
        <p style="color:#aaa;padding:25px;">
          Paste a YouTube URL above to preview it here.
        </p>
      <?php endif; ?>
    </div>

    <div class="section-heading-row">
      <h2>Tracked YouTube Videos</h2>
      <span>
        <?= count($videos) ?> video<?= count($videos) === 1 ? '' : 's' ?> tracked
      </span>
    </div>

    <?php if (!empty($videos)): ?>
      <?php foreach ($videos as $video): ?>
        <article class="review-box youtube-item">
          <h3><?= e($video['title']) ?></h3>

          <p>
            Status:
            <?= $video['watched'] ? 'Watched' : 'Not watched yet' ?>
          </p>

          <p>
            Tracked:
            <?= (int)$video['views_tracked'] ?>
            time<?= (int)$video['views_tracked'] === 1 ? '' : 's' ?>
          </p>

          <a
            class="btn"
            href="videos.php?play=<?= urlencode($video['youtube_id']) ?>">
            Play
          </a>

          <a
            class="btn"
            href="film-detail.php?type=video&youtube=<?= urlencode($video['youtube_id']) ?>&title=<?= urlencode($video['title']) ?>">
            View Details
          </a>

          <form method="POST" action="videos.php" style="display:inline;">
            <input type="hidden" name="action" value="toggle_watched">
            <input
              type="hidden"
              name="video_id"
              value="<?= (int)$video['video_id'] ?>"
            >

            <button class="btn btn-secondary" type="submit">
              <?= $video['watched'] ? 'Mark Unwatched' : 'Mark Watched' ?>
            </button>
          </form>

          <form method="POST" action="videos.php" style="display:inline;">
            <input type="hidden" name="action" value="delete_video">
            <input
              type="hidden"
              name="video_id"
              value="<?= (int)$video['video_id'] ?>"
            >

            <button class="btn btn-secondary" type="submit">
              Delete
            </button>
          </form>
        </article>
      <?php endforeach; ?>
    <?php else: ?>
      <p>No YouTube videos tracked yet.</p>
    <?php endif; ?>
  </section>
</main>

<?php include 'includes/footer.php'; ?>