<?php
require_once '../../Backend/PHP/auth.php';
require_once '../../Backend/PHP/config.php';
requireLogin();

// Ensure user is admin
if ($_SESSION['user_role'] !== 'admin') {
    header('Location: candidate-dashboard.php');
    exit();
}

// Get admin info
$adminName = $_SESSION['user_name'];
$adminUID = $_SESSION['user_uid'];

// Fetch dashboard stats
try {
    // Total Questions created by this admin
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM questions WHERE admin_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $totalQuestions = $stmt->fetchColumn();

    // Active Tests created by this admin
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tests WHERE created_by = ? AND active = 1");
    $stmt->execute([$_SESSION['user_id']]);
    $activeTests = $stmt->fetchColumn();

    // Registered Candidates (all users with role 'candidate')
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'candidate'");
    $registeredCandidates = $stmt->fetchColumn();
} catch(PDOException $e) {
    $totalQuestions = $activeTests = $registeredCandidates = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/Frontend/src/tailwind.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <title>Admin Dashboard - CodeLens</title>
<style>
@keyframes fadein {
  0% { opacity: 0; transform: translateY(20px); }
  100% { opacity: 1; transform: translateY(0); }
}
.animate-fadein { animation: fadein 0.8s ease; }
</style>
</head>
<body class="bg-gradient-to-br from-amber-100 via-amber-200 to-amber-400 min-h-screen animate-fadein">
    <!-- Navigation -->
    <nav class="bg-white/70 backdrop-blur border-b border-amber-100 shadow-lg sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-8">
                    <a href="../index.php" class="text-2xl font-bold text-amber-600">CodeLens</a>
                    <a href="admin-dashboard.php" class="text-gray-700 hover:text-amber-600 font-medium">Dashboard</a>
                    <a href="manage-questions.php" class="text-gray-700 hover:text-amber-600">Questions</a>
                    <a href="manage-tests.php" class="text-gray-700 hover:text-amber-600">Tests</a>
                    <a href="admin-analytics.php" class="text-gray-700 hover:text-amber-600">Analytics</a>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700">Welcome, <?php echo htmlspecialchars($adminName); ?></span>
                    <span class="bg-amber-100 text-amber-800 px-2 py-1 rounded-full text-sm">Admin</span>
                    <span class="text-sm text-gray-500">ID: <?php echo htmlspecialchars($adminUID); ?></span>
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
            <h1 class="text-4xl md:text-5xl font-extrabold mb-2 drop-shadow-lg">Welcome, <?php echo htmlspecialchars($adminName); ?>!</h1>
            <p class="text-lg md:text-xl opacity-90">Manage tests, questions, and candidates with ease.</p>
        </div>
        <div class="flex flex-col items-center">
            <span class="bg-amber-100 text-amber-800 px-3 py-1 rounded-full text-base font-semibold mb-2">Admin</span>
            <span class="text-white/70 text-xs">ID: <?php echo htmlspecialchars($adminUID); ?></span>
        </div>
    </div>
        <!-- Admin Controls -->
        <div class="mb-8 grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Quick Stats -->
            <div class="bg-white/80 rounded-2xl shadow-xl p-8 hover:shadow-2xl transition-shadow flex flex-col items-center">
    <div class="mb-4"><svg class="w-10 h-10 text-amber-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg></div>
    <h3 class="text-lg font-semibold mb-2">Total Questions</h3>
    <span class="text-3xl font-extrabold text-gray-900 mb-2"><?php echo isset($totalQuestions) ? $totalQuestions : 0; ?></span>
    <span class="text-sm text-gray-500">Questions created</span>
</div>
<div class="bg-white/80 rounded-2xl shadow-xl p-8 hover:shadow-2xl transition-shadow flex flex-col items-center">
    <div class="mb-4"><svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-6a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v6"/><path stroke-linecap="round" stroke-linejoin="round" d="M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z"/></svg></div>
    <h3 class="text-lg font-semibold mb-2">Active Tests</h3>
    <span class="text-3xl font-extrabold text-gray-900 mb-2"><?php echo isset($activeTests) ? $activeTests : 0; ?></span>
    <span class="text-sm text-gray-500">Currently running</span>
</div>
<div class="bg-white/80 rounded-2xl shadow-xl p-8 hover:shadow-2xl transition-shadow flex flex-col items-center">
    <div class="mb-4"><svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M8 12l2 2 4-4"/></svg></div>
    <h3 class="text-lg font-semibold mb-2">Candidates</h3>
    <span class="text-3xl font-extrabold text-gray-900 mb-2"><?php echo isset($registeredCandidates) ? $registeredCandidates : 0; ?></span>
    <span class="text-sm text-gray-500">Registered users</span>

</div>

                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow p-6 col-span-2">
                <h3 class="text-lg font-semibold mb-4">Quick Actions</h3>
                <div class="grid grid-cols-2 gap-4">
                    <button onclick="location.href='manage-tests.php'" class="p-4 bg-amber-50 rounded-lg hover:bg-amber-100 transition-colors">
                        <h4 class="font-semibold text-amber-700">Create Tests</h4>
                        <p class="text-sm text-gray-600">Create and Modify Tests</p>
                    </button>
                    <button onclick="location.href='manage-questions.php'" class="p-4 bg-amber-50 rounded-lg hover:bg-amber-100 transition-colors">
                        <h4 class="font-semibold text-amber-700">Manage Questions</h4>
                        <p class="text-sm text-gray-600">Add and Edit Questions</p>
                    </button>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4">Recent Activity</h3>
                <?php include 'admin-dashboard-activity.php'; ?>
                <?php if (empty($recentActivity)): ?>
                    <p class="text-gray-500 text-center py-4">No recent activity</p>
                <?php else: ?>
                    <ul class="divide-y divide-gray-200">
                        <?php foreach ($recentActivity as $activity): ?>
                        <li class="py-3 flex items-center gap-4">
                            <?php if ($activity['type'] === 'Test Created'): ?>
                                <span class="inline-block bg-amber-100 text-amber-700 px-2 py-1 rounded text-xs font-bold">Test</span>
                            <?php elseif ($activity['type'] === 'Question Added'): ?>
                                <span class="inline-block bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs font-bold">Question</span>
                            <?php elseif ($activity['type'] === 'Candidate Registered'): ?>
                                <span class="inline-block bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-bold">Candidate</span>
                            <?php endif; ?>
                            <span class="flex-1 text-gray-800 text-sm">
                                <?php echo htmlspecialchars($activity['info']); ?>
                            </span>
                            <span class="text-xs text-gray-400">
                                <?php echo date('M d, Y H:i', strtotime($activity['time'])); ?>
                            </span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>