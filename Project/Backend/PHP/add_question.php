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
    $admin_id = $_SESSION['user_id'];
    $question_text = trim($_POST['question']);
    $option_a = trim($_POST['option_a']);
    $option_b = trim($_POST['option_b']);
    $option_c = trim($_POST['option_c']);
    $option_d = trim($_POST['option_d']);
    $correct_answer = $_POST['correct_answer'];
    $category = $_POST['category'];
    $difficulty = $_POST['difficulty'];

    try {
        $stmt = $pdo->prepare("INSERT INTO questions (admin_id, question_text, option_a, option_b, option_c, option_d, correct_answer, category, difficulty) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$admin_id, $question_text, $option_a, $option_b, $option_c, $option_d, $correct_answer, $category, $difficulty]);
        
        header('Location: ../../Frontend/Pages/manage-questions.php?success=Question added successfully');
    } catch(PDOException $e) {
        header('Location: ../../Frontend/Pages/manage-questions.php?error=Failed to add question: ' . $e->getMessage());
    }
}
?> 