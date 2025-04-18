<?php
require_once '../../Backend/PHP/auth.php';
require_once '../../Backend/PHP/config.php';
requireLogin();

// Ensure user is admin
if ($_SESSION['user_role'] !== 'admin') {
    header('Location: candidate-dashboard.php');
    exit();
}

// Get test ID from URL
$test_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($test_id <= 0) {
    header('Location: manage-tests.php');
    exit();
}

// Fetch test details
try {
    $stmt = $pdo->prepare("SELECT * FROM tests WHERE id = ? AND created_by = ?");
    $stmt->execute([$test_id, $_SESSION['user_id']]);
    $test = $stmt->fetch();
    
    if (!$test) {
        header('Location: manage-tests.php');
        exit();
    }
} catch (PDOException $e) {
    $error = "Error fetching test: " . $e->getMessage();
    header('Location: manage-tests.php?error=' . urlencode($error));
    exit();
}

// Handle add question to test
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_questions'])) {
    if (!empty($_POST['question_ids'])) {
        try {
            // Begin transaction
            $pdo->beginTransaction();
            
            // Delete existing questions if replace option is selected
            if (isset($_POST['replace_existing']) && $_POST['replace_existing'] === 'yes') {
                $deleteStmt = $pdo->prepare("DELETE FROM test_questions WHERE test_id = ?");
                $deleteStmt->execute([$test_id]);
            }
            
            // Insert new question associations
            $insertStmt = $pdo->prepare("INSERT INTO test_questions (test_id, question_id) VALUES (?, ?)");
            
            foreach ($_POST['question_ids'] as $question_id) {
                // Check if this association already exists
                $checkStmt = $pdo->prepare("SELECT id FROM test_questions WHERE test_id = ? AND question_id = ?");
                $checkStmt->execute([$test_id, $question_id]);
                
                if (!$checkStmt->fetch()) {
                    $insertStmt->execute([$test_id, $question_id]);
                }
            }
            
            // Commit transaction
            $pdo->commit();
            
            $success = "Questions added to test successfully";
            header("Location: test-questions.php?id=$test_id&success=" . urlencode($success));
            exit();
            
        } catch (PDOException $e) {
            // Rollback transaction on error
            $pdo->rollBack();
            $error = "Error adding questions: " . $e->getMessage();
        }
    } else {
        $error = "Please select at least one question";
    }
}

// Fetch already added questions for this test
try {
    $stmt = $pdo->prepare("
        SELECT q.* FROM questions q
        JOIN test_questions tq ON q.id = tq.question_id
        WHERE tq.test_id = ?
        ORDER BY q.category, q.difficulty
    ");
    $stmt->execute([$test_id]);
    $added_questions = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching test questions: " . $e->getMessage();
    $added_questions = [];
}

// Fetch available questions not yet added to this test
try {
    $stmt = $pdo->prepare("
        SELECT q.* FROM questions q
        WHERE q.admin_id = ? 
        AND q.id NOT IN (
            SELECT question_id FROM test_questions WHERE test_id = ?
        )
        ORDER BY q.category, q.difficulty
    ");
    $stmt->execute([$_SESSION['user_id'], $test_id]);
    $available_questions = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching available questions: " . $e->getMessage();
    $available_questions = [];
}

// Categories mapping
$categories = [
    'data_structures' => 'Data Structures',
    'algorithms' => 'Algorithms',
    'programming' => 'Programming',
    'database' => 'Database',
    'system_design' => 'System Design',
    'web_development' => 'Web Development',
    'other' => 'Other'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/Frontend/src/tailwind.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <title>Test Questions - CodeLens</title>
    <style>
        .question-list-scroll { max-height: 300px; overflow-y: auto; }
    </style>
</head>
<body class="bg-gray-100">
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
                    <span class="text-gray-700">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <span class="bg-amber-100 text-amber-800 px-2 py-1 rounded-full text-sm">Admin</span>
                    <a href="/Backend/PHP/logout.php" class="bg-amber-600 text-white px-4 py-2 rounded-md hover:bg-amber-700">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Test Details -->
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold">Test: <?php echo htmlspecialchars($test['title']); ?></h2>
                    <a href="manage-tests.php" class="text-amber-600 hover:text-amber-800">
                        ← Back to Tests
                    </a>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
                    <div class="bg-gray-50 rounded p-4">
                        <div class="text-sm text-gray-500">Duration</div>
                        <div class="text-lg font-semibold"><?php echo $test['duration']; ?> minutes</div>
                    </div>
                    <div class="bg-gray-50 rounded p-4">
                        <div class="text-sm text-gray-500">Passing Score</div>
                        <div class="text-lg font-semibold"><?php echo $test['passing_score']; ?>%</div>
                    </div>
                    <div class="bg-gray-50 rounded p-4">
                        <div class="text-sm text-gray-500">Questions</div>
                        <div class="text-lg font-semibold"><?php echo count($added_questions); ?></div>
                    </div>
                </div>
                
                <?php if (!empty($test['description'])): ?>
                <div class="mt-4">
                    <h3 class="text-lg font-semibold mb-2">Description</h3>
                    <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($test['description'])); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
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
        
        <!-- Current Test Questions -->
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="p-6">
                <h2 class="text-2xl font-bold mb-6">Current Test Questions</h2>
                
                <?php if(empty($added_questions)): ?>
                    <p class="text-gray-500 text-center py-6">No questions have been added to this test yet.</p>
                <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Question</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Difficulty</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach($added_questions as $question): ?>
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        <?php echo htmlspecialchars(substr($question['question_text'], 0, 50) . (strlen($question['question_text']) > 50 ? '...' : '')); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <?php echo isset($categories[$question['category']]) ? $categories[$question['category']] : $question['category']; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?php 
                                                    if($question['difficulty'] === 'easy') echo 'bg-green-100 text-green-800';
                                                    elseif($question['difficulty'] === 'medium') echo 'bg-amber-100 text-amber-800';
                                                    else echo 'bg-red-100 text-red-800';
                                                ?>">
                                        <?php echo ucfirst($question['difficulty']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <a href="remove-question.php?test_id=<?php echo $test_id; ?>&question_id=<?php echo $question['id']; ?>" 
                                       class="text-red-600 hover:text-red-800" 
                                       onclick="return confirm('Are you sure you want to remove this question from the test?')">
                                        Remove
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
        
        <!-- Add Questions Form -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6">
                <h2 class="text-2xl font-bold mb-6">Add Questions to Test</h2>
                
                <?php if(empty($available_questions)): ?>
                    <p class="text-gray-500 text-center py-6">No additional questions available. <a href="manage-questions.php" class="text-amber-600 hover:text-amber-800">Create new questions</a></p>
                <?php else: ?>
                <form method="POST" action="">
                    <div class="mb-4">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="replace_existing" value="yes" class="h-4 w-4 text-amber-600 focus:ring-amber-500 border-gray-300 rounded">
                            <span class="ml-2 text-gray-700">Replace existing questions (removes all currently added questions)</span>
                        </label>
                    </div>
                    
                    <div class="overflow-x-auto mb-6">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <input type="checkbox" id="select-all" class="h-4 w-4 text-amber-600 focus:ring-amber-500 border-gray-300 rounded">
                                    </th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Question</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Difficulty</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach($available_questions as $question): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" name="question_ids[]" value="<?php echo $question['id']; ?>" 
                                               class="question-checkbox h-4 w-4 text-amber-600 focus:ring-amber-500 border-gray-300 rounded">
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                            <?php echo htmlspecialchars(substr($question['question_text'], 0, 50) . (strlen($question['question_text']) > 50 ? '...' : '')); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <?php echo isset($categories[$question['category']]) ? $categories[$question['category']] : $question['category']; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    <?php 
                                                        if($question['difficulty'] === 'easy') echo 'bg-green-100 text-green-800';
                                                        elseif($question['difficulty'] === 'medium') echo 'bg-amber-100 text-amber-800';
                                                        else echo 'bg-red-100 text-red-800';
                                                    ?>">
                                            <?php echo ucfirst($question['difficulty']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div>
                        <button type="submit" name="add_questions" 
                                class="bg-amber-600 text-white px-6 py-2 rounded-md hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">
                            Add Selected Questions
                        </button>
                    </div>
                </form>
                
                <script>
                    // JavaScript for select all functionality
                    document.getElementById('select-all').addEventListener('change', function() {
                        const checkboxes = document.querySelectorAll('.question-checkbox');
                        checkboxes.forEach(checkbox => {
                            checkbox.checked = this.checked;
                        });
                    });
                </script>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html> 