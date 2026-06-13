<?php
require_once 'includes/init.php';

$pageTitle = 'Reset Password';
$error = '';
$success = '';

function resetPasswordFromSession($newPassword) {
    $db = getDB();

    if (empty($_SESSION['reset_email'])) {
        return false;
    }

    $email = $_SESSION['reset_email'];
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    $stmt = $db->prepare("
        UPDATE users
        SET password_hash = ?
        WHERE email = ?
    ");
    $stmt->execute([$hashedPassword, $email]);

    unset($_SESSION['reset_email']);

    return true;
}

if (empty($_SESSION['reset_email'])) {
    $error = 'Reset session expired. Please enter your email again.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($_SESSION['reset_email'])) {
        $error = 'Reset session expired. Please enter your email again.';
    } elseif ($newPassword === '' || $confirmPassword === '') {
        $error = 'Please fill in all fields.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } elseif (strlen($newPassword) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        if (resetPasswordFromSession($newPassword)) {
            $success = 'Password reset successful. Please login with your new password.';
        } else {
            $error = 'Reset session expired. Please enter your email again.';
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<main>
  <section class="page-header">
    <h1>Reset Password</h1>
    <p>Create a new password for your CineByte account.</p>
  </section>

  <section class="content-section auth-single-layout">
    <form class="box auth-card" method="POST" action="reset_password.php">
      <h2>New Password</h2>

      <?php if ($error): ?>
        <div class="form-message error"><?= e($error) ?></div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="form-message success"><?= e($success) ?></div>

        <p class="auth-switch">
          <a href="login.php">Go to Login</a>
        </p>
      <?php elseif (!empty($_SESSION['reset_email'])): ?>
        <label>New Password</label>
        <input type="password" name="new_password" minlength="6" required />

        <label>Confirm New Password</label>
        <input type="password" name="confirm_password" minlength="6" required />

        <button class="btn" type="submit">Reset Password</button>
      <?php else: ?>
        <p class="auth-switch">
          <a href="forgot_password.php">Request a new reset</a>
        </p>
      <?php endif; ?>
    </form>
  </section>
</main>

<?php include 'includes/footer.php'; ?>