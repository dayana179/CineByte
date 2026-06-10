<?php
require_once 'includes/init.php';
requireLogin();
$pageTitle = 'Profile';

$db  = getDB();
$uid = $_SESSION['user_id'];
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_settings') {
        $username = trim($_POST['username'] ?? '');
        $theme    = in_array($_POST['theme'] ?? '', ['dark','light']) ? $_POST['theme'] : 'dark';
        $notifs   = isset($_POST['notifications']) ? 1 : 0;
        if ($username) {
            $db->prepare('UPDATE users SET username=?, theme=?, notifications=? WHERE id=?')
               ->execute([$username, $theme, $notifs, $uid]);
            $_SESSION['username'] = $username;
            $_SESSION['theme']    = $theme;
            $msg = 'Settings updated!';
        }

    } elseif ($action === 'change_password') {
        $row = $db->prepare('SELECT password_hash FROM users WHERE id=?');
        $row->execute([$uid]);
        $hash    = $row->fetchColumn();
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password']     ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!password_verify($current, $hash)) {
            $msg = 'Current password is incorrect.';
        } elseif (strlen($new) < 6) {
            $msg = 'New password must be at least 6 characters.';
        } elseif ($new !== $confirm) {
            $msg = 'New passwords do not match.';
        } else {
            $db->prepare('UPDATE users SET password_hash=? WHERE id=?')
               ->execute([password_hash($new, PASSWORD_DEFAULT), $uid]);
            $msg = 'Password changed successfully!';
        }

    } elseif ($action === 'remove_watchlist') {
        $db->prepare('DELETE FROM watchlist WHERE user_id=? AND tmdb_id=?')
           ->execute([$uid, (int)$_POST['tmdb_id']]);
    }
}

// Fetch all data
$stmt = $db->prepare('SELECT * FROM users WHERE id=?');
$stmt->execute([$uid]);
$user = $stmt->fetch();

$stmt = $db->prepare('SELECT * FROM watchlist WHERE user_id=? ORDER BY added_at DESC');
$stmt->execute([$uid]);
$watchlist = $stmt->fetchAll();

$stmt = $db->prepare('SELECT * FROM user_lists WHERE user_id=? ORDER BY created_at DESC');
$stmt->execute([$uid]);
$lists = $stmt->fetchAll();

$stmt = $db->prepare('SELECT * FROM journals WHERE user_id=? ORDER BY created_at DESC');
$stmt->execute([$uid]);
$journals = $stmt->fetchAll();

?>
<?php include 'includes/header.php'; ?>

<main>
  <section class="page-header">
    <h1>User Profile</h1>
    <p>Manage your account, watchlist, journal entries, created lists, and settings.</p>
  </section>

  <?php if ($msg): ?>
    <p style="margin:0 7% 20px;padding:12px;background:#1a3a1a;border:1px solid #4caf50;border-radius:8px;color:#a5d6a7;"><?= e($msg) ?></p>
  <?php endif; ?>

  <section class="content-section profile-layout">
    <aside class="profile-sidebar">
      <h2>Menu</h2>
      <a href="#profileInfo">Profile</a>
      <a href="#watchlist">To Watch List</a>
      <a href="#created">Created Lists</a>
      <a href="#journal">Added Journals</a>
      <a href="#settings">Settings</a>
      <a href="auth.php?action=logout" style="color:#ff3c3c;margin-top:20px;">Logout</a>
    </aside>

    <div class="profile-content">

      <!-- Profile Info -->
      <section id="profileInfo" class="box">
        <h2>Profile</h2>
        <p><strong>Username:</strong> <?= e($user['username']) ?></p>
        <p><strong>Email:</strong>    <?= e($user['email']) ?></p>
        <p><strong>Member since:</strong> <?= e(date('d M Y', strtotime($user['created_at']))) ?></p>
      </section>

      <!-- Watchlist -->
      <section id="watchlist" class="box">
        <h2>To Watch List</h2>
        <?php if (empty($watchlist)): ?>
          <p style="color:#aaa;">No watchlist items yet. <a href="films.php" style="color:#ff3c3c;">Browse films &rarr;</a></p>
        <?php else: ?>
          <div class="card-grid">
            <?php foreach ($watchlist as $item): ?>
              <article class="movie-card">
                <img src="<?= $item['poster_path'] ? e(TMDB_IMAGE_BASE . $item['poster_path']) : 'https://via.placeholder.com/500x750?text=No+Poster' ?>" alt="<?= e($item['title']) ?>">
                <div>
                  <h3><?= e($item['title']) ?></h3>
                  <a href="film-detail.php?id=<?= (int)$item['tmdb_id'] ?>" class="small-link">View &rarr;</a>
                  <form method="POST" style="margin-top:8px;">
                    <input type="hidden" name="action"  value="remove_watchlist">
                    <input type="hidden" name="tmdb_id" value="<?= (int)$item['tmdb_id'] ?>">
                    <button type="submit" style="background:none;border:1px solid #ff3c3c;color:#ff3c3c;padding:4px 10px;border-radius:6px;cursor:pointer;font-size:13px;">Remove</button>
                  </form>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>

      <!-- Created Lists -->
      <section id="created" class="box">
        <h2>Created Lists</h2>
        <?php if (empty($lists)): ?>
          <p style="color:#aaa;">No lists yet. <a href="lists.php" style="color:#ff3c3c;">Create one &rarr;</a></p>
        <?php else: ?>
          <ul style="list-style:none;padding:0;">
            <?php foreach ($lists as $list): ?>
              <li style="padding:10px 0;border-bottom:1px solid #2b323d;">
                <?= e($list['list_name']) ?>
                <small style="color:#666;margin-left:10px;"><?= e(date('d M Y', strtotime($list['created_at']))) ?></small>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </section>

      <!-- Journals -->
      <section id="journal" class="box">
        <h2>Added Journals</h2>
        <?php if (empty($journals)): ?>
          <p style="color:#aaa;">No journal entries yet. <a href="journal.php" style="color:#ff3c3c;">Add one &rarr;</a></p>
        <?php else: ?>
          <?php foreach ($journals as $j): ?>
            <article class="review-box" style="margin-bottom:16px;">
              <h3><?= e($j['title']) ?></h3>
              <p style="color:#ffc84a;">⭐ <?= (int)$j['rating'] ?>/10</p>
              <p style="color:#ccc;"><?= e($j['review']) ?></p>
              <small style="color:#666;"><?= e(date('d M Y', strtotime($j['created_at']))) ?></small>
            </article>
          <?php endforeach; ?>
        <?php endif; ?>
      </section>

      <!-- Settings -->
      <section id="settings" class="box">
        <h2>Settings</h2>
        <form method="POST">
          <input type="hidden" name="action" value="update_settings">
          <label>Username</label>
          <input type="text" name="username" value="<?= e($user['username']) ?>" required>
          <label>Theme</label>
          <select name="theme">
            <option value="dark"  <?= $user['theme']==='dark'  ? 'selected':'' ?>>Dark</option>
            <option value="light" <?= $user['theme']==='light' ? 'selected':'' ?>>Light</option>
          </select>
          <label style="display:flex;align-items:center;gap:10px;margin-bottom:18px;">
            <input type="checkbox" name="notifications" <?= $user['notifications'] ? 'checked':'' ?> style="width:auto;margin:0;">
            Enable Notifications
          </label>
          <button class="btn" type="submit">Save Settings</button>
        </form>

        <hr style="border-color:#2b323d;margin:24px 0;">

        <h3>Change Password</h3>
        <form method="POST" style="margin-top:16px;">
          <input type="hidden" name="action" value="change_password">
          <label>Current Password</label>
          <input type="password" name="current_password" required>
          <label>New Password</label>
          <input type="password" name="new_password" required minlength="6">
          <label>Confirm New Password</label>
          <input type="password" name="confirm_password" required>
          <button class="btn btn-secondary" type="submit">Change Password</button>
        </form>
      </section>

    </div>
  </section>
</main>

<?php include 'includes/footer.php'; ?>
