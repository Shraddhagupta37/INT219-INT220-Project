<?php
session_start();
require_once 'config.php';
require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $test_id = $_POST['test_id'];
    $answers = $_POST['answers']; // Array of answers
    $time_taken = $_POST['time_taken'];

    try {
        // Start transaction
        $pdo->beginTransaction();

        // Get correct answers
        $stmt = $pdo->prepare("SELECT id, correct_answer FROM questions WHERE test_id = ?");
        $stmt->execute([$test_id]);
        $questions = $stmt->fetchAll();

        // Calculate score
        $total_questions = count($questions);
        $correct_answers = 0;
        foreach ($questions as $question) {
            if (isset($answers[$question['id']]) && $answers[$question['id']] === $question['correct_answer']) {
                $correct_answers++;
            }
        }
        $score_percentage = ($correct_answers / $total_questions) * 100;

        // Save test attempt
        $stmt = $pdo->prepare("INSERT INTO test_attempts (user_id, test_id, score, time_taken, total_questions, correct_answers) 
                              VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $test_id, $score_percentage, $time_taken, $total_questions, $correct_answers]);
        $attempt_id = $pdo->lastInsertId();

        // Save individual answers
        $stmt = $pdo->prepare("INSERT INTO test_answers (attempt_id, question_id, selected_answer) VALUES (?, ?, ?)");
        foreach ($answers as $question_id => $answer) {
            $stmt->execute([$attempt_id, $question_id, $answer]);
        }

        $pdo->commit();
        header('Location: ../../Frontend/Pages/test-result.php?attempt_id=' . $attempt_id);

    } catch(PDOException $e) {
        $pdo->rollBack();
        header('Location: ../../Frontend/Pages/candidate-dashboard.php?error=Test submission failed');
    }
}
?> 