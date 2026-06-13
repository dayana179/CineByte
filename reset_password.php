<?php
require_once 'includes/init.php';

$pageTitle = 'Reset Password';

if (currentUser()) {
    header('Location: profile.php');
    exit();
}

$token = $_GET['token'] ?? '';
?>

<?php include 'includes/header.php'; ?>

<main>
  <section class="page-header">
    <h1>Reset Password</h1>
    <p>Create a new password for your CineByte account.</p>
  </section>

  <section class="content-section auth-single-layout">
    <div class="box auth-card">
      <h2>New Password</h2>

      <?php if (isset($_GET['error'])): ?>
        <div class="auth-message">
          <?= e($_GET['error']) ?>
        </div>
      <?php endif; ?>

      <?php if (empty($token)): ?>
        <p class="muted-text">Invalid reset link.</p>
        <a href="forgot_password.php" class="btn btn-secondary">
          Request New Link
        </a>
      <?php else: ?>
        <form action="process_reset_password.php" method="POST">
          <input
            type="hidden"
            name="token"
            value="<?= e($token) ?>"
          />

          <input
            type="password"
            name="password"
            placeholder="New password"
            required
          />

          <input
            type="password"
            name="confirm_password"
            placeholder="Confirm new password"
            required
          />

          <button type="submit" class="btn">Reset Password</button>
        </form>
      <?php endif; ?>

      <p class="auth-switch">
        Back to
        <a href="login.php">Login</a>
      </p>
    </div>
  </section>
</main>

<?php include 'includes/footer.php'; ?>