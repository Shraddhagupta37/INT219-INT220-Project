<?php
require_once '../../Backend/PHP/auth.php';
require_once '../../Backend/PHP/config.php';
requireLogin();

// Ensure user is candidate
if ($_SESSION['user_role'] !== 'candidate') {
    header('Location: admin-dashboard.php');
    exit();
}

$userName = $_SESSION['user_name'];
$userUID = $_SESSION['user_uid'];
$userId = $_SESSION['user_id'];

// Check if attempt_id is provided
if (!isset($_GET['attempt_id']) || !is_numeric($_GET['attempt_id'])) {
    header('Location: my-results.php');
    exit();
}

$attemptId = $_GET['attempt_id'];

// Fetch test attempt details
try {
    $stmt = $pdo->prepare("
        SELECT ta.*, t.title, t.description, t.passing_score, t.duration
        FROM test_attempts ta
        JOIN tests t ON ta.test_id = t.id
        WHERE ta.id = ? AND ta.user_id = ?
    ");
    $stmt->execute([$attemptId, $userId]);
    $attempt = $stmt->fetch();
    
    if (!$attempt) {
        header('Location: my-results.php');
        exit();
    }
    
    // Fetch questions and answers for this attempt
    $stmt = $pdo->prepare("
        SELECT q.id, q.question_text, q.option_a, q.option_b, q.option_c, q.option_d, 
               q.correct_option, ua.selected_option
        FROM questions q
        JOIN user_answers ua ON q.id = ua.question_id
        WHERE ua.attempt_id = ?
        ORDER BY q.id ASC
    ");
    $stmt->execute([$attemptId]);
    $questions = $stmt->fetchAll();
    if (!$questions) {
        $questions = [];
    }
} catch (PDOException $e) {
    $error = "Error fetching test details: " . $e->getMessage();
    $questions = [];
}

// Calculate test statistics
$correctAnswers = 0;
$wrongAnswers = 0;
$skippedAnswers = 0;

foreach ($questions as $question) {
    if (empty($question['selected_option'])) {
        $skippedAnswers++;
    } else if (strtoupper($question['selected_option']) === strtoupper($question['correct_option'])) {
        $correctAnswers++;
    } else {
        $wrongAnswers++;
    }
}

$totalQuestions = count($questions);
$score = $attempt['score'];
$passed = $score >= $attempt['passing_score'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/Frontend/src/tailwind.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <title>Test Result - CodeLens</title>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-8">
                    <a href="../index.php" class="text-2xl font-bold text-amber-600">CodeLens</a>
                    <a href="candidate-dashboard.php" class="text-gray-700 hover:text-amber-600">Dashboard</a>
                    <a href="available-tests.php" class="text-gray-700 hover:text-amber-600">Available Tests</a>
                    <a href="my-results.php" class="text-gray-700 hover:text-amber-600">My Results</a>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700">Welcome, <?php echo htmlspecialchars($userName); ?></span>
                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-sm">Candidate</span>
                    <a href="/Backend/PHP/logout.php" class="bg-amber-600 text-white px-4 py-2 rounded-md hover:bg-amber-700">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <?php if(isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php else: ?>
            <div class="mb-6">
                <a href="my-results.php" class="inline-flex items-center text-amber-600 hover:text-amber-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    Back to My Results
                </a>
            </div>
            
            <!-- Test Summary -->
            <div class="bg-white rounded-lg shadow mb-8 overflow-hidden">
                <div class="border-b border-gray-200">
                    <div class="p-6">
                        <h1 class="text-2xl font-bold text-gray-900"><?php echo htmlspecialchars($attempt['title']); ?></h1>
                        <p class="mt-2 text-gray-600"><?php echo htmlspecialchars($attempt['description']); ?></p>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Violation Counts</h3>
<p class="mt-1 text-lg font-semibold">
    <?php 
        $fullscreen_violations = isset($attempt['fullscreen_violations']) ? $attempt['fullscreen_violations'] : 0;
        $tab_violations = isset($attempt['tab_violations']) ? $attempt['tab_violations'] : 0;
        echo "Fullscreen: <span class='text-red-600'>" . $fullscreen_violations . "</span><br>Tab & Window: <span class='text-red-600'>" . $tab_violations . "</span>";
    ?>
</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Date Completed</h3>
                            <p class="mt-1 text-lg font-semibold"><?php echo date('M d, Y - H:i', strtotime($attempt['completed_at'])); ?></p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Time Taken</h3>
<p class="mt-1 text-lg font-semibold">
    <?php 
        $timeTaken = isset($attempt['time_taken']) ? intval($attempt['time_taken']) : 0;
        // Calculate total duration in seconds
        // Always use the test's original duration as source of truth for 'out of' time
        $totalDuration = 0;
        if (!empty($attempt['t.duration']) && intval($attempt['t.duration']) > 0) {
            $totalDuration = intval($attempt['t.duration']) * 60; // from tests (minutes)
        } elseif (!empty($attempt['duration']) && intval($attempt['duration']) > 0) {
            $totalDuration = intval($attempt['duration']); // fallback to test_attempts (seconds)
        }
        // Cap timeTaken at totalDuration for display
        if ($totalDuration > 0 && $timeTaken > $totalDuration) {
            $timeTaken = $totalDuration;
        }
        $minutes = floor($timeTaken / 60);
        $seconds = $timeTaken % 60;
        $totalMinutes = ($totalDuration > 0) ? floor($totalDuration / 60) : '--';
        $totalSeconds = ($totalDuration > 0) ? ($totalDuration % 60) : '';
        echo $minutes . "m " . $seconds . "s";
    ?>
</p>
                        </div>
                        
                        <div>
    <h3 class="text-sm font-medium text-gray-500">Score</h3>
    <p class="mt-1 text-lg font-semibold">
        <?php echo $score; ?>%
        <span class="text-sm text-gray-500">
            (<?php echo isset($attempt['correct_answers']) ? $attempt['correct_answers'] : 0; ?>/<?php echo $attempt['total_questions']; ?>)
        </span>
    </p>
</div>
<div>
    <h3 class="text-sm font-medium text-gray-500">Skipped</h3>
    <p class="mt-1 text-lg font-semibold">
        <?php 
            $skippedAnswers = 0;
            foreach ($questions as $question) {
                if (empty($question['selected_option'])) {
                    $skippedAnswers++;
                }
            }
            echo $skippedAnswers;
        ?>
        <span class="text-sm text-gray-500">question(s)</span>
    </p>
</div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Result</h3>
                            <p class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium <?php echo $passed ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                    <?php echo $passed ? 'PASSED' : 'FAILED'; ?>
                                </span>
                                <span class="text-sm text-gray-500 ml-2">
                                    (Passing: <?php echo $attempt['passing_score']; ?>%)
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
                

            </div>
            
            <!-- Detailed Questions and Answers -->
            <div class="bg-white rounded-lg shadow mb-8">
                <div class="p-6">
                    <h2 class="text-xl font-bold mb-6">Questions & Answers</h2>
                    
                    <?php foreach($questions as $index => $question): ?>
                    <div class="mb-8 pb-6 <?php echo $index < count($questions) - 1 ? 'border-b border-gray-200' : ''; ?>">
                        <div class="flex items-start mb-4">
                            <div class="bg-gray-100 rounded-full flex items-center justify-center h-8 w-8 flex-shrink-0 mr-3">
                                <span class="text-gray-700 font-medium"><?php echo $index + 1; ?></span>
                            </div>
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2"><?php echo htmlspecialchars($question['question_text']); ?></h3>
                                
                                <!-- Options -->
                                <div class="space-y-2 mb-4">
                                    <?php $options = ['a', 'b', 'c', 'd']; ?>
                                    <?php foreach($options as $option): ?>
                                        <?php 
                                            $isSelected = strtoupper($question['selected_option']) === strtoupper($option);
                                            $isCorrect = strtoupper($question['correct_option']) === strtoupper($option);
                                            
                                            $bgClass = '';
                                            if ($isSelected && $isCorrect) {
                                                $bgClass = 'bg-green-50 border-green-200 text-green-800';
                                            } else if ($isSelected && !$isCorrect) {
                                                $bgClass = 'bg-red-50 border-red-200 text-red-800';
                                            } else if ($isCorrect) {
                                                $bgClass = 'bg-green-50 border-green-200 text-green-800';
                                            } else {
                                                $bgClass = 'bg-gray-50 border-gray-200 text-gray-800';
                                            }
                                        ?>
                                        <div class="flex items-center p-3 rounded-md border <?php echo $bgClass; ?>">
                                            <div class="flex-shrink-0 h-5 w-5 mr-2">
                                                <?php if ($isSelected && $isCorrect): ?>
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                    </svg>
                                                <?php elseif ($isSelected && !$isCorrect): ?>
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                                    </svg>
                                                <?php elseif ($isCorrect): ?>
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                    </svg>
                                                <?php else: ?>
                                                    <div class="h-5 w-5 rounded-full border border-gray-300 flex items-center justify-center">
                                                        <span class="text-xs font-medium uppercase"><?php echo $option; ?></span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <span><?php echo htmlspecialchars($question['option_' . $option]); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <!-- Result -->
                                <div class="text-sm">
                                    <?php if (empty($question['selected_option'])): ?>
                                        <div class="text-gray-500">
                                            <span class="font-medium">Not answered.</span> 
                                            The correct answer was option <?php echo strtoupper($question['correct_option']); ?> (<?php echo htmlspecialchars($question['option_' . strtolower($question['correct_option'])]); ?>).
                                        </div>
                                    <?php elseif (strtoupper($question['selected_option']) === strtoupper($question['correct_option'])): ?>
                                        <div class="text-green-600 font-medium">Correct answer!</div>
                                    <?php else: ?>
                                        <div class="text-red-600 font-medium">
                                            Incorrect. You selected option <?php echo strtoupper($question['selected_option']); ?>, but the correct answer was option <?php echo strtoupper($question['correct_option']); ?> (<?php echo htmlspecialchars($question['option_' . strtolower($question['correct_option'])]); ?>).
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="flex justify-between mb-8">
                <a href="my-results.php" class="bg-white text-amber-600 border border-amber-600 px-4 py-2 rounded-md hover:bg-amber-50">
                    Back to My Results
                </a>
                
                <a href="take-test.php?test_id=<?php echo $attempt['test_id']; ?>" class="bg-amber-600 text-white px-4 py-2 rounded-md hover:bg-amber-700">
                    Retake Test
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html> 