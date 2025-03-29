<?php
require_once __DIR__.'/../includes/config.php';
require_once __DIR__.'/../includes/functions.php';

// Check if already installed
if (file_exists(DB_PATH)) {
    header('Location: ../admin/login.php');
    exit;
}

// Handle installation form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create database
    $db = new SQLite3(DB_PATH);
    $schema = file_get_contents(DB_SCHEMA);
    $db->exec($schema);

    // Create admin user
    $admin_password = md5($_POST['admin_password']);
    $db->prepare("INSERT INTO users (username, email, password_md5, role) 
                 VALUES (?, ?, ?, 'admin')")
       ->execute([
           $_POST['admin_username'],
           $_POST['admin_email'],
           $admin_password
       ]);

    // Set basic settings
    $db->prepare("INSERT INTO settings (site_title, admin_email) VALUES (?, ?)")
       ->execute([
           $_POST['site_title'],
           $_POST['admin_email']
       ]);

    // Set admin permissions
    $admin_id = $db->lastInsertId();
    $db->exec("INSERT INTO report_permissions VALUES ($admin_id, 1, 1, 1)");

    header('Location: ../admin/login.php?installed=1');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install Google Ads Tracker</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="install-container">
        <div class="install-box">
            <h1>Google Ads Tracker Installation</h1>
            <form method="POST">
                <div class="form-group">
                    <label>Site Title</label>
                    <input type="text" name="site_title" required>
                </div>
                <div class="form-group">
                    <label>Admin Username</label>
                    <input type="text" name="admin_username" required>
                </div>
                <div class="form-group">
                    <label>Admin Email</label>
                    <input type="email" name="admin_email" required>
                </div>
                <div class="form-group">
                    <label>Admin Password</label>
                    <input type="password" name="admin_password" required>
                </div>
                <button type="submit" class="btn-primary">Install</button>
            </form>
        </div>
    </div>
</body>
</html>