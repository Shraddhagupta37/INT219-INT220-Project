<?php
require_once '../../Backend/PHP/auth.php';
require_once '../../Backend/PHP/config.php';
requireLogin();

// Ensure user is candidate
if ($_SESSION['user_role'] !== 'candidate') {
    echo '<!DOCTYPE html><html><head><title>Access Denied</title><link href="/Frontend/src/tailwind.css" rel="stylesheet"></head><body class="bg-gray-100 flex items-center justify-center min-h-screen"><div class="bg-white shadow-lg rounded-lg p-8 text-center"><h1 class="text-3xl font-bold text-amber-600 mb-4">Admins cannot take the mock tests.</h1><a href="/Frontend/Pages/admin-dashboard.php" class="mt-4 inline-block bg-amber-600 text-white px-6 py-2 rounded hover:bg-amber-700">Go to Dashboard</a></div></body></html>';
    exit();
}

$userId = $_SESSION['user_id'];

if (!isset($_GET['test_id']) || !is_numeric($_GET['test_id'])) {
    die('Invalid test ID.');
}
$test_id = intval($_GET['test_id']);

// Fetch test details
$stmt = $pdo->prepare("SELECT * FROM tests WHERE id = ? AND active = 1");
$stmt->execute([$test_id]);
$test = $stmt->fetch();
if (!$test) {
    die('Test not found or inactive.');
}

// Fetch questions for the test
$stmt = $pdo->prepare("SELECT q.*, q.correct_option FROM questions q INNER JOIN test_questions tq ON q.id = tq.question_id WHERE tq.test_id = ?");
$stmt->execute([$test_id]);
$questions = $stmt->fetchAll();
if (empty($questions)) {
    die('No questions found for this test.');
}

// Handle form submission
// --- Time tracking ---
if (!isset($_SESSION['test_start_time'])) {
    $_SESSION['test_start_time'] = time();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $answers = $_POST['answers'] ?? [];
    $correct = 0;
    foreach ($questions as $question) {
        $qid = $question['id'];
        $right = isset($question['correct_option']) ? $question['correct_option'] : '';
        $selected = isset($answers[$qid]) ? strtoupper($answers[$qid]) : null;
        if ($selected !== null && $selected === $right) {
            $correct++;
        }
    }
    $score = ($correct / count($questions)) * 100;
    // Calculate time taken
    $timeTaken = 0;
    if (isset($_SESSION['test_start_time'])) {
        $timeTaken = time() - $_SESSION['test_start_time'];
        unset($_SESSION['test_start_time']);
    }
    // Save test duration (in seconds) for result page
    $testDuration = isset($test['duration']) ? intval($test['duration']) * 60 : 0;
    // Cap time taken at test duration
    if ($testDuration > 0 && $timeTaken > $testDuration) {
        $timeTaken = $testDuration;
    }
    try {
        $pdo->beginTransaction();
        // Insert test attempt with completed_at as NOW()
        // Get violation counts from POST
        $fullscreen_violations = isset($_POST['fullscreen_violations']) ? intval($_POST['fullscreen_violations']) : 0;
        $tab_violations = isset($_POST['tab_violations']) ? intval($_POST['tab_violations']) : 0;
        $stmt = $pdo->prepare("INSERT INTO test_attempts (user_id, score, total_questions, test_id, correct_answers, time_taken, fullscreen_violations, tab_violations, duration, completed_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$userId, $score, count($questions), $test_id, $correct, $timeTaken, $fullscreen_violations, $tab_violations, $testDuration]);
        $attempt_id = $pdo->lastInsertId();
        // Save each answer
        foreach ($questions as $question) {
            $qid = $question['id'];
            $selected = isset($answers[$qid]) ? strtoupper($answers[$qid]) : null;
            $stmt = $pdo->prepare("INSERT INTO user_answers (attempt_id, question_id, selected_option) VALUES (?, ?, ?)");
            $stmt->execute([$attempt_id, $qid, $selected]);
        }
        $pdo->commit();
        // Clear violation counters from sessionStorage via JS
        echo "<script>sessionStorage.removeItem('fullscreen_violations'); sessionStorage.removeItem('tab_violations');</script>";
        header('Location: test-result.php?attempt_id=' . $attempt_id);
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">Error saving test attempt: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/Frontend/src/tailwind.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <title>Take Test - CodeLens</title>
</head>
<body class="bg-gray-100">
    <div class="max-w-3xl mx-auto py-8">
        <!-- Fullscreen Prompt & Instructions -->
        <div id="fullscreenPrompt" style="text-align:center;">
            <h2 class="text-xl font-bold mb-4">Ready to start your test?</h2>
            <div class="mb-4 text-gray-700 bg-yellow-50 border border-yellow-200 rounded p-4 max-w-xl mx-auto">
                <ul class="list-disc list-inside text-left">
                    <li>The test will run in fullscreen mode for security.</li>
                    <li>If you exit fullscreen, switch tabs, or minimize the window, you will receive a warning.</li>
                    <li>After 3 such violations, your test will end automatically and be submitted.</li>
                    <li>At the end, you will see the number of violations you made.</li>
                    <li>Click the button below to begin in fullscreen.</li>
                </ul>
            </div>
            <div class="mb-4 flex flex-col items-center">
                <div id="camMicPreview" style="margin-bottom: 12px;">
                    <div id="videoContainer" style="display:inline-block;">
                        <video id="webcamPreview" width="400" height="300" autoplay playsinline style="background:#222; border-radius:12px; border: 2px solid #f59e42;"></video>
                    </div>
                    <div id="micContainer" style="margin-top:12px;">
                        <span id="micLabel" class="text-gray-700 text-sm">Mic Level:</span>
                        <progress id="micLevel" value="0" max="100" style="vertical-align:middle;"></progress>
                        <button id="toggleMic" type="button" class="ml-2 px-2 py-1 rounded text-xs bg-gray-200 hover:bg-gray-300 text-gray-700">Disable Mic</button>
                    </div>
                    <div id="camMicDenied" class="text-red-600 text-xs mt-2" style="display:none;">Camera/Mic access denied. You can still take the test.</div>
                </div>
            </div>
            <button id="startFullscreen" class="bg-amber-600 text-white px-6 py-2 rounded hover:bg-amber-700">
                Start Test in Fullscreen
            </button>
            <script>
            // Camera & Mic preview logic
            let webcam = document.getElementById('webcamPreview');
            let micLevel = document.getElementById('micLevel');
            let camMicDenied = document.getElementById('camMicDenied');
            let toggleMicBtn = document.getElementById('toggleMic');
            let micEnabled = true;
            let audioTrack = null;
            let audioCtx = null;
            let micSource = null;
            let analyser = null;
            let dataArray = null;
            let micAnimFrame = null;

            function startMicLevel(stream) {
                audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                micSource = audioCtx.createMediaStreamSource(stream);
                analyser = audioCtx.createAnalyser();
                micSource.connect(analyser);
                analyser.fftSize = 256;
                dataArray = new Uint8Array(analyser.frequencyBinCount);
                function updateMicLevel() {
                    if (!micEnabled) return;
                    analyser.getByteTimeDomainData(dataArray);
                    let sum = 0;
                    for (let i = 0; i < dataArray.length; i++) {
                        let val = (dataArray[i] - 128) / 128;
                        sum += val * val;
                    }
                    let rms = Math.sqrt(sum / dataArray.length);
                    micLevel.value = Math.min(100, Math.round(rms * 200));
                    micAnimFrame = requestAnimationFrame(updateMicLevel);
                }
                updateMicLevel();
            }

            function stopMicLevel() {
                micLevel.value = 0;
                if (audioCtx) {
                    audioCtx.close();
                    audioCtx = null;
                }
                if (micAnimFrame) {
                    cancelAnimationFrame(micAnimFrame);
                    micAnimFrame = null;
                }
            }

            navigator.mediaDevices.getUserMedia({ video: true, audio: true })
                .then(function(stream) {
                    webcam.srcObject = stream;
                    camMicDenied.style.display = 'none';
                    // Save audio track for toggling
                    audioTrack = stream.getAudioTracks()[0];
                    micEnabled = true;
                    startMicLevel(stream);
                    toggleMicBtn.onclick = function() {
                        micEnabled = !micEnabled;
                        if (!micEnabled) {
                            if (audioTrack) audioTrack.enabled = false;
                            stopMicLevel();
                            toggleMicBtn.textContent = 'Enable Mic';
                        } else {
                            if (audioTrack) audioTrack.enabled = true;
                            startMicLevel(stream);
                            toggleMicBtn.textContent = 'Disable Mic';
                        }
                    };
                })
                .catch(function(err) {
                    camMicDenied.style.display = 'block';
                });

            // --- Make webcam visible in fullscreen mode (testForm) ---
            // Clone the video node to testForm
            document.addEventListener('DOMContentLoaded', function() {
                let formCamContainer = document.createElement('div');
                formCamContainer.id = 'formCamContainer';
                formCamContainer.style = 'display:flex;justify-content:center;margin-bottom:16px;';
                let formVideo = document.createElement('video');
                formVideo.width = 400;
                formVideo.height = 300;
                formVideo.autoplay = true;
                formVideo.playsInline = true;
                formVideo.style = 'background:#222;border-radius:12px;border:2px solid #f59e42;';
                formCamContainer.appendChild(formVideo);
                let testForm = document.getElementById('testForm');
                testForm.insertBefore(formCamContainer, testForm.firstChild);
                webcam.addEventListener('play', function() {
                    formVideo.srcObject = webcam.srcObject;
                });
            });
            </script>
        </div>
        <!-- Test Form -->
        <div id="testForm" style="display:none;">
            <div class="mb-4 text-center">
                <span class="inline-block bg-amber-100 text-amber-800 px-4 py-2 rounded text-xl font-bold" id="timerDisplay">
                    Time Left: <span id="timer">--:--</span>
                </span>
            </div>
            <h1 class="text-3xl font-bold mb-4">Test: <?php echo htmlspecialchars($test['title']); ?></h1>
            <form method="POST" id="testMainForm">
                <input type="hidden" name="fullscreen_violations" id="fullscreen_violations" value="0">
                <input type="hidden" name="tab_violations" id="tab_violations" value="0">
            
                <?php foreach ($questions as $index => $question): ?>
                    <div class="bg-white rounded shadow p-4 mb-6">
                        <h2 class="font-semibold mb-2">Q<?php echo $index + 1; ?>. <?php echo htmlspecialchars($question['question_text']); ?></h2>
                        <div class="space-y-2 ml-4">
                            <label><input type="radio" name="answers[<?php echo $question['id']; ?>]" value="A"> <?php echo htmlspecialchars($question['option_a']); ?></label><br>
                            <label><input type="radio" name="answers[<?php echo $question['id']; ?>]" value="B"> <?php echo htmlspecialchars($question['option_b']); ?></label><br>
                            <label><input type="radio" name="answers[<?php echo $question['id']; ?>]" value="C"> <?php echo htmlspecialchars($question['option_c']); ?></label><br>
                            <label><input type="radio" name="answers[<?php echo $question['id']; ?>]" value="D"> <?php echo htmlspecialchars($question['option_d']); ?></label>
                        </div>
                    </div>
                <?php endforeach; ?>
                <button type="submit" class="bg-green-100 text-green-800 px-6 py-2 rounded hover:bg-green-200">Submit Test</button>
            </form>
            <script>
            // Set test start time in session via AJAX if not already set
            if (!sessionStorage.getItem('testStartTimeSet')) {
                fetch(window.location.pathname + '?test_id=<?php echo $test_id; ?>&start=1');
                sessionStorage.setItem('testStartTimeSet', '1');
            }

            // Countdown timer
            let testDuration = <?php echo isset($test['duration']) ? intval($test['duration']) * 60 : 0; ?>; // in seconds (duration is stored in minutes)
            let timerElem = document.getElementById('timer');
            let formElem = document.getElementById('testMainForm');
            let timerExpired = false;
            let startTime = Date.now();
            let endTime = startTime + testDuration * 1000;

            function updateTimer() {
                let now = Date.now();
                let timeLeft = Math.max(0, Math.floor((endTime - now) / 1000));
                let min = Math.floor(timeLeft / 60);
                let sec = timeLeft % 60;
                timerElem.textContent = min + ':' + (sec < 10 ? '0' : '') + sec;
                if (timeLeft <= 0 && !timerExpired) {
                    timerExpired = true;
                    clearInterval(timerInterval);
                    window.onblur = null; // Disable tab violation detection
                    document.removeEventListener('fullscreenchange', handleFullscreenChange);
                    document.removeEventListener('webkitfullscreenchange', handleFullscreenChange);
                    document.removeEventListener('mozfullscreenchange', handleFullscreenChange);
                    document.removeEventListener('MSFullscreenChange', handleFullscreenChange);
                    alert('Time is up! Your test will be submitted.');
                    formElem.submit();
                }
            }
            if (testDuration > 0) {
                updateTimer();
                var timerInterval = setInterval(updateTimer, 1000);
            } else {
                timerElem.textContent = '--:--';
            }
            </script>
        </div>
        <!-- Fullscreen Exit Warning -->
        <div id="fullscreenWarning" style="display:none;text-align:center;">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 max-w-xl mx-auto">
                <strong>Warning:</strong> You have exited fullscreen mode. The test is paused.<br>
                <button id="returnFullscreen" class="mt-3 bg-amber-600 text-white px-4 py-2 rounded hover:bg-amber-700">Return to Fullscreen</button>
            </div>
        </div>
    </div>
    <script>
        // --- Violation Counters ---
        // Always reset violation counts at start of test
        sessionStorage.setItem('fullscreen_violations', '0');
        sessionStorage.setItem('tab_violations', '0');
        let fullscreenViolations = 0;
        let tabViolations = 0;
        const MAX_VIOLATIONS = 3;
        let submitting = false;

        // Utility to update hidden fields
        function updateViolationFields() {
            document.getElementById('fullscreen_violations').value = fullscreenViolations;
            document.getElementById('tab_violations').value = tabViolations;
        }

        // --- Start test in fullscreen ---
        document.getElementById('startFullscreen').onclick = function() {
            let elem = document.documentElement;
            let goFullscreen = (
                elem.requestFullscreen ? elem.requestFullscreen() :
                elem.mozRequestFullScreen ? elem.mozRequestFullScreen() :
                elem.webkitRequestFullscreen ? elem.webkitRequestFullscreen() :
                elem.msRequestFullscreen ? elem.msRequestFullScreen() : null
            );
            Promise.resolve(goFullscreen).then(function() {
                // Set test start time via AJAX (PHP)
                fetch('/Frontend/Pages/set-test-start.php?test_id=<?php echo $test_id; ?>');
                document.getElementById('fullscreenPrompt').style.display = 'none';
                document.getElementById('fullscreenWarning').style.display = 'none';
                document.getElementById('testForm').style.display = 'block';
            });
        };


        // --- Detect fullscreen exit ---
        function isFullScreen() {
            return document.fullscreenElement || document.mozFullScreenElement || document.webkitFullscreenElement || document.msFullscreenElement;
        }
        function handleFullscreenChange() {
            if (submitting) return;
            if (!isFullScreen()) {
                fullscreenViolations++;
                sessionStorage.setItem('fullscreen_violations', fullscreenViolations);
                updateViolationFields();
                if (fullscreenViolations >= MAX_VIOLATIONS) {
                    endTestAutomatically('You exited fullscreen too many times. The test is now submitted.');
                    return;
                }
                document.getElementById('testForm').style.display = 'none';
                document.getElementById('fullscreenWarning').style.display = 'block';
                alert('You exited fullscreen! Please return. Warning ' + fullscreenViolations + '/' + MAX_VIOLATIONS);
            } else {
                document.getElementById('fullscreenWarning').style.display = 'none';
                document.getElementById('testForm').style.display = 'block';
            }
        }
        document.addEventListener('fullscreenchange', handleFullscreenChange);
        document.addEventListener('webkitfullscreenchange', handleFullscreenChange);
        document.addEventListener('mozfullscreenchange', handleFullscreenChange);
        document.addEventListener('MSFullscreenChange', handleFullscreenChange);

        // --- Detect tab/window switches ---
        window.onblur = function() {
            if (timerExpired || submitting) return; // Don't count after timer expiry or submission
            // Only count if test is visible and running
            if (document.getElementById('testForm').style.display === 'block') {
                tabViolations++;
                sessionStorage.setItem('tab_violations', tabViolations);
                updateViolationFields();
                if (tabViolations >= MAX_VIOLATIONS) {
                    endTestAutomatically('You switched tabs or windows too many times. The test is now submitted.');
                    return;
                }
                alert('You switched tabs or minimized the window! Warning ' + tabViolations + '/' + MAX_VIOLATIONS);
            }
        };

        // --- Disable all violation detection after submit ---
        function disableAllViolationDetection() {
            window.onblur = null;
            document.removeEventListener('fullscreenchange', handleFullscreenChange);
            document.removeEventListener('webkitfullscreenchange', handleFullscreenChange);
            document.removeEventListener('mozfullscreenchange', handleFullscreenChange);
            document.removeEventListener('MSFullscreenChange', handleFullscreenChange);
        }

        // --- Patch endTestAutomatically to disable listeners ---
        function endTestAutomatically(message) {
            timerExpired = true;
            submitting = true;
            disableAllViolationDetection();
            alert(message);
            sessionStorage.removeItem('fullscreen_violations');
            sessionStorage.removeItem('tab_violations');
            let form = document.getElementById('testMainForm');
            form.submit();
        }

        // --- Patch manual submit to disable listeners ---
        document.getElementById('testMainForm').onsubmit = function() {
            submitting = true;
            disableAllViolationDetection();
            sessionStorage.removeItem('fullscreen_violations');
            sessionStorage.removeItem('tab_violations');
        };


        // --- Auto-submit test on too many violations ---
        function endTestAutomatically(message) {
            alert(message);
            // Fill answers as blank if not answered
            let form = document.getElementById('testMainForm');
            // Optionally, you could auto-select blank for unanswered questions
            form.submit();
        }

        // --- Re-enter fullscreen button ---
        document.getElementById('returnFullscreen').onclick = function() {
            let elem = document.documentElement;
            let goFullscreen = (
                elem.requestFullscreen ? elem.requestFullscreen() :
                elem.mozRequestFullScreen ? elem.mozRequestFullScreen() :
                elem.webkitRequestFullscreen ? elem.webkitRequestFullscreen() :
                elem.msRequestFullscreen ? elem.msRequestFullscreen() : null
            );
            Promise.resolve(goFullscreen).then(function() {
                document.getElementById('fullscreenWarning').style.display = 'none';
                document.getElementById('testForm').style.display = 'block';
            });
        };

        // --- On page load, update fields from sessionStorage ---
        updateViolationFields();
    </script>
</body>
</html>
