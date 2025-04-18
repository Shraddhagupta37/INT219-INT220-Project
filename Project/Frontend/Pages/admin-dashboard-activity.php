<?php
// Fetch recent activity for admin dashboard (last 7 actions: test creation, question creation, candidate registration)
$recentActivity = [];
try {
    // Last 3 tests created (by any admin)
    $stmt = $pdo->query("SELECT 'Test Created' as type, title as info, created_at as time FROM tests ORDER BY created_at DESC LIMIT 3");
    $recentActivity = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Last 2 questions created (by any admin)
    $stmt = $pdo->query("SELECT 'Question Added' as type, question_text as info, created_at as time FROM questions ORDER BY created_at DESC LIMIT 2");
    $recentActivity = array_merge($recentActivity, $stmt->fetchAll(PDO::FETCH_ASSOC));
    // Last 2 candidates registered
    $stmt = $pdo->query("SELECT 'Candidate Registered' as type, user_name as info, created_at as time FROM users WHERE role = 'candidate' ORDER BY created_at DESC LIMIT 2");
    $recentActivity = array_merge($recentActivity, $stmt->fetchAll(PDO::FETCH_ASSOC));
    // Sort all by time desc
    usort($recentActivity, function($a, $b) { return strtotime($b['time']) - strtotime($a['time']); });
    $recentActivity = array_slice($recentActivity, 0, 7);
} catch (PDOException $e) {
    $recentActivity = [];
}
?>
