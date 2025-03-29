<?php
require_once __DIR__.'/../includes/config.php';
require_once __DIR__.'/../includes/functions.php';

session_start();

// Check authentication and admin role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['add_user'])) {
            // Add new user
            $username = sanitizeInput($_POST['username']);
            $email = sanitizeInput($_POST['email']);
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $role = sanitizeInput($_POST['role']);
            
            $stmt = $db->prepare("INSERT INTO users (username, email, password_md5, role) VALUES (?, ?, ?, ?)");
            $stmt->bindValue(1, $username, SQLITE3_TEXT);
            $stmt->bindValue(2, $email, SQLITE3_TEXT);
            $stmt->bindValue(3, $password, SQLITE3_TEXT);
            $stmt->bindValue(4, $role, SQLITE3_TEXT);
            $stmt->execute();
            
            $_SESSION['success'] = "User added successfully";
        }
        elseif (isset($_POST['edit_user'])) {
            // Edit existing user
            $id = (int)$_POST['user_id'];
            $username = sanitizeInput($_POST['username']);
            $email = sanitizeInput($_POST['email']);
            $role = sanitizeInput($_POST['role']);
            
            $stmt = $db->prepare("UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?");
            $stmt->bindValue(1, $username, SQLITE3_TEXT);
            $stmt->bindValue(2, $email, SQLITE3_TEXT);
            $stmt->bindValue(3, $role, SQLITE3_TEXT);
            $stmt->bindValue(4, $id, SQLITE3_INTEGER);
            $stmt->execute();
            
            $_SESSION['success'] = "User updated successfully";
        }
        elseif (isset($_POST['delete_user'])) {
            // Delete user (prevent self-deletion)
            $id = (int)$_POST['user_id'];
            if ($id !== $_SESSION['user_id']) {
                $db->exec("DELETE FROM users WHERE id = $id");
                $_SESSION['success'] = "User deleted successfully";
            } else {
                $_SESSION['error'] = "You cannot delete your own account";
            }
        }
        elseif (isset($_POST['reset_password'])) {
            // Reset password
            $id = (int)$_POST['user_id'];
            $password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            
            $stmt = $db->prepare("UPDATE users SET password_md5 = ? WHERE id = ?");
            $stmt->bindValue(1, $password, SQLITE3_TEXT);
            $stmt->bindValue(2, $id, SQLITE3_INTEGER);
            $stmt->execute();
            
            $_SESSION['success'] = "Password reset successfully";
        }
        
        // Redirect to prevent form resubmission
        header("Location: users.php");
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}

// Get all users
$users = $db->query("SELECT * FROM users ORDER BY username");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Google Ads Tracker</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <h1><i class="fas fa-users"></i> User Management</h1>
        
        <!-- Display messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert success"><?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert error"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <!-- Add User Form -->
        <div class="card">
            <h2><i class="fas fa-user-plus"></i> Add New User</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Username:</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>Role:</label>
                    <select name="role" required>
                        <option value="admin">Admin</option>
                        <option value="report_viewer">Report Viewer</option>
                    </select>
                </div>
                <button type="submit" name="add_user" class="btn-primary">
                    <i class="fas fa-save"></i> Add User
                </button>
            </form>
        </div>
        
        <!-- User List -->
        <div class="card">
            <h2><i class="fas fa-list"></i> User List</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $users->fetchArray(SQLITE3_ASSOC)): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['role']) ?></td>
                        <td><?= $user['last_login'] ? date('Y-m-d H:i', strtotime($user['last_login'])) : 'Never' ?></td>
                        <td class="actions">
                            <!-- Edit Button -->
                            <button class="btn-edit" onclick="openEditModal(<?= $user['id'] ?>, '<?= htmlspecialchars($user['username']) ?>', '<?= htmlspecialchars($user['email']) ?>', '<?= htmlspecialchars($user['role']) ?>')">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            
                            <!-- Reset Password Button -->
                            <button class="btn-reset" onclick="openResetModal(<?= $user['id'] ?>, '<?= htmlspecialchars($user['username']) ?>')">
                                <i class="fas fa-key"></i> Reset Password
                            </button>
                            
                            <!-- Delete Button (disabled for current user) -->
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <button type="submit" name="delete_user" class="btn-delete" onclick="return confirm('Are you sure you want to delete this user?')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Edit User Modal -->
        <div id="editModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('editModal')">&times;</span>
                <h2><i class="fas fa-edit"></i> Edit User</h2>
                <form method="POST" id="editForm">
                    <input type="hidden" name="user_id" id="editUserId">
                    <input type="hidden" name="edit_user" value="1">
                    <div class="form-group">
                        <label>Username:</label>
                        <input type="text" name="username" id="editUsername" required>
                    </div>
                    <div class="form-group">
                        <label>Email:</label>
                        <input type="email" name="email" id="editEmail" required>
                    </div>
                    <div class="form-group">
                        <label>Role:</label>
                        <select name="role" id="editRole" required>
                            <option value="admin">Admin</option>
                            <option value="report_viewer">Report Viewer</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Reset Password Modal -->
        <div id="resetModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('resetModal')">&times;</span>
                <h2><i class="fas fa-key"></i> Reset Password for <span id="resetUsername"></span></h2>
                <form method="POST" id="resetForm">
                    <input type="hidden" name="user_id" id="resetUserId">
                    <input type="hidden" name="reset_password" value="1">
                    <div class="form-group">
                        <label>New Password:</label>
                        <input type="password" name="new_password" required>
                    </div>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Reset Password
                    </button>
                </form>
            </div>
        </div>
        
        <script>
            // Modal functions
            function openEditModal(id, username, email, role) {
                document.getElementById('editUserId').value = id;
                document.getElementById('editUsername').value = username;
                document.getElementById('editEmail').value = email;
                document.getElementById('editRole').value = role;
                document.getElementById('editModal').style.display = 'block';
            }
            
            function openResetModal(id, username) {
                document.getElementById('resetUserId').value = id;
                document.getElementById('resetUsername').textContent = username;
                document.getElementById('resetModal').style.display = 'block';
            }
            
            function closeModal(modalId) {
                document.getElementById(modalId).style.display = 'none';
            }
            
            // Close modal when clicking outside
            window.onclick = function(event) {
                if (event.target.className === 'modal') {
                    event.target.style.display = 'none';
                }
            }
        </script>
    </div>
</body>
</html>