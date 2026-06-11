<?php
require_once __DIR__ . '/includes/init.php';
requireLogin();

$pageTitle = 'Settings';
$user = currentUser();
$uid = $user['id'];

$db = getDB();

$msg = '';
$error = '';

$stmt = $db->prepare("SELECT id, username, password_hash FROM users WHERE id = ?");
$stmt->execute([$uid]);
$userData = $stmt->fetch();

if (!$userData) {
    die('User not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // =========================
    // CHANGE USERNAME
    // =========================
    if ($action === 'change_username') {
        $newUsername = trim($_POST['username'] ?? '');

        if ($newUsername === '') {
            $error = 'Username is required.';
        } elseif (strlen($newUsername) < 3) {
            $error = 'Username must be at least 3 characters.';
        } else {
            // Check if username is already used by another user
            $checkStmt = $db->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $checkStmt->execute([$newUsername, $uid]);
            $existingUser = $checkStmt->fetch();

            if ($existingUser) {
                $error = 'This username is already taken.';
            } else {
                $updateStmt = $db->prepare("UPDATE users SET username = ? WHERE id = ?");
                $updateStmt->execute([$newUsername, $uid]);

                $_SESSION['username'] = $newUsername;

                $msg = 'Username updated successfully.';

                // Refresh data after update
                $stmt = $db->prepare("SELECT id, username, password_hash FROM users WHERE id = ?");
                $stmt->execute([$uid]);
                $userData = $stmt->fetch();
            }
        }
    }

    // =========================
    // CHANGE PASSWORD
    // =========================
    if ($action === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            $error = 'All password fields are required.';
        } elseif (!password_verify($currentPassword, $userData['password_hash'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($newPassword) < 8) {
            $error = 'New password must be at least 8 characters.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'New password and confirm password do not match.';
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            $updateStmt = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $updateStmt->execute([$hashedPassword, $uid]);

            $msg = 'Password updated successfully.';
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <h1>Settings</h1>
    <p>Manage your CineByte account settings.</p>
</section>

<section class="content-section settings-page">
    <div class="settings-card box">

        <?php if ($msg): ?>
            <p class="settings-message success"><?php echo e($msg); ?></p>
        <?php endif; ?>

        <?php if ($error): ?>
            <p class="settings-message error"><?php echo e($error); ?></p>
        <?php endif; ?>

        <form method="post" class="settings-form">
            <input type="hidden" name="action" value="change_username">

            <h2>Change Username</h2>

            <label for="username">New Username</label>
            <input 
                type="text" 
                id="username" 
                name="username" 
                placeholder="Enter new username"
                autocomplete="off"
                required
            >

            <button type="submit" class="btn">Save Username</button>
        </form>

        <div class="settings-divider"></div>

        <form method="post" class="settings-form">
            <input type="hidden" name="action" value="change_password">

            <h2>Change Password</h2>

            <label for="current_password">Current Password</label>
            <input 
                type="password" 
                id="current_password" 
                name="current_password" 
                required
            >

            <label for="new_password">New Password</label>
            <input 
                type="password" 
                id="new_password" 
                name="new_password" 
                required
            >

            <label for="confirm_password">Confirm New Password</label>
            <input 
                type="password" 
                id="confirm_password" 
                name="confirm_password" 
                required
            >

            <button type="submit" class="btn">Save Password</button>
        </form>

    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>