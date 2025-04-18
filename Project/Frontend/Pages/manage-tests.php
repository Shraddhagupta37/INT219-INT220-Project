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

// Handle test deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_test']) && isset($_POST['test_id'])) {
    $tid = intval($_POST['test_id']);
    try {
        // First, remove related test_questions
        $stmt = $pdo->prepare("DELETE FROM test_questions WHERE test_id = ?");
        $stmt->execute([$tid]);
        // Now, delete the test itself
        $stmt = $pdo->prepare("DELETE FROM tests WHERE id = ? AND created_by = ?");
        $stmt->execute([$tid, $_SESSION['user_id']]);
        $success = "Test deleted successfully.";
        header("Location: manage-tests.php?success=" . urlencode($success));
        exit();
    } catch (PDOException $e) {
        $error = "Error deleting test: " . $e->getMessage();
    }
}

// Handle test creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_test'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $duration = (int)$_POST['duration'];
    $passing_score = (float)$_POST['passing_score'];
    $test_type = isset($_POST['test_type']) ? $_POST['test_type'] : 'MCQs';
    
    if (empty($title) || $duration <= 0 || $passing_score <= 0) {
        $error = "Please fill in all required fields correctly";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO tests (title, description, duration, passing_score, created_by, type) 
                                VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $description, $duration, $passing_score, $_SESSION['user_id'], $test_type]);
            
            $success = "Test created successfully";
            
            // Redirect to avoid form resubmission
            header("Location: manage-tests.php?success=" . urlencode($success));
            exit();
        } catch (PDOException $e) {
            $error = "Error creating test: " . $e->getMessage();
        }
    }
}

// Fetch all tests
try {
    $stmt = $pdo->prepare("
        SELECT t.*, COUNT(tq.id) as question_count 
        FROM tests t
        LEFT JOIN test_questions tq ON t.id = tq.test_id
        WHERE t.created_by = ?
        GROUP BY t.id
        ORDER BY t.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $tests = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching tests: " . $e->getMessage();
    $tests = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/Frontend/src/tailwind.css" rel="stylesheet">
    <title>Manage Tests - CodeLens</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
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
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-8">
                    <a href="../index.php" class="text-2xl font-bold text-amber-600">CodeLens</a>
                    <a href="admin-dashboard.php" class="text-gray-700 hover:text-amber-600">Dashboard</a>
                    <a href="manage-questions.php" class="text-gray-700 hover:text-amber-600">Questions</a>
                    <a href="manage-tests.php" class="text-gray-700 hover:text-amber-600 font-medium">Tests</a>
                    <a href="admin-analytics.php" class="text-gray-700 hover:text-amber-600">Analytics</a>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700">Welcome, <?php echo htmlspecialchars($adminName); ?></span>
                    <span class="bg-amber-100 text-amber-800 px-2 py-1 rounded-full text-sm">Admin</span>
                    <a href="../../Backend/PHP/logout.php" class="bg-amber-600 text-white px-4 py-2 rounded-md hover:bg-amber-700">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Create Test Form -->
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="p-6">
                <h2 class="text-2xl font-bold mb-6">Create New Test</h2>
                
                <?php if(isset($error)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <?php if(isset($_GET['success'])): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        <?php echo htmlspecialchars($_GET['success']); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Test Title</label>
                            <input type="text" name="title" 
                                   class="mt-1 block w-full h-11 text-base rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 px-3 py-2" 
                                   required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Duration (minutes)</label>
                            <input type="number" name="duration" min="1" 
                                   class="mt-1 block w-full h-11 text-base rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 px-3 py-2" 
                                   required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Test Type</label>
                            <select name="test_type" class="mt-1 block w-full h-11 text-base rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 px-3 py-2" required>
                                <option value="MCQs">MCQs</option>
                                <option value="Coding">Coding</option>
                                <option value="MIX">MIX (Code + MCQ)</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" rows="5" 
                                 class="mt-1 block w-full text-base rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 px-3 py-2" style="min-height:56px;"></textarea>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Passing Score (%)</label>
                        <input type="number" name="passing_score" min="1" max="100" value="70" 
                               class="mt-1 block w-full h-11 text-base rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 px-3 py-2" 
                               required>
                    </div>
                    
                    <div>
                        <button type="submit" name="create_test" 
                                class="bg-amber-600 text-white px-6 py-2 rounded-md hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">
                            Create Test
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Tests List -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6">
                <h2 class="text-2xl font-bold mb-6">Your Tests</h2>
                
                <?php if(empty($tests)): ?>
                    <p class="text-gray-500 text-center py-6">You haven't created any tests yet.</p>
                <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Questions</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pass Score</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach($tests as $test): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php echo htmlspecialchars($test['title']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php echo $test['duration']; ?> min
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php echo $test['question_count']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php echo $test['passing_score']; ?>%
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $test['active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                        <?php echo $test['active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this test?');" style="display:inline;">
                                        <input type="hidden" name="test_id" value="<?php echo $test['id']; ?>">
                                        <button type="submit" name="delete_test" class="bg-red-600 text-white px-4 py-1 rounded hover:bg-red-700">Delete</button>
                                    </form>
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