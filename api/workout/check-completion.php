<?php
/**
 * Check if workout type was completed today
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

$workoutType = $_GET['workout_type'] ?? '';

if (empty($workoutType)) {
    errorResponse('Workout type is required');
}

$userId = getCurrentUserId();
$db = getDB();

try {
    // Check if this workout type was completed today
    $stmt = $db->prepare("
        SELECT 
            id,
            completed_at,
            xp_earned
        FROM workout_sessions 
        WHERE user_id = ? 
        AND workout_type = ? 
        AND status = 'completed'
        AND DATE(completed_at) = CURDATE()
        ORDER BY completed_at DESC
        LIMIT 1
    ");
    $stmt->execute([$userId, $workoutType]);
    $completion = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($completion) {
        successResponse([
            'completed_today' => true,
            'completed_at' => $completion['completed_at'],
            'xp_earned' => $completion['xp_earned']
        ]);
    } else {
        successResponse([
            'completed_today' => false
        ]);
    }
    
} catch (Exception $e) {
    logError('Check completion error: ' . $e->getMessage());
    errorResponse('Failed to check completion status', 500);
}
