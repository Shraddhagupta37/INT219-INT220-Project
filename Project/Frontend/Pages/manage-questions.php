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

// Handle question deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_question']) && isset($_POST['question_id'])) {
    $qid = intval($_POST['question_id']);
    try {
        // First, remove from test_questions
        $stmt = $pdo->prepare("DELETE FROM test_questions WHERE question_id = ?");
        $stmt->execute([$qid]);
        // Now, delete the question itself
        $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ? AND admin_id = ?");
        $stmt->execute([$qid, $_SESSION['user_id']]);
        $success = "Question deleted successfully.";
        header("Location: manage-questions.php?success=" . urlencode($success));
        exit();
    } catch (PDOException $e) {
        $error = "Error deleting question: " . $e->getMessage();
    }
}

// Handle question creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_question'])) {
    $question_text = trim($_POST['question_text']);
    $option_a = trim($_POST['option_a']);
    $option_b = trim($_POST['option_b']);
    $option_c = trim($_POST['option_c']);
    $option_d = trim($_POST['option_d']);
    $correct_option = $_POST['correct_option'];
    $category = $_POST['category'];
    $difficulty = $_POST['difficulty'];
    
    if (empty($question_text) || empty($option_a) || empty($option_b) || empty($option_c) || empty($option_d)) {
        $error = "Please fill in all required fields";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO questions (admin_id, question_text, option_a, option_b, option_c, option_d, correct_option, category, difficulty) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $question_text, $option_a, $option_b, $option_c, $option_d, $correct_option, $category, $difficulty]);
            
            $success = "Question created successfully";
            
            // Redirect to avoid form resubmission
            header("Location: manage-questions.php?success=" . urlencode($success));
            exit();
        } catch (PDOException $e) {
            $error = "Error creating question: " . $e->getMessage();
        }
    }
}

// Fetch all tests created by this admin
try {
    $stmt = $pdo->prepare("SELECT * FROM tests WHERE created_by = ? ORDER BY created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $tests = $stmt->fetchAll();
} catch (PDOException $e) {
    $tests = [];
}

// Fetch all questions created by this admin
try {
    $stmt = $pdo->prepare("
        SELECT * FROM questions 
        WHERE admin_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $questions = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching questions: " . $e->getMessage();
    $questions = [];
}

// Categories for dropdown
$categories = [
    'general' => 'General',
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
    <title>Manage Questions - CodeLens</title>
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
                    <a href="manage-questions.php" class="text-gray-700 hover:text-amber-600 font-medium">Questions</a>
                    <a href="manage-tests.php" class="text-gray-700 hover:text-amber-600">Tests</a>
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
        <!-- Create Question Form -->
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="p-6">
                <h2 class="text-2xl font-bold mb-6">Create New Question</h2>
                
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
                
                <form method="POST" action="" id="questionForm">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Question Type</label>
                        <select name="type" id="questionType" class="mt-1 block w-full h-11 text-base rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 px-3 py-2" required>
                            <option value="MCQ">MCQ</option>
                            <option value="Coding">Coding</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Question Text</label>
                        <textarea name="question_text" rows="3" class="mt-1 block w-full text-base rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 px-3 py-2" required></textarea>
                    </div>
                    <div id="mcqFields">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Option A</label>
                                <input type="text" name="option_a" class="mt-1 block w-full h-11 text-base rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Option B</label>
                                <input type="text" name="option_b" class="mt-1 block w-full h-11 text-base rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Option C</label>
                                <input type="text" name="option_c" class="mt-1 block w-full h-11 text-base rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Option D</label>
                                <input type="text" name="option_d" class="mt-1 block w-full h-11 text-base rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 px-3 py-2">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Correct Option</label>
                            <select name="correct_option" class="mt-1 block w-full h-11 text-base rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 px-3 py-2">
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                            </select>
                        </div>
                    </div>
                    <div id="codingFields" style="display:none;">
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Language</label>
        <select name="language" id="codingLanguage" class="mt-1 block w-full h-11 text-base rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 px-3 py-2">
            <option value="python">Python</option>
            <option value="c">C</option>
            <option value="cpp">C++</option>
            <option value="java">Java</option>
        </select>
    </div>
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Starter Code (optional)</label>
        <div id="monacoEditorContainer" class="border border-gray-300" style="height: 260px; border-radius: 0.75rem; overflow: hidden;"></div>
        <textarea name="starter_code" id="starterCodeTextarea" style="display:none;"></textarea>
    </div>
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Test Cases</label>
        <div id="testCasesContainer"></div>
        <button type="button" id="addTestCaseBtn" class="mt-2 bg-amber-500 text-white px-4 py-1 rounded hover:bg-amber-600">Add Test Case</button>
    </div>
    <style>
        .test-case-card { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 1rem; margin-bottom: 1rem; position: relative; }
        .remove-testcase-btn { position: absolute; top: 0.5rem; right: 0.5rem; color: #b91c1c; background: #fee2e2; border: none; border-radius: 0.25rem; padding: 0.25rem 0.5rem; cursor: pointer; }
    </style>
    <style>
    /* Ensure Monaco editor itself is rounded */
    #monacoEditorContainer .monaco-editor,
    #monacoEditorContainer .overflow-guard {
        border-radius: 0.75rem !important;
        overflow: hidden;
    }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/monaco-editor@0.34.1/min/vs/loader.js"></script>
    <script>
    let monacoEditor;
    let monacoLoaded = false;
    let testCaseCount = 1;
    let testCases = [{input:'',output:''}];
    function renderTestCases() {
        const container = document.getElementById('testCasesContainer');
        container.innerHTML = '';
        testCases.forEach((tc, idx) => {
            container.innerHTML += `
            <div class='test-case-card'>
                <button type='button' class='remove-testcase-btn' onclick='removeTestCase(${idx})' title='Remove'>&times;</button>
                <label class='block text-xs font-medium mb-1'>Input</label>
                <textarea name='test_case_input_${idx+1}' rows='2' class='block w-full mb-2 rounded border px-2 py-1'>${tc.input}</textarea>
                <label class='block text-xs font-medium mb-1'>Expected Output</label>
                <textarea name='test_case_output_${idx+1}' rows='2' class='block w-full rounded border px-2 py-1'>${tc.output}</textarea>
            </div>`;
        });
    }
    function removeTestCase(idx) {
        testCases.splice(idx,1);
        if(testCases.length===0) testCases.push({input:'',output:''});
        renderTestCases();
    }
    window.removeTestCase = removeTestCase;
    document.addEventListener('DOMContentLoaded', function() {
        // Monaco loader
        require.config({ paths: { 'vs': 'https://cdn.jsdelivr.net/npm/monaco-editor@0.34.1/min/vs' }});
        require(['vs/editor/editor.main'], function() {
            monacoLoaded = true;
            monacoEditor = monaco.editor.create(document.getElementById('monacoEditorContainer'), {
                value: '',
                language: 'python',
                theme: 'vs-dark',
                fontSize: 15,
                minimap: {enabled: false}
            });
        });
        // Coding/MCQ toggle
        const typeSelect = document.getElementById('questionType');
        const mcqFields = document.getElementById('mcqFields');
        const codingFields = document.getElementById('codingFields');
        function toggleFields() {
            if(typeSelect.value === 'Coding') {
                mcqFields.style.display = 'none';
                codingFields.style.display = '';
                setTimeout(()=>{ if(monacoEditor) monacoEditor.layout(); }, 200);
            } else {
                mcqFields.style.display = '';
                codingFields.style.display = 'none';
            }
        }
        typeSelect.addEventListener('change', toggleFields);
        toggleFields();
        // Language change
        document.getElementById('codingLanguage').addEventListener('change', function() {
            if(monacoLoaded && monacoEditor) {
                let lang = this.value;
                if(lang==='cpp' || lang==='c') lang='c_cpp';
                if(lang==='python') lang='python';
                if(lang==='java') lang='java';
                monaco.editor.setModelLanguage(monacoEditor.getModel(), lang);
            }
        });
        // Test cases
        renderTestCases();
        document.getElementById('addTestCaseBtn').addEventListener('click', function() {
            testCases.push({input:'',output:''});
            renderTestCases();
        });
        // Form submit: sync code and testcases
        document.getElementById('questionForm').addEventListener('submit', function() {
            if(typeSelect.value==='Coding' && monacoEditor) {
                document.getElementById('starterCodeTextarea').value = monacoEditor.getValue();
                // Sync test cases
                testCases = [];
                document.querySelectorAll('#testCasesContainer .test-case-card').forEach(function(card, idx){
                    let input = card.querySelector('textarea[name^="test_case_input_"]').value;
                    let output = card.querySelector('textarea[name^="test_case_output_"]').value;
                    testCases.push({input,output});
                });
                // Save as JSON in a hidden input if needed for backend
            }
        });
    });
    </script>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.14/ace.js" integrity="sha512-6Zk6lJ5p6Kk7xJqQX6e9gWlL7rM6l9z3u5VQf3A1K0+JQv+z5kQ1FQwF7D0zF7l6Kk6lJ5p6Kk7xJqQX6e9gWlA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('questionType');
    const mcqFields = document.getElementById('mcqFields');
    const codingFields = document.getElementById('codingFields');
    const aceEditorWrapper = document.getElementById('aceEditorWrapper');
    let aceEditor;
    function toggleFields() {
        if(typeSelect.value === 'Coding') {
            mcqFields.style.display = 'none';
            codingFields.style.display = '';
            aceEditorWrapper.style.display = '';
            if(!aceEditor) {
                aceEditor = ace.edit('aceEditor');
                aceEditor.setTheme('ace/theme/monokai');
                aceEditor.session.setMode('ace/mode/python');
                aceEditor.setOptions({fontSize: '15px', minLines: 8, maxLines: 20});
            }
        } else {
            mcqFields.style.display = '';
            codingFields.style.display = 'none';
            aceEditorWrapper.style.display = 'none';
        }
    }
    typeSelect.addEventListener('change', toggleFields);
    toggleFields();

    // Sync Ace Editor with textarea on submit
    document.getElementById('questionForm').addEventListener('submit', function() {
        if(typeSelect.value === 'Coding' && aceEditor) {
            document.getElementById('starterCodeTextarea').value = aceEditor.getValue();
        }
    });
    // Change Ace mode on language select
    document.getElementById('codingLanguage').addEventListener('change', function() {
        if(aceEditor) {
            let lang = this.value;
            let mode = 'python';
            if(lang === 'cpp') mode = 'c_cpp';
            else if(lang === 'java') mode = 'java';
            else if(lang === 'javascript') mode = 'javascript';
            aceEditor.session.setMode('ace/mode/' + mode);
        }
    });
});
</script>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <select name="category" class="mt-1 block w-full h-11 text-base rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 px-3 py-2" required>
                                <?php foreach ($categories as $value => $label): ?>
                                    <option value="<?php echo $value; ?>"<?php echo $value === 'general' ? ' selected' : ''; ?>><?php echo $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Difficulty</label>
                            <select name="difficulty" 
                                    class="mt-1 block w-full h-11 text-base rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 px-3 py-2" required>
                                <option value="easy">Easy</option>
                                <option value="medium">Medium</option>
                                <option value="hard">Hard</option>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <button type="submit" name="create_question" 
                                class="bg-amber-600 text-white px-6 py-2 rounded-md hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">
                            Create Question
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Questions List -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6">
                <h2 class="text-2xl font-bold mb-6">Your Questions</h2>
                
                <?php if(empty($questions)): ?>
                    <p class="text-gray-500 text-center py-6">You haven't created any questions yet.</p>
                <?php else: ?>
                <form id="bulkAddToTestForm" method="POST" action="">
    <div class="flex items-center mb-4">
    <input type="checkbox" id="selectAllQuestions" class="mr-2">
    <label for="selectAllQuestions" class="mr-6">Select All</label>
    <div class="flex-1"></div>
    <button type="button" class="bg-amber-600 text-white px-4 py-2 rounded hover:bg-amber-700" onclick="openBulkAddToTestModal()" id="openBulkAddBtn" disabled>Add Selected to Test</button>
</div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Question</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Difficulty</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
<th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Add to Test</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
    <?php foreach($questions as $question): ?>
    <tr>
        <td class="px-2 py-4 text-center align-middle">
            <input type="checkbox" name="bulk_question_ids[]" value="<?php echo $question['id']; ?>" class="questionCheckbox">
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
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('M d, Y', strtotime($question['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
    <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this question?');" style="display:inline;">
        <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
        <button type="submit" name="delete_question" class="bg-red-600 text-white px-4 py-1 rounded hover:bg-red-700">Delete</button>
    </form>
</td>
<!-- Add to Test Button -->
<td class="px-6 py-4 whitespace-nowrap text-sm">
    <button type="button" class="bg-amber-600 text-white px-4 py-1 rounded hover:bg-amber-700" onclick="openAddToTestModal(<?php echo $question['id']; ?>)">Add to Test</button>
</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
    </div>
</form>
<?php endif; ?>
            </div>
        </div>
    </div>

<!-- Bulk Add to Test Modal -->
<div id="bulkAddToTestModal" class="fixed z-50 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
  <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeBulkAddToTestModal()"></div>
    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
    <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
      <form id="bulkAddToTestModalForm" method="POST" action="">
        <input type="hidden" name="bulk_add_to_test" value="1">
        <div class="mb-4">
          <label for="bulk_test_id" class="block text-sm font-medium text-gray-700 mb-1">Select Test</label>
          <select name="bulk_test_id" id="bulkAddToTestTestSelect" class="mt-1 block w-full h-11 text-base rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 px-3 py-2" required>
            <option value="">-- Select Test --</option>
            <?php foreach($tests as $test): ?>
                <option value="<?php echo $test['id']; ?>"><?php echo htmlspecialchars($test['title']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="flex justify-end">
          <button type="button" onclick="closeBulkAddToTestModal()" class="bg-gray-200 text-gray-700 px-4 py-2 rounded mr-2">Cancel</button>
          <button type="submit" class="bg-amber-600 text-white px-4 py-2 rounded hover:bg-amber-700">Add Selected</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Bulk selection logic
const selectAll = document.getElementById('selectAllQuestions');
const checkboxes = document.getElementsByClassName('questionCheckbox');
const openBulkBtn = document.getElementById('openBulkAddBtn');

selectAll && selectAll.addEventListener('change', function() {
    for (let cb of checkboxes) cb.checked = this.checked;
    updateBulkBtnState();
});

for (let cb of checkboxes) {
    cb.addEventListener('change', updateBulkBtnState);
}

function updateBulkBtnState() {
    let checked = false;
    for (let cb of checkboxes) if (cb.checked) checked = true;
    openBulkBtn.disabled = !checked;
}

function openBulkAddToTestModal() {
    // Copy selected question IDs to modal form
    const modalForm = document.getElementById('bulkAddToTestModalForm');
    // Remove previous hidden inputs
    [...modalForm.querySelectorAll('input[name="bulk_question_ids[]"]')].forEach(e => e.remove());
    // Add checked question ids
    for (let cb of checkboxes) {
        if (cb.checked) {
            let hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'bulk_question_ids[]';
            hidden.value = cb.value;
            modalForm.appendChild(hidden);
        }
    }
    document.getElementById('bulkAddToTestModal').classList.remove('hidden');
}
function closeBulkAddToTestModal() {
    document.getElementById('bulkAddToTestModal').classList.add('hidden');
}
</script>

<?php
// Handle Bulk Add to Test
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_add_to_test'], $_POST['bulk_question_ids'], $_POST['bulk_test_id'])) {
    $bulk_test_id = (int)$_POST['bulk_test_id'];
    $bulk_question_ids = array_map('intval', $_POST['bulk_question_ids']);
    $added = 0;
    $skipped = 0;
    try {
        $pdo->beginTransaction();
        $insertStmt = $pdo->prepare("INSERT INTO test_questions (test_id, question_id) VALUES (?, ?)");
        $checkStmt = $pdo->prepare("SELECT id FROM test_questions WHERE test_id = ? AND question_id = ?");
        foreach ($bulk_question_ids as $qid) {
            $checkStmt->execute([$bulk_test_id, $qid]);
            if (!$checkStmt->fetch()) {
                $insertStmt->execute([$bulk_test_id, $qid]);
                $added++;
            } else {
                $skipped++;
            }
        }
        $pdo->commit();
        echo '<script>alert("Added '.$added.' questions to test. '.($skipped ? $skipped.' already existed.' : '').'"); window.location.href = "manage-questions.php";</script>';
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo '<script>alert("Error adding questions: '.addslashes($e->getMessage()).'"); window.location.href = "manage-questions.php";</script>';
        exit();
    }
}
?>

<!-- Add to Test Modal -->
<div id="addToTestModal" class="fixed z-50 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
  <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeAddToTestModal()"></div>
    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
    <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
      <form id="addToTestForm" method="POST" action="">
        <input type="hidden" name="add_to_test_question_id" id="addToTestQuestionId" value="">
        <div class="mb-4">
          <label for="test_id" class="block text-sm font-medium text-gray-700 mb-1">Select Test</label>
          <select name="test_id" id="addToTestTestSelect" class="mt-1 block w-full h-11 text-base rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 px-3 py-2" required>
            <option value="">-- Select Test --</option>
            <?php foreach($tests as $test): ?>
                <option value="<?php echo $test['id']; ?>"><?php echo htmlspecialchars($test['title']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="flex justify-end">
          <button type="button" onclick="closeAddToTestModal()" class="bg-gray-200 text-gray-700 px-4 py-2 rounded mr-2">Cancel</button>
          <button type="submit" name="add_to_test" class="bg-amber-600 text-white px-4 py-2 rounded hover:bg-amber-700">Add</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
function openAddToTestModal(questionId) {
    document.getElementById('addToTestQuestionId').value = questionId;
    document.getElementById('addToTestModal').classList.remove('hidden');
}
function closeAddToTestModal() {
    document.getElementById('addToTestModal').classList.add('hidden');
}
</script>
<?php
// Handle Add to Test (at the top, after fetching $tests)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_test'], $_POST['add_to_test_question_id'], $_POST['test_id'])) {
    $question_id = (int)$_POST['add_to_test_question_id'];
    $test_id = (int)$_POST['test_id'];
    try {
        // Check if already added
        $stmt = $pdo->prepare("SELECT id FROM test_questions WHERE test_id = ? AND question_id = ?");
        $stmt->execute([$test_id, $question_id]);
        if (!$stmt->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO test_questions (test_id, question_id) VALUES (?, ?)");
            $stmt->execute([$test_id, $question_id]);
            echo '<script>alert("Question added to test successfully!"); window.location.href = "manage-questions.php";</script>';
            exit();
        } else {
            echo '<script>alert("This question is already added to the selected test."); window.location.href = "manage-questions.php";</script>';
            exit();
        }
    } catch (PDOException $e) {
        echo '<script>alert("Error adding question to test: '.addslashes($e->getMessage()).'"); window.location.href = "manage-questions.php";</script>';
        exit();
    }
}
?>
</body>
</html> 