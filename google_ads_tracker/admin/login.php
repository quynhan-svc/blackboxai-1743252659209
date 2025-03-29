<?php
require_once __DIR__.'/../includes/config.php';
require_once __DIR__.'/../includes/functions.php';

session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = md5($_POST['password']);

    $stmt = $db->prepare("SELECT * FROM users WHERE username = ? AND password_md5 = ?");
    $stmt->bindValue(1, $username, SQLITE3_TEXT);
    $stmt->bindValue(2, $password, SQLITE3_TEXT);
    $result = $stmt->execute();
    $user = $result->fetchArray(SQLITE3_ASSOC);

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['last_activity'] = time();

        // Update last login
        $db->exec("UPDATE users SET last_login = datetime('now') WHERE id = {$user['id']}");

        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Invalid username or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Google Ads Tracker</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="cpanel-login">
        <div class="login-brand">
            <img src="../assets/images/logo.png" alt="Logo" class="login-logo">
            <h1>Google Ads Tracker</h1>
        </div>
        
        <div class="login-box">
            <div class="login-header">
                <h2><i class="fas fa-sign-in-alt"></i> Account Login</h2>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST" class="login-form">
                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" placeholder="Username" name="username" required>
                </div>
                
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" placeholder="Password" name="password" required>
                </div>
                
                <button type="submit" class="btn-login">Login</button>
                
                <div class="login-footer">
                    <p>© 2025 Google Ads Tracker</p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>