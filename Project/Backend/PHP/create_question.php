<?php
session_start();
require_once 'config.php';
require_once 'auth.php';

// Ensure user is admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../../Frontend/Pages/dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question_text = $_POST['question_text'];
    $option_a = $_POST['option_a'];
    $option_b = $_POST['option_b'];
    $option_c = $_POST['option_c'];
    $option_d = $_POST['option_d'];
    $correct_answer = $_POST['correct_answer'];
    $admin_id = $_SESSION['user_id'];

    try {
        $stmt = $pdo->prepare("INSERT INTO questions (admin_id, question_text, option_a, option_b, option_c, option_d, correct_answer) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$admin_id, $question_text, $option_a, $option_b, $option_c, $option_d, $correct_answer]);
        
        header('Location: ../../Frontend/Pages/dashboard.php?success=Question created successfully');
    } catch(PDOException $e) {
        header('Location: ../../Frontend/Pages/dashboard.php?error=Failed to create question');
    }
}
?> 