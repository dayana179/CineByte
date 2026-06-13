<?php
require_once 'includes/init.php';

$pageTitle = 'Sign Up';

if (currentUser()) {
    header('Location: profile.php');
    exit();
}
?>

<?php include 'includes/header.php'; ?>

<main>
  <section class="page-header">
    <h1>Sign Up</h1>
    <p>Create your CineByte account.</p>
  </section>

  <section class="content-section auth-single-layout">
    <div class="box auth-card">
      <h2>Create Account</h2>

      <?php if (isset($_GET['error'])): ?>
        <div class="auth-message">
          <?= e($_GET['error']) ?>
        </div>
      <?php endif; ?>

      <form action="process_signup.php" method="POST">
        <input
          type="text"
          name="username"
          placeholder="Username"
          required
        />

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

        <button type="submit" class="btn">Sign Up</button>
      </form>

      <p class="auth-switch">
        Already have an account?
        <a href="login.php">Login</a>
      </p>
    </div>
  </section>
</main>

<?php include 'includes/footer.php'; ?>