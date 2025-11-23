<?php
/**
 * Complete Exercise - Award XP
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Invalid request method', 405);
}

if (!isLoggedIn()) {
    errorResponse('Unauthorized', 401);
}

$data = json_decode(file_get_contents('php://input'), true);
$sessionId = $data['session_id'] ?? 0;
$exerciseName = $data['exercise_name'] ?? '';
$sets = $data['sets'] ?? 0;
$reps = $data['reps'] ?? 0;

$userId = getCurrentUserId();
$db = getDB();

try {
    // XP calculation: 10 XP per exercise
    $xpEarned = 10;
    
    // Record exercise completion
    $stmt = $db->prepare("
        INSERT INTO exercise_completions (session_id, user_id, exercise_name, sets_completed, reps_completed, xp_earned)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$sessionId, $userId, $exerciseName, $sets, $reps, $xpEarned]);
    
    // Update session progress
    $stmt = $db->prepare("
        UPDATE workout_sessions 
        SET completed_exercises = completed_exercises + 1,
            xp_earned = xp_earned + ?
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$xpEarned, $sessionId, $userId]);
    
    // Add XP to user
    $stmt = $db->prepare("UPDATE users SET xp = xp + ? WHERE id = ?");
    $stmt->execute([$xpEarned, $userId]);
    
    // Get updated user stats
    $stmt = $db->prepare("SELECT xp, level FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Check for level up (every 100 XP = 1 level)
    $newLevel = floor($user['xp'] / 100) + 1;
    $leveledUp = false;
    
    if ($newLevel > $user['level']) {
        $stmt = $db->prepare("UPDATE users SET level = ? WHERE id = ?");
        $stmt->execute([$newLevel, $userId]);
        $leveledUp = true;
    }
    
    // Calculate progress to next level
    $xpForCurrentLevel = ($newLevel - 1) * 100;
    $xpForNextLevel = $newLevel * 100;
    $xpProgress = $user['xp'] - $xpForCurrentLevel;
    $xpNeeded = $xpForNextLevel - $xpForCurrentLevel;
    $progressPercent = ($xpProgress / $xpNeeded) * 100;
    
    successResponse([
        'xp_earned' => $xpEarned,
        'total_xp' => $user['xp'],
        'level' => $newLevel,
        'leveled_up' => $leveledUp,
        'xp_progress' => $xpProgress,
        'xp_needed' => $xpNeeded,
        'progress_percent' => round($progressPercent, 1),
        'message' => $leveledUp ? '🎉 LEVEL UP! You\'re now level ' . $newLevel . '!' : '💪 +' . $xpEarned . ' XP!'
    ]);
    
} catch (Exception $e) {
    logError('Complete exercise error: ' . $e->getMessage());
    errorResponse('Failed to complete exercise', 500);
}
