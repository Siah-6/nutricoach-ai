<?php
/**
 * Start Workout Session
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
$workoutType = $data['workout_type'] ?? 'General Workout';
$totalExercises = $data['total_exercises'] ?? 0;
$exercises = $data['exercises'] ?? [];

$userId = getCurrentUserId();
$db = getDB();

try {
    // Check if user has an in-progress session from today
    $stmt = $db->prepare("
        SELECT id FROM workout_sessions 
        WHERE user_id = ? 
        AND status = 'in_progress'
        AND DATE(started_at) = CURDATE()
    ");
    $stmt->execute([$userId]);
    $existingSession = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingSession) {
        // Return existing session instead of creating new one
        successResponse([
            'session_id' => $existingSession['id'],
            'workout_type' => $workoutType,
            'total_exercises' => $totalExercises,
            'message' => 'Continuing your workout! 💪',
            'resumed' => true
        ]);
        return;
    }
    
    // Create new workout session with exercises
    $exercisesJson = json_encode($exercises);
    
    $stmt = $db->prepare("
        INSERT INTO workout_sessions (user_id, workout_type, total_exercises, workout_plan, status)
        VALUES (?, ?, ?, ?, 'in_progress')
    ");
    $stmt->execute([$userId, $workoutType, $totalExercises, $exercisesJson]);
    
    $sessionId = $db->lastInsertId();
    
    successResponse([
        'session_id' => $sessionId,
        'workout_type' => $workoutType,
        'total_exercises' => $totalExercises,
        'exercises' => $exercises,
        'message' => 'Workout session started! 💪'
    ]);
    
} catch (Exception $e) {
    logError('Start session error: ' . $e->getMessage());
    errorResponse('Failed to start workout session', 500);
}
