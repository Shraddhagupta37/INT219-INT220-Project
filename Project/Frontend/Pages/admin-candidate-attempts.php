<?php
require_once '../../Backend/PHP/auth.php';
require_once '../../Backend/PHP/config.php';
requireLogin();

// Ensure user is admin
if ($_SESSION['user_role'] !== 'admin') {
    header('Location: candidate-dashboard.php');
    exit();
}

$candidateId = isset($_GET['candidate_id']) ? intval($_GET['candidate_id']) : 0;
if (!$candidateId) {
    echo '<div class="p-6 text-red-500">Invalid candidate ID.</div>';
    exit();
}

// Handle delete attempt
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_attempt_id'])) {
    $attemptId = intval($_POST['delete_attempt_id']);
    $stmt = $pdo->prepare("DELETE FROM test_attempts WHERE id = ? AND user_id = ?");
    $stmt->execute([$attemptId, $candidateId]);
    header('Location: admin-candidate-attempts.php?candidate_id=' . $candidateId . '&success=1');
    exit();
}

// Fetch candidate info
$stmt = $pdo->prepare("SELECT user_name, user_uid FROM users WHERE id = ? AND role = 'candidate'");
$stmt->execute([$candidateId]);
$candidate = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$candidate) {
    echo '<div class="p-6 text-red-500">Candidate not found.</div>';
    exit();
}

// Fetch attempts (same logic as candidate dashboard: only for existing tests)
$stmt = $pdo->prepare("
    SELECT ta.*, t.title, t.passing_score
    FROM test_attempts ta
    JOIN tests t ON ta.test_id = t.id
    WHERE ta.user_id = ?
    ORDER BY ta.completed_at DESC
");
$stmt->execute([$candidateId]);
$attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Stats
$totalTests = count($attempts);
$totalScore = 0;
$passedTests = 0;
foreach ($attempts as $a) {
    $totalScore += $a['score'];
    if ($a['score'] >= $a['passing_score']) $passedTests++;
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
    <title>Candidate Attempts - Admin</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-white/70 border-b shadow-lg mb-8">
        <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center">
            <a href="admin-dashboard.php" class="text-2xl font-bold text-amber-600">CodeLens Admin</a>
            <span class="text-gray-700">Admin</span>
        </div>
    </nav>
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow p-8 mt-4">
        <h2 class="text-2xl font-bold mb-4">Candidate: <?php echo htmlspecialchars($candidate['user_name']) . ' (ID: ' . htmlspecialchars($candidate['user_uid']) . ')'; ?></h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-amber-50 p-4 rounded-lg border border-amber-200">
                <div class="text-sm text-amber-700 font-medium">Total Attempts</div>
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
        <h3 class="text-xl font-semibold mt-8 mb-4">All Test Attempts</h3>
        <?php if (empty($attempts)): ?>
            <div class="text-gray-500">No attempts found.</div>
        <?php else: ?>
            <table class="min-w-full bg-white border rounded-lg overflow-hidden">
                <thead>
                    <tr>
                        <th class="px-4 py-2 border-b text-left">Test</th>
                        <th class="px-4 py-2 border-b text-left">Score</th>
                        <th class="px-4 py-2 border-b text-left">Passing Score</th>
                        <th class="px-4 py-2 border-b text-left">Completed At</th>
                        <th class="px-4 py-2 border-b text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attempts as $a): ?>
                        <tr>
                            <td class="px-4 py-2 border-b"><?php echo htmlspecialchars($a['title']); ?></td>
                            <td class="px-4 py-2 border-b"><?php echo $a['score']; ?></td>
                            <td class="px-4 py-2 border-b"><?php echo $a['passing_score']; ?></td>
                            <td class="px-4 py-2 border-b"><?php echo htmlspecialchars($a['completed_at']); ?></td>
                            <td class="px-4 py-2 border-b">
                                <form method="POST" onsubmit="return confirm('Delete this attempt?');" style="display:inline;">
                                    <input type="hidden" name="delete_attempt_id" value="<?php echo $a['id']; ?>">
                                    <button type="submit" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-700 text-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
