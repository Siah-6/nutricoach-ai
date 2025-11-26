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
    
    // Initialize XP and level if null or missing
    if (!$user || $user['xp'] === null || $user['level'] === null) {
        $stmt = $db->prepare("UPDATE users SET xp = COALESCE(xp, 0), level = COALESCE(level, 1) WHERE id = ?");
        $stmt->execute([$userId]);
        
        // Re-fetch updated values
        $stmt = $db->prepare("SELECT xp, level FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Ensure we have valid values (fallback to defaults)
    $currentXp = intval($user['xp'] ?? 0);
    $currentLevel = intval($user['level'] ?? 1);
    
    // Auto-calculate correct level based on XP (100 XP per level)
    $calculatedLevel = floor($currentXp / 100) + 1;
    
    // Update level in database if it's wrong
    if ($calculatedLevel != $currentLevel) {
        $stmt = $db->prepare("UPDATE users SET level = ? WHERE id = ?");
        $stmt->execute([$calculatedLevel, $userId]);
        $currentLevel = $calculatedLevel;
    }
    
    // Calculate progress to next level
    $xpForCurrentLevel = ($currentLevel - 1) * 100;
    $xpForNextLevel = $currentLevel * 100;
    $xpProgress = $currentXp - $xpForCurrentLevel;
    $xpNeeded = $xpForNextLevel - $xpForCurrentLevel;
    $progressPercent = ($xpProgress / $xpNeeded) * 100;
    
    // Check if user reached a milestone (every 5 levels)
    $isMilestone = ($currentLevel % 5 == 0);
    $nextMilestone = ceil($currentLevel / 5) * 5;
    
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
        'xp' => $currentXp,
        'level' => $currentLevel,
        'xp_progress' => $xpProgress,
        'xp_needed' => $xpNeeded,
        'progress_percent' => round($progressPercent, 1),
        'total_workouts' => $totalWorkouts,
        'total_exercises' => $totalExercises,
        'total_achievements' => $totalAchievements,
        'is_milestone' => $isMilestone,
        'next_milestone' => $nextMilestone
    ]);
    
} catch (Exception $e) {
    logError('Get stats error: ' . $e->getMessage());
    errorResponse('Failed to get stats', 500);
}
