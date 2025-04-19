<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $uid = trim($_POST['uid']);
    $country_code = trim($_POST['country_code']);
    $phone = trim($_POST['phone']);
    $full_phone = $country_code . $phone; // Store as international format
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];

    // Validation
    if (empty($fullname) || empty($email) || empty($uid) || empty($country_code) || empty($phone) || empty($password)) {
        redirectWith('../../Frontend/Pages/register.php', 'All fields are required');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirectWith('../../Frontend/Pages/register.php', 'Invalid email format');
    }

    // Validate phone: allow 5-15 digits (international)
    if (!preg_match("/^[0-9]{5,15}$/", $phone)) {
        redirectWith('/Frontend/Pages/register.php', 'Invalid phone number');
    }

    if (strlen($password) < 6) {
        redirectWith('../../Frontend/Pages/register.php', 'Password must be at least 6 characters');
    }

    if ($password !== $confirm_password) {
        redirectWith('../../Frontend/Pages/register.php', 'Passwords do not match');
    }

    try {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR uid = ?");
        $stmt->execute([$email, $uid]);
        
        if ($stmt->rowCount() > 0) {
            redirectWith('../../Frontend/Pages/register.php', 'Email or Registration Number already exists');
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert user
        $stmt = $pdo->prepare("INSERT INTO users (fullname, uid, email, phone, password, role) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$fullname, $uid, $email, $full_phone, $hashed_password, $role]);

        redirectWith('../../Frontend/Pages/login.php', 'Registration successful! Please login.', 'success');

    } catch(PDOException $e) {
        redirectWith('../../Frontend/Pages/register.php', 'Registration failed: ' . $e->getMessage());
    }
}
?>
