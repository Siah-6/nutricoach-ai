<?php
/**
 * Finish Workout Session - Bonus XP
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

$userId = getCurrentUserId();
$db = getDB();

try {
    // Get session info
    $stmt = $db->prepare("
        SELECT * FROM workout_sessions 
        WHERE id = ? AND user_id = ? AND status = 'in_progress'
    ");
    $stmt->execute([$sessionId, $userId]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$session) {
        errorResponse('Session not found or already completed', 404);
    }
    
    // Check if user already completed this workout type today
    $stmt = $db->prepare("
        SELECT COUNT(*) as count FROM workout_sessions 
        WHERE user_id = ? 
        AND workout_type = ? 
        AND status = 'completed'
        AND DATE(completed_at) = CURDATE()
        AND id != ?
    ");
    $stmt->execute([$userId, $session['workout_type'], $sessionId]);
    $completedToday = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($completedToday > 0) {
        // Still mark as completed but don't award XP
        $stmt = $db->prepare("
            UPDATE workout_sessions 
            SET status = 'completed',
                completed_at = NOW(),
                xp_earned = 0
            WHERE id = ?
        ");
        $stmt->execute([$sessionId]);
        
        successResponse([
            'session_completed' => true,
            'already_completed_today' => true,
            'xp_earned' => 0,
            'message' => '✅ Workout logged! You already completed this workout today, so no XP awarded.'
        ]);
        return;
    }
    
    // Bonus XP for completing workout: 50 XP
    $bonusXP = 50;
    
    // Update session as completed
    $stmt = $db->prepare("
        UPDATE workout_sessions 
        SET status = 'completed',
            completed_at = NOW(),
            xp_earned = xp_earned + ?
        WHERE id = ?
    ");
    $stmt->execute([$bonusXP, $sessionId]);
    
    // Initialize XP and level if NULL (important for Hostinger/production)
    $stmt = $db->prepare("UPDATE users SET xp = COALESCE(xp, 0), level = COALESCE(level, 1) WHERE id = ? AND (xp IS NULL OR level IS NULL)");
    $stmt->execute([$userId]);
    
    // Add bonus XP to user
    $stmt = $db->prepare("UPDATE users SET xp = COALESCE(xp, 0) + ? WHERE id = ?");
    $stmt->execute([$bonusXP, $userId]);
    
    // Get updated user stats
    $stmt = $db->prepare("SELECT xp, level FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Check for level up
    $newLevel = floor($user['xp'] / 100) + 1;
    $leveledUp = false;
    
    if ($newLevel > $user['level']) {
        $stmt = $db->prepare("UPDATE users SET level = ? WHERE id = ?");
        $stmt->execute([$newLevel, $userId]);
        $leveledUp = true;
    }
    
    // Check for achievements
    $achievements = [];
    
    // First workout achievement
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM workout_sessions WHERE user_id = ? AND status = 'completed'");
    $stmt->execute([$userId]);
    $workoutCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($workoutCount == 1) {
        $stmt = $db->prepare("
            INSERT INTO achievements (user_id, achievement_type, achievement_name, description, xp_reward)
            VALUES (?, 'first_workout', 'First Steps', 'Completed your first workout!', 100)
        ");
        $stmt->execute([$userId]);
        $achievements[] = ['name' => 'First Steps', 'xp' => 100];
        
        // Add achievement XP
        $stmt = $db->prepare("UPDATE users SET xp = xp + 100 WHERE id = ?");
        $stmt->execute([$userId]);
    }
    
    // 10 workouts achievement
    if ($workoutCount == 10) {
        $stmt = $db->prepare("
            INSERT INTO achievements (user_id, achievement_type, achievement_name, description, xp_reward)
            VALUES (?, '10_workouts', 'Dedicated', 'Completed 10 workouts!', 200)
        ");
        $stmt->execute([$userId]);
        $achievements[] = ['name' => 'Dedicated', 'xp' => 200];
        
        $stmt = $db->prepare("UPDATE users SET xp = xp + 200 WHERE id = ?");
        $stmt->execute([$userId]);
    }
    
    $totalXPEarned = $session['xp_earned'] + $bonusXP + array_sum(array_column($achievements, 'xp'));
    
    successResponse([
        'session_completed' => true,
        'exercises_completed' => $session['completed_exercises'],
        'total_xp_earned' => $totalXPEarned,
        'bonus_xp' => $bonusXP,
        'level' => $newLevel,
        'leveled_up' => $leveledUp,
        'achievements' => $achievements,
        'message' => '🎉 Workout Complete! +' . $totalXPEarned . ' XP!'
    ]);
    
} catch (Exception $e) {
    logError('Finish session error: ' . $e->getMessage());
    errorResponse('Failed to finish workout session', 500);
}
