<?php
require_once __DIR__.'/../includes/config.php';
require_once __DIR__.'/../includes/functions.php';

session_start();

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $db->prepare("UPDATE settings SET 
        mail_host = :host,
        mail_port = :port,
        mail_encryption = :encryption,
        mail_username = :username,
        mail_password = :password,
        site_title = :title,
        admin_email = :email,
        alert_threshold = :threshold
    ");
    
    $stmt->bindValue(':host', $_POST['mail_host'], SQLITE3_TEXT);
    $stmt->bindValue(':port', $_POST['mail_port'], SQLITE3_INTEGER);
    $stmt->bindValue(':encryption', $_POST['mail_encryption'], SQLITE3_TEXT);
    $stmt->bindValue(':username', $_POST['mail_username'], SQLITE3_TEXT);
    $stmt->bindValue(':password', $_POST['mail_password'], SQLITE3_TEXT);
    $stmt->bindValue(':title', $_POST['site_title'], SQLITE3_TEXT);
    $stmt->bindValue(':email', $_POST['admin_email'], SQLITE3_TEXT);
    $stmt->bindValue(':threshold', $_POST['alert_threshold'], SQLITE3_INTEGER);
    
    $stmt->execute();
    
    header('Location: settings.php?success=1');
    exit;
}

// Get current settings
$settings = $db->querySingle("SELECT * FROM settings", true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Google Ads Tracker</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <h1>System Settings</h1>
        
        <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">Settings updated successfully!</div>
        <?php endif; ?>

        <form method="POST" class="settings-form">
            <h2>General Settings</h2>
            <div class="form-group">
                <label>Site Title</label>
                <input type="text" name="site_title" value="<?= htmlspecialchars($settings['site_title'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Admin Email</label>
                <input type="email" name="admin_email" value="<?= htmlspecialchars($settings['admin_email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Alert Threshold (clicks/hour from same IP)</label>
                <input type="number" name="alert_threshold" value="<?= htmlspecialchars($settings['alert_threshold'] ?? 5) ?>" min="1" required>
            </div>

            <h2>Email Settings</h2>
            <div class="form-group">
                <label>SMTP Host</label>
                <input type="text" name="mail_host" value="<?= htmlspecialchars($settings['mail_host'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>SMTP Port</label>
                <input type="number" name="mail_port" value="<?= htmlspecialchars($settings['mail_port'] ?? 587) ?>">
            </div>
            <div class="form-group">
                <label>Encryption</label>
                <select name="mail_encryption">
                    <option value="tls" <?= ($settings['mail_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS</option>
                    <option value="ssl" <?= ($settings['mail_encryption'] ?? 'tls') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                </select>
            </div>
            <div class="form-group">
                <label>SMTP Username</label>
                <input type="text" name="mail_username" value="<?= htmlspecialchars($settings['mail_username'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>SMTP Password</label>
                <input type="password" name="mail_password" value="<?= htmlspecialchars($settings['mail_password'] ?? '') ?>">
            </div>

            <button type="submit" class="btn-primary">Save Settings</button>
        </form>
    </div>
</body>
</html>