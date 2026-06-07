<?php
require_once 'includes/init.php';

// Logout
if (($_GET['action'] ?? '') === 'logout') {
    session_destroy();
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $db     = getDB();

    if ($action === 'signup') {
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$username || !$email || !$password) {
            $error = 'All fields are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email address.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } else {
            $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'That email is already registered.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare('INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)');
                $stmt->execute([$username, $email, $hash]);
                $_SESSION['user_id']  = $db->lastInsertId();
                $_SESSION['username'] = $username;
                $_SESSION['email']    = $email;
                $_SESSION['theme']    = 'dark';
                redirect('profile.php');
            }
        }

    } elseif ($action === 'login') {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$email || !$password) {
            $error = 'Email and password are required.';
        } else {
            $stmt = $db->prepare('SELECT * FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if (!$user || !password_verify($password, $user['password_hash'])) {
                $error = 'Invalid email or password.';
            } else {
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email']    = $user['email'];
                $_SESSION['theme']    = $user['theme'];
                redirect('profile.php');
            }
        }
    }
}

$pageTitle = 'Account';
?>
<?php include 'includes/header.php'; ?>

<main>
  <section class="page-header">
    <h1>Account</h1>
    <p>Login or create an account to access personalised watchlists, journals, and lists.</p>
  </section>

  <?php if ($error): ?>
    <p style="margin:0 7% 20px;padding:14px;background:#3d1515;border:1px solid #ff3c3c;border-radius:8px;color:#ff9999;">
      <?= e($error) ?>
    </p>
  <?php endif; ?>

  <section class="content-section auth-layout">

    <form class="box" method="POST" action="auth.php">
      <input type="hidden" name="action" value="signup">
      <h2>Sign Up</h2>
      <label>Username</label>
      <input type="text" name="username" required>
      <label>Email</label>
      <input type="email" name="email" required>
      <label>Password</label>
      <input type="password" name="password" required minlength="6">
      <button class="btn" type="submit">Create Account</button>
    </form>

    <form class="box" method="POST" action="auth.php">
      <input type="hidden" name="action" value="login">
      <h2>Login</h2>
      <label>Email</label>
      <input type="email" name="email" required>
      <label>Password</label>
      <input type="password" name="password" required>
      <button class="btn" type="submit">Login</button>
    </form>

  </section>
</main>

<?php include 'includes/footer.php'; ?>
