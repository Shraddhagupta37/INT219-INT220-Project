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

// Fetch all available active tests
try {
    $stmt = $pdo->prepare("
        SELECT t.*, COUNT(tq.id) as question_count, 
        (SELECT COUNT(*) FROM test_attempts WHERE test_id = t.id AND user_id = ?) as attempts
        FROM tests t
        LEFT JOIN test_questions tq ON t.id = tq.test_id
        WHERE t.active = 1
        GROUP BY t.id
        ORDER BY t.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $tests = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching tests: " . $e->getMessage();
    $tests = [];
}

// Fetch user's test attempts
try {
    $stmt = $pdo->prepare("
        SELECT ta.*, t.title, t.passing_score
        FROM test_attempts ta
        JOIN tests t ON ta.test_id = t.id
        WHERE ta.user_id = ?
        ORDER BY ta.completed_at DESC
        LIMIT 5
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $recent_attempts = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching attempts: " . $e->getMessage();
    $recent_attempts = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/Frontend/src/tailwind.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <title>Available Tests - CodeLens</title>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-8">
                    <a href="../index.php" class="text-2xl font-bold text-amber-600">CodeLens</a>
                    <a href="candidate-dashboard.php" class="text-gray-700 hover:text-amber-600">Dashboard</a>
                    <a href="available-tests.php" class="text-gray-700 hover:text-amber-600 font-medium">Available Tests</a>
                    <a href="my-results.php" class="text-gray-700 hover:text-amber-600">My Results</a>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700">Welcome, <?php echo htmlspecialchars($userName); ?></span>
                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-sm">Candidate</span>
                    <a href="../../Backend/PHP/logout.php" class="bg-amber-600 text-white px-4 py-2 rounded-md hover:bg-amber-700">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        
        
        <!-- Available Tests -->
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="p-6">
                <h2 class="text-2xl font-bold mb-6">Available Tests</h2>
                
                <?php if(empty($tests)): ?>
                    <p class="text-gray-500 text-center py-6">No tests are currently available.</p>
                <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php foreach($tests as $test): ?>
                    <div class="rounded-2xl p-6 bg-white/90 shadow-xl hover:shadow-2xl transition-all duration-200 border-0 outline-none">
                        <h3 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($test['title']); ?></h3>
                        <?php if (!empty($test['description'])): ?>
                            <p class="text-gray-600 mb-4"><?php echo htmlspecialchars(substr($test['description'], 0, 100) . (strlen($test['description']) > 100 ? '...' : '')); ?></p>
                        <?php endif; ?>
                        <div class="grid grid-cols-2 gap-2 mb-4">
                            <div class="text-sm text-gray-600">
                                <span class="font-semibold">Duration:</span> <?php echo $test['duration']; ?> minutes
                            </div>
                            <div class="text-sm text-gray-600">
                                <span class="font-semibold">Questions:</span> <?php echo $test['question_count']; ?>
                            </div>
                            <div class="text-sm text-gray-600">
                                <span class="font-semibold">Passing Score:</span> <?php echo $test['passing_score']; ?>%
                            </div>
                            <div class="text-sm text-gray-600">
                                <span class="font-semibold">Attempts:</span> <?php echo $test['attempts']; ?>
                            </div>
                        </div>
                        <?php if($test['question_count'] > 0): ?>
                            <a href="take-test.php?test_id=<?php echo $test['id']; ?>" 
                               class="inline-block bg-amber-600 text-white px-4 py-2 rounded-md hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 start-test-btn"
                               data-test-url="take-test.php?test_id=<?php echo $test['id']; ?>">
                                Start Test
                            </a>
                        <?php else: ?>
                            <span class="inline-block bg-gray-300 text-gray-600 px-4 py-2 rounded-md cursor-not-allowed">
                                No Questions Available
                            </span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Recent Attempts -->
        <?php if(!empty($recent_attempts)): ?>
        <div class="bg-white rounded-lg shadow">
            <div class="p-6">
                <h2 class="text-2xl font-bold mb-6">Your Recent Test Attempts</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Test Name</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Result</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach($recent_attempts as $attempt): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($attempt['title']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <?php echo isset($attempt['score']) ? $attempt['score'] . '%' : 'N/A'; ?><?php
    if (isset($attempt['correct_answers']) && isset($attempt['total_questions'])) {
        echo ' (' . $attempt['correct_answers'] . '/' . $attempt['total_questions'] . ')';
    }
?>
                                    </div>
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
                                        View Results
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4 text-right">
                    <a href="my-results.php" class="text-amber-600 hover:text-amber-800">
                        View All Results →
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
<script>
// Fullscreen and anti-tab-switch logic for Start Test
function launchFullscreenAndNavigate(url) {
    // Show alert
    if (!confirm('Once you start the test, it will run in fullscreen mode.\n\n- Do NOT exit fullscreen mode.\n- Do NOT switch tabs or windows.\n- Exit only using the provided button.\n\nAre you sure you want to start?')) {
        return;
    }
    // Go fullscreen
    const docElm = document.documentElement;
    if (docElm.requestFullscreen) {
        docElm.requestFullscreen();
    } else if (docElm.mozRequestFullScreen) { /* Firefox */
        docElm.mozRequestFullScreen();
    } else if (docElm.webkitRequestFullscreen) { /* Chrome, Safari & Opera */
        docElm.webkitRequestFullscreen();
    } else if (docElm.msRequestFullscreen) { /* IE/Edge */
        docElm.msRequestFullscreen();
    }
    // After a short delay (to allow fullscreen), navigate
    setTimeout(function() {
        window.location.href = url;
    }, 300);
}
// Attach to all Start Test buttons
window.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.start-test-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            launchFullscreenAndNavigate(this.getAttribute('data-test-url'));
        });
    });
});
</script>
</body>
</html> 