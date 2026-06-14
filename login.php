<?php
require_once 'includes/init.php';

$pageTitle = 'Login';
$error = '';

function loginUser($email, $password) {
    $db = getDB();

    $stmt = $db->prepare("
        SELECT id, username, email, password_hash
        FROM users
        WHERE email = ?
        LIMIT 1
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        return 'notfound';
    }

    if (!password_verify($password, $user['password_hash'])) {
        return 'password';
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];

    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter your email and password.';
    } else {
        $result = loginUser($email, $password);

        if ($result === true) {
            header('Location: index.php');
            exit;
        }

        if ($result === 'password') {
            $error = 'Incorrect password. Please try again.';
        } else {
            $error = 'No account found with this email.';
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<main>
  <section class="page-header">
    <h1>Login</h1>
    <p>Access your CineByte account to manage your profile, lists, and journal.</p>
  </section>

  <section class="content-section auth-single-layout">
    <form class="box auth-card" method="POST" action="login.php">
      <h2>Welcome Back</h2>

      <?php if ($error): ?>
        <div class="form-message error"><?= e($error) ?></div>
      <?php endif; ?>

      <label>Email</label>
      <input type="email" name="email" autocomplete="email" required />

      <label>Password</label>
      <input type="password" name="password" required />

      <a class="forgot-password-link" href="forgot_password.php">Forgot password?</a>

      <br>

      <button class="btn" type="submit">Login</button>

      <p class="auth-switch">
        Don't have an account?
        <a href="signup.php">Sign Up</a>
      </p>
    </form>
  </section>
</main>

<?php include 'includes/footer.php'; ?>

<script>
  window.addEventListener("pageshow", function () {
    const passwordInput = document.querySelector('input[name="password"]');

    if (passwordInput) {
      passwordInput.value = "";
    }
  });
</script>