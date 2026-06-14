<?php
require_once 'includes/init.php';

$pageTitle = 'Sign Up';
$error = '';
$success = '';

function createUserAccount($username, $email, $password) {
    $db = getDB();

    $checkStmt = $db->prepare("
        SELECT id
        FROM users
        WHERE email = ? OR username = ?
        LIMIT 1
    ");
    $checkStmt->execute([$email, $username]);

    if ($checkStmt->fetch()) {
        return 'Account with this email or username already exists.';
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $db->prepare("
        INSERT INTO users (username, email, password_hash)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$username, $email, $hashedPassword]);

    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $email === '' || $password === '') {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $result = createUserAccount($username, $email, $password);

        if ($result === true) {
            $success = 'Account created successfully. You can now login.';
        } else {
            $error = $result;
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<main>
  <section class="page-header">
    <h1>Create Account</h1>
    <p>Join CineByte and start building your personal film space.</p>
  </section>

  <section class="content-section auth-single-layout">
    <form class="box auth-card" method="POST" action="signup.php" autocomplete="off">
      <h2>Sign Up</h2>

      <?php if ($error): ?>
        <div class="form-message error"><?= e($error) ?></div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="form-message success"><?= e($success) ?></div>
      <?php endif; ?>

      <label>Username</label>
      <input type="text" name="username" autocomplete="off" required />

      <label>Email</label>
      <input type="email" name="email" autocomplete="off" required />

      <label>Password</label>
      <input type="password" name="password" minlength="6" autocomplete="new-password" required />

      <button class="btn" type="submit">Create Account</button>

      <p class="auth-switch">
        Already have an account?
        <a href="login.php">Login</a>
      </p>
    </form>
  </section>
</main>

<?php include 'includes/footer.php'; ?>