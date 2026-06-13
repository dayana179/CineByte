<?php
require_once 'includes/init.php';

$pageTitle = 'Forgot Password';

if (currentUser()) {
    header('Location: profile.php');
    exit();
}
?>

<?php include 'includes/header.php'; ?>

<main>
  <section class="page-header">
    <h1>Forgot Password</h1>
    <p>Enter your email to reset your password.</p>
  </section>

  <section class="content-section auth-single-layout">
    <div class="box auth-card">
      <h2>Reset Request</h2>

      <?php if (isset($_GET['error'])): ?>
        <div class="auth-message">
          <?= e($_GET['error']) ?>
        </div>
      <?php endif; ?>

      <?php if (isset($_GET['success'])): ?>
        <div class="auth-message">
          <?= e($_GET['success']) ?>
        </div>
      <?php endif; ?>

      <form action="process_forgot_password.php" method="POST">
        <input
          type="email"
          name="email"
          placeholder="Enter your email"
          required
        />

        <button type="submit" class="btn">Send Reset Link</button>
      </form>

      <p class="auth-switch">
        Remember your password?
        <a href="login.php">Login</a>
      </p>
    </div>
  </section>
</main>

<?php include 'includes/footer.php'; ?>