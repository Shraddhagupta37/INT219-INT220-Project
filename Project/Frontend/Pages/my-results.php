<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
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

// Fetch all unique completed test attempts by the user
try {
    $stmt = $pdo->prepare("
        SELECT ta.*, t.title, t.description, t.passing_score
        FROM test_attempts ta
        JOIN tests t ON ta.test_id = t.id
        WHERE ta.user_id = ?
        ORDER BY ta.completed_at DESC
    ");
    $stmt->execute([$userId]);
    $attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Failed to fetch test results.";
    $attempts = [];
}

// Calculate statistics
$totalTests = count($attempts);
$passedTests = 0;
$totalScore = 0;

foreach ($attempts as $attempt) {
    if ($attempt['score'] >= $attempt['passing_score']) {
        $passedTests++;
    }
    $totalScore += $attempt['score'];
}

$avgScore = $totalTests > 0 ? round($totalScore / $totalTests, 1) : 0;
$passRate = $totalTests > 0 ? round(($passedTests / $totalTests) * 100, 1) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/Frontend/src/tailwind.css" rel="stylesheet">
    <title>My Test Results - CodeLens</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-8">
                    <a href="/Frontend/index.php" class="text-2xl font-bold text-amber-600">CodeLens</a>
                    <a href="candidate-dashboard.php" class="text-gray-700 hover:text-amber-600">Dashboard</a>
                    <a href="available-tests.php" class="text-gray-700 hover:text-amber-600">Available Tests</a>
                    <a href="my-results.php" class="text-gray-700 hover:text-amber-600 font-medium">My Results</a>
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
        <?php endif; ?>
        
        <!-- Performance Overview -->
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="p-6">
                <h2 class="text-2xl font-bold mb-6">Your Performance Overview</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-amber-50 p-4 rounded-lg border border-amber-200">
                        <div class="text-sm text-amber-700 font-medium">Total Tests</div>
                        <div class="text-3xl font-bold text-amber-900"><?php echo $totalTests; ?></div>
                    </div>
                    
                    <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                        <div class="text-sm text-green-700 font-medium">Passed</div>
                        <div class="text-3xl font-bold text-green-900"><?php echo $passedTests; ?></div>
                    </div>
                    
                    <div class="bg-red-50 p-4 rounded-lg border border-red-200">
                        <div class="text-sm text-red-700 font-medium">Failed</div>
                        <div class="text-3xl font-bold text-red-900"><?php echo $totalTests - $passedTests; ?></div>
                    </div>
                    
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <div class="text-sm text-gray-700 font-medium">Average Score</div>
                        <div class="text-3xl font-bold text-gray-900"><?php echo $avgScore; ?>%</div>
                    </div>
                </div>
                
                <?php if($totalTests > 0): ?>
                <div class="w-full bg-gray-200 rounded-full h-4 mb-6">
                    <div class="bg-amber-600 h-4 rounded-full" style="width: <?php echo $passRate; ?>%"></div>
                </div>
                
                <div class="text-sm text-gray-600 text-center">
                    <span class="font-bold">Pass Rate: <?php echo $passRate; ?>%</span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Test Results -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6">
                <h2 class="text-2xl font-bold mb-6">All Test Results</h2>
                
                <?php if(empty($attempts)): ?>
                    <p class="text-gray-500 text-center py-6">You haven't taken any tests yet.</p>
                    <div class="text-center mt-4">
                        <a href="available-tests.php" class="inline-block bg-amber-600 text-white px-4 py-2 rounded-md hover:bg-amber-700">
                            Browse Available Tests
                        </a>
                    </div>
                <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Test Name</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time Taken</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Completed</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Result</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach($attempts as $attempt): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($attempt['title']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <?php echo $attempt['score']; ?>% (<?php echo isset($attempt['correct_answers']) ? $attempt['correct_answers'] : 0; ?>/<?php echo $attempt['total_questions']; ?>)
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php 
                                        $timeTaken = isset($attempt['time_taken']) ? $attempt['time_taken'] : 0;
                                        $minutes = floor($timeTaken / 60);
                                        $seconds = $timeTaken % 60;
                                        echo $minutes . "m " . $seconds . "s";
                                    ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('M d, Y - H:i', strtotime($attempt['completed_at'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $attempt['score'] >= $attempt['passing_score'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                        <?php echo $attempt['score'] >= $attempt['passing_score'] ? 'PASSED' : 'FAILED'; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <a href="test-result.php?attempt_id=<?php echo $attempt['id']; ?>" class="text-amber-600 hover:text-amber-800">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html> 