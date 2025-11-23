<?php
/**
 * Get Current Workout Session
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
    // Get in-progress session
    $stmt = $db->prepare("
        SELECT * FROM workout_sessions 
        WHERE user_id = ? AND status = 'in_progress'
        ORDER BY started_at DESC
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$session) {
        successResponse([
            'has_session' => false,
            'message' => 'No active workout session'
        ]);
        return;
    }
    
    // Get completed exercises for this session
    $stmt = $db->prepare("
        SELECT exercise_name FROM exercise_completions 
        WHERE session_id = ?
    ");
    $stmt->execute([$session['id']]);
    $completedExercises = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Parse workout plan
    $exercises = json_decode($session['workout_plan'], true) ?? [];
    
    successResponse([
        'has_session' => true,
        'session_id' => $session['id'],
        'workout_type' => $session['workout_type'],
        'exercises' => $exercises,
        'completed_exercises' => $completedExercises,
        'started_at' => $session['started_at']
    ]);
    
} catch (Exception $e) {
    logError('Get current session error: ' . $e->getMessage());
    errorResponse('Failed to get current session', 500);
}
