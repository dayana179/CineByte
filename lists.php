<?php
require_once 'includes/init.php';
$pageTitle = 'Lists';
$user      = currentUser();
$listMsg   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireLogin();
    $db = getDB();

    if (($_POST['action'] ?? '') === 'create_list') {
        $name = trim($_POST['list_name'] ?? '');
        if ($name) {
            $stmt = $db->prepare('INSERT INTO user_lists (user_id, list_name) VALUES (?, ?)');
            $stmt->execute([$_SESSION['user_id'], $name]);
            $listMsg = 'List "' . e($name) . '" created!';
        }
    } elseif (($_POST['action'] ?? '') === 'delete_list') {
        $stmt = $db->prepare('DELETE FROM user_lists WHERE id = ? AND user_id = ?');
        $stmt->execute([(int)$_POST['list_id'], $_SESSION['user_id']]);
    }
}

// Fetch TMDb lists
$nowPlaying  = array_slice(tmdbFetch('/movie/now_playing')['results'] ?? [], 0, 8);
$topRated    = array_slice(tmdbFetch('/movie/top_rated')['results']   ?? [], 0, 5);
$mostWatched = array_slice(tmdbFetch('/movie/popular')['results']     ?? [], 0, 8);

// User's DB lists
$userLists = [];
if ($user) {
    $db   = getDB();
    $stmt = $db->prepare('SELECT * FROM user_lists WHERE user_id = ? ORDER BY created_at DESC');
    $stmt->execute([$_SESSION['user_id']]);
    $userLists = $stmt->fetchAll();
}

function movieCard(array $m): string {
    $poster = $m['poster_path']
        ? htmlspecialchars(TMDB_IMAGE_BASE . $m['poster_path'], ENT_QUOTES)
        : 'https://via.placeholder.com/500x750?text=No+Poster';
    $title  = htmlspecialchars($m['title'], ENT_QUOTES);
    $rating = number_format($m['vote_average'], 1);
    $year   = htmlspecialchars(substr($m['release_date'] ?? 'N/A', 0, 4), ENT_QUOTES);
    $id     = (int)$m['id'];
    return "<article class='movie-card'>
      <img src='$poster' alt='$title poster'>
      <div>
        <h3>$title</h3>
        <p>&#11088; $rating &nbsp;|&nbsp; $year</p>
        <a href='film-detail.php?id=$id' class='small-link'>View Details &rarr;</a>
      </div>
    </article>";
}
?>
<?php include 'includes/header.php'; ?>

<main>
  <section class="page-header">
    <h1>Lists</h1>
    <p>Explore featured lists, top picks, most watched content, and create your own.</p>
  </section>

  <?php if ($listMsg): ?>
    <p style="margin:0 7% 20px;padding:12px;background:#1a3a1a;border:1px solid #4caf50;border-radius:8px;color:#a5d6a7;"><?= $listMsg ?></p>
  <?php endif; ?>

  <section class="content-section">
    <h2>Featured &mdash; Now Playing</h2>
    <div class="card-grid"><?php foreach ($nowPlaying  as $m) echo movieCard($m); ?></div>
  </section>

  <section class="content-section">
    <h2>Top 5 &mdash; All Time Rated</h2>
    <div class="card-grid"><?php foreach ($topRated    as $m) echo movieCard($m); ?></div>
  </section>

  <section class="content-section">
    <h2>Most Watched &mdash; Trending Now</h2>
    <div class="card-grid"><?php foreach ($mostWatched as $m) echo movieCard($m); ?></div>
  </section>

  <section class="content-section">
    <h2>Create Your Own List</h2>
    <?php if ($user): ?>
      <form method="POST" class="box" style="max-width:500px;margin-bottom:24px;">
        <input type="hidden" name="action" value="create_list">
        <label>List Name</label>
        <input type="text" name="list_name" placeholder="e.g. My Sci-Fi Favourites" required>
        <button class="btn" type="submit">Create List</button>
      </form>

      <div class="box">
        <h3 style="margin-bottom:14px;">Your Lists</h3>
        <?php if (empty($userLists)): ?>
          <p style="color:#aaa;">No lists created yet.</p>
        <?php else: ?>
          <ul style="list-style:none;padding:0;">
            <?php foreach ($userLists as $list): ?>
              <li style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid #2b323d;">
                <span><?= e($list['list_name']) ?></span>
                <form method="POST" style="margin:0;">
                  <input type="hidden" name="action"  value="delete_list">
                  <input type="hidden" name="list_id" value="<?= (int)$list['id'] ?>">
                  <button type="submit" style="background:none;border:1px solid #ff3c3c;color:#ff3c3c;padding:4px 10px;border-radius:6px;cursor:pointer;">Delete</button>
                </form>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div class="box">
        <p>Please <a href="login.php" style="color:#ff3c3c;">login</a> to create your own lists.</p>
      </div>
    <?php endif; ?>
  </section>
</main>

<?php include 'includes/footer.php'; ?>
