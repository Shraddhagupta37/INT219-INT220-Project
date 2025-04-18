<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier']);
    $password = $_POST['password'];

    // Validation
    if (empty($identifier) || empty($password)) {
        redirectWith('../../Frontend/Pages/login.php', 'All fields are required');
    }

    try {
        // Check if identifier is email or UID
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR uid = ?");
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['fullname'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_uid'] = $user['uid'];
            
            // If "Remember me" is checked
            if (isset($_POST['remember']) && $_POST['remember'] == 'on') {
                $token = bin2hex(random_bytes(32));
                setcookie('remember_token', $token, time() + (86400 * 30), "/"); // 30 days
                
                // Store token in database (you'll need to add a remember_token column)
                $stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                $stmt->execute([$token, $user['id']]);
            }

            // Redirect based on role
            if ($user['role'] === 'admin') {
                redirectWith('../../Frontend/Pages/admin-dashboard.php', 'Login successful!', 'success');
            } else {
                redirectWith('../../Frontend/Pages/candidate-dashboard.php', 'Login successful!', 'success');
            }
        } else {
            redirectWith('../../Frontend/Pages/login.php', 'Invalid credentials');
        }
    } catch(PDOException $e) {
        redirectWith('../../Frontend/Pages/login.php', 'Login failed: ' . $e->getMessage());
    }
}
?>
