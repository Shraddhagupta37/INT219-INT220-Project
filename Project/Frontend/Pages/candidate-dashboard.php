<?php
require_once '../../Backend/PHP/auth.php';  // This will handle the session
require_once '../../Backend/PHP/config.php';
requireLogin();

// Ensure user is candidate
if ($_SESSION['user_role'] !== 'candidate') {
    header('Location: admin-dashboard.php');
    exit();
}

$userName = $_SESSION['user_name'];
$userUID = $_SESSION['user_uid'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/Frontend/src/tailwind.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <title>Candidate Dashboard - CodeLens</title>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white/70 backdrop-blur border-b border-amber-100 shadow-lg sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-8">
                    <a href="../index.php" class="text-2xl font-bold text-amber-600">CodeLens</a>
                    <a href="candidate-dashboard.php" class="text-gray-700 hover:text-amber-600 font-medium">Dashboard</a>
                    <a href="available-tests.php" class="text-gray-700 hover:text-amber-600">Available Tests</a>
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
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
    <!-- Hero Section -->
    <div class="bg-gradient-to-br from-amber-500 via-amber-600 to-amber-900 text-white rounded-2xl shadow-xl mb-8 p-8 flex flex-col md:flex-row items-center justify-between gap-8">
        <div>
            <h1 class="text-4xl md:text-5xl font-extrabold mb-2 drop-shadow-lg">Welcome, <?php echo htmlspecialchars($userName); ?>!</h1>
            <p class="text-lg md:text-xl opacity-90">Ready to take your next test and boost your skills?</p>
        </div>
        <div class="flex flex-col items-center">
            <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-base font-semibold mb-2">Candidate</span>
            <span class="text-white/70 text-xs">ID: <?php echo htmlspecialchars($userUID); ?></span>
        </div>
    </div>
        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
            <a href="my-results.php" class="block bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow focus:outline-none focus:ring-2 focus:ring-amber-500">
    <div class="text-gray-500 text-sm">Tests Completed</div>
    <div class="text-3xl font-bold text-gray-800"><?php
    // Tests Completed (all attempts)
    $userId = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM test_attempts ta JOIN tests t ON ta.test_id = t.id WHERE ta.user_id = ?");
    $stmt->execute([$userId]);
    $testsCompleted = $stmt->fetchColumn();
    echo $testsCompleted;
?></div>
    <div class="text-amber-500 text-sm font-bold">View Results →</div>
</a>
            <a href="my-results.php#performance" class="block bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow focus:outline-none focus:ring-2 focus:ring-amber-500">
    <div class="text-gray-500 text-sm">Average Score</div>
    <div class="text-3xl font-bold text-gray-800"><?php
    // Average Score (all attempts)
    $stmt = $pdo->prepare("SELECT AVG(ta.score) FROM test_attempts ta JOIN tests t ON ta.test_id = t.id WHERE ta.user_id = ?");
    $stmt->execute([$userId]);
    $avg = $stmt->fetchColumn();
    echo $avg !== null ? round($avg, 2) . '%' : '0%';
?></div>
    <div class="text-amber-500 text-sm font-bold">Your Performance</div>
</a>
            <a href="available-tests.php" class="block bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow focus:outline-none focus:ring-2 focus:ring-amber-500">
    <div class="text-gray-500 text-sm">Available Tests</div>
    <div class="text-3xl font-bold text-gray-800"><?php
    // Available Tests
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tests t WHERE t.active = 1 AND t.id NOT IN (SELECT test_id FROM test_attempts WHERE user_id = ?)");
    $stmt->execute([$_SESSION['user_id']]);
    echo $stmt->fetchColumn();
?></div>
    <div class="text-amber-500 text-sm font-bold">Take Test →</div>
</a>
        </div>

        <!-- Performance Graph Section -->
        <div class="bg-white/80 rounded-2xl shadow-xl mb-8">
            <div class="p-8">
                <h2 class="text-2xl font-bold mb-6">Your Performance</h2>
                <?php
// Fetch last 5 test attempts for the candidate
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT t.title, ta.score, ta.completed_at FROM test_attempts ta JOIN tests t ON ta.test_id = t.id WHERE ta.user_id = ? ORDER BY ta.completed_at DESC LIMIT 5");
$stmt->execute([$userId]);
$attempts = $stmt->fetchAll();
$labels = [];
$scores = [];
foreach (array_reverse($attempts) as $attempt) {
    $labels[] = htmlspecialchars($attempt['title']);
    $scores[] = round($attempt['score'], 2);
}
if (count($attempts) === 0) {
    echo '<div class="flex flex-col items-center justify-center py-10 text-gray-400">';
    echo '<svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2m8 0v2a4 4 0 004 4h2a4 4 0 004-4v-2m-8 0H5m8 0h2" /></svg>';
    echo '<div class="text-lg font-semibold mb-2">No Test Attempts Yet</div>';
    echo '<div class="text-sm">Take some tests to see your performance graph!</div>';
    echo '</div>';
} else {
    echo '<canvas id="performanceChart" height="120"></canvas>';
}
?>
<?php if (count($attempts) > 0): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('performanceChart').getContext('2d');
    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: <?php echo json_encode($labels); ?>,
                            datasets: [{
                                label: 'Score (%)',
                                data: <?php echo json_encode($scores); ?>,
                                fill: true,
                                backgroundColor: 'rgba(251,191,36,0.15)',
                                borderColor: 'rgba(251,191,36,1)',
                                pointBackgroundColor: 'rgba(251,191,36,1)',
                                pointBorderColor: '#fff',
                                tension: 0.4
                            }]
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    max: 100,
                                    ticks: { color: '#374151', font: { weight: 'bold' } }
                                },
                                x: {
                                    ticks: { color: '#374151', font: { weight: 'bold' } }
                                }
                            },
                            plugins: {
                                legend: { display: false },
                                tooltip: { enabled: true }
                            }
                        }
                    });
                });
                </script>
            <?php endif; ?>
            </div>
        </div>
        <!-- Available Tests Section -->
        <div class="bg-white/80 rounded-2xl shadow-xl mb-8">
            <div class="p-8">
                <h2 class="text-2xl font-bold mb-6">Available Tests</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php
                    // Fetch active tests not attempted by the candidate
                    $userId = $_SESSION['user_id'];
                    $stmt = $pdo->prepare("SELECT t.id, t.title, t.duration, COUNT(tq.id) as question_count
                        FROM tests t
                        LEFT JOIN test_questions tq ON t.id = tq.test_id
                        WHERE t.active = 1 AND t.id NOT IN (
                            SELECT test_id FROM test_attempts WHERE user_id = ?
                        )
                        GROUP BY t.id
                        ORDER BY t.created_at DESC");
                    $stmt->execute([$userId]);
                    $availableTests = $stmt->fetchAll();
                    if (empty($availableTests)) {
                        echo '<p class="text-gray-500 text-center py-6 w-full">No available tests at the moment.</p>';
                    } else {
                        foreach ($availableTests as $test) {
                            echo '<div class="border rounded-lg p-6 hover:shadow-md transition-shadow">';
                            echo '<h3 class="text-lg font-semibold mb-2">' . htmlspecialchars($test['title']) . '</h3>';
                            echo '<div class="text-sm text-gray-500 mb-4">';
                            echo '<p>Duration: ' . htmlspecialchars($test['duration']) . ' minutes</p>';
                            echo '<p>Questions: ' . htmlspecialchars($test['question_count']) . '</p>';
                            echo '<p>Status: Not Started</p>';
                            echo '</div>';
                            echo '<a href="take-test.php?test_id=' . htmlspecialchars($test['id']) . '" class="inline-block bg-amber-600 text-white px-4 py-2 rounded-md hover:bg-amber-700">Start Test</a>';
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
            </div>
        </div>

    </div>


</body>
</html>