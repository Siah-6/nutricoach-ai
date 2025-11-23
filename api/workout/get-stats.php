<?php
/**
 * Get User XP and Level Stats
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    errorResponse('Invalid request method', 405);
}

if (!isLoggedIn()) {
    errorResponse('Unauthorized', 401);
}

$userId = getCurrentUserId();
$db = getDB();

try {
    // Get user stats
    $stmt = $db->prepare("SELECT xp, level FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        // Initialize XP and level if not set
        $stmt = $db->prepare("UPDATE users SET xp = 0, level = 1 WHERE id = ?");
        $stmt->execute([$userId]);
        $user = ['xp' => 0, 'level' => 1];
    }
    
    // Calculate progress to next level
    $currentLevel = $user['level'];
    $xpForCurrentLevel = ($currentLevel - 1) * 100;
    $xpForNextLevel = $currentLevel * 100;
    $xpProgress = $user['xp'] - $xpForCurrentLevel;
    $xpNeeded = $xpForNextLevel - $xpForCurrentLevel;
    $progressPercent = ($xpProgress / $xpNeeded) * 100;
    
    // Get workout stats
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM workout_sessions WHERE user_id = ? AND status = 'completed'");
    $stmt->execute([$userId]);
    $totalWorkouts = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM exercise_completions WHERE user_id = ?");
    $stmt->execute([$userId]);
    $totalExercises = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM achievements WHERE user_id = ?");
    $stmt->execute([$userId]);
    $totalAchievements = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    successResponse([
        'xp' => $user['xp'],
        'level' => $currentLevel,
        'xp_progress' => $xpProgress,
        'xp_needed' => $xpNeeded,
        'progress_percent' => round($progressPercent, 1),
        'total_workouts' => $totalWorkouts,
        'total_exercises' => $totalExercises,
        'total_achievements' => $totalAchievements
    ]);
    
} catch (Exception $e) {
    logError('Get stats error: ' . $e->getMessage());
    errorResponse('Failed to get stats', 500);
}
