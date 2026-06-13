<?php
require_once 'includes/init.php';

$pageTitle = 'Login';

if (currentUser()) {
    header('Location: profile.php');
    exit();
}
?>

<?php include 'includes/header.php'; ?>

<main>
  <section class="page-header">
    <h1>Login</h1>
    <p>Access your CineByte account.</p>
  </section>

  <section class="content-section auth-single-layout">
    <div class="box auth-card">
      <h2>Welcome Back</h2>

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

      <form action="process_login.php" method="POST">
        <input
          type="email"
          name="email"
          placeholder="Email"
          required
        />

        <input
          type="password"
          name="password"
          placeholder="Password"
          required
        />

        <a href="forgot_password.php" class="forgot-password-link">
          Forgot password?
        </a>

        <button type="submit" class="btn">Login</button>
      </form>

      <p class="auth-switch">
        Do not have an account?
        <a href="signup.php">Sign up</a>
      </p>
    </div>
  </section>
</main>

<?php include 'includes/footer.php'; ?>