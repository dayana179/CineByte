<?php
require_once 'includes/init.php';

$pageTitle = 'Forgot Password';
$error = '';

function startPasswordReset($email) {
    $db = getDB();

    $stmt = $db->prepare("
        SELECT id, email
        FROM users
        WHERE email = ?
        LIMIT 1
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        return false;
    }

    $_SESSION['reset_email'] = $user['email'];

    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if ($email === '') {
        $error = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        if (startPasswordReset($email)) {
            header('Location: reset_password.php');
            exit;
        }

        $error = 'No account found with this email.';
    }
}
?>

<?php include 'includes/header.php'; ?>

<main>
  <section class="page-header">
    <h1>Forgot Password</h1>
    <p>Enter your registered email to reset your password.</p>
  </section>

  <section class="content-section auth-single-layout">
    <form class="box auth-card" method="POST" action="forgot_password.php">
      <h2>Reset Request</h2>

      <?php if ($error): ?>
        <div class="form-message error"><?= e($error) ?></div>
      <?php endif; ?>

      <label>Email</label>
      <input type="email" name="email" required />

      <button class="btn" type="submit">Continue</button>

      <p class="auth-switch">
        Remember your password?
        <a href="login.php">Back to Login</a>
      </p>
    </form>
  </section>
</main>

<?php include 'includes/footer.php'; ?>