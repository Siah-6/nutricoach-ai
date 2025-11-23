<?php
/**
 * Progress Tracking API Endpoint
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    errorResponse('Unauthorized', 401);
}

$userId = getCurrentUserId();
$db = getDB();

// GET - Retrieve progress data
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $days = isset($_GET['days']) ? (int)$_GET['days'] : 30;
        $days = min($days, 365); // Max 1 year
        
        $stmt = $db->prepare("
            SELECT *
            FROM progress_logs
            WHERE user_id = ? AND log_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
            ORDER BY log_date ASC
        ");
        $stmt->execute([$userId, $days]);
        $logs = $stmt->fetchAll();
        
        // Get user profile for comparison
        $profile = getUserProfile($userId);
        
        successResponse([
            'logs' => $logs,
            'profile' => $profile,
            'period_days' => $days
        ]);
        
    } catch (Exception $e) {
        logError('Get progress error: ' . $e->getMessage());
        errorResponse('An error occurred. Please try again later', 500);
    }
}

// POST - Log progress
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        $logDate = isset($data['log_date']) ? $data['log_date'] : date('Y-m-d');
        $weight = isset($data['weight']) ? (float)$data['weight'] : null;
        $bodyFat = isset($data['body_fat_percentage']) ? (float)$data['body_fat_percentage'] : null;
        $muscleMass = isset($data['muscle_mass']) ? (float)$data['muscle_mass'] : null;
        $caloriesConsumed = isset($data['calories_consumed']) ? (int)$data['calories_consumed'] : null;
        $caloriesBurned = isset($data['calories_burned']) ? (int)$data['calories_burned'] : null;
        $workoutCompleted = isset($data['workout_completed']) ? (bool)$data['workout_completed'] : false;
        $workoutDuration = isset($data['workout_duration']) ? (int)$data['workout_duration'] : null;
        $notes = isset($data['notes']) ? sanitize($data['notes']) : null;
        
        // Check if log exists for this date
        $stmt = $db->prepare("SELECT id FROM progress_logs WHERE user_id = ? AND log_date = ?");
        $stmt->execute([$userId, $logDate]);
        $existingLog = $stmt->fetch();
        
        if ($existingLog) {
            // Update existing log
            $stmt = $db->prepare("
                UPDATE progress_logs SET
                    weight = COALESCE(?, weight),
                    body_fat_percentage = COALESCE(?, body_fat_percentage),
                    muscle_mass = COALESCE(?, muscle_mass),
                    calories_consumed = COALESCE(?, calories_consumed),
                    calories_burned = COALESCE(?, calories_burned),
                    workout_completed = ?,
                    workout_duration = COALESCE(?, workout_duration),
                    notes = COALESCE(?, notes)
                WHERE id = ?
            ");
            
            $stmt->execute([
                $weight, $bodyFat, $muscleMass,
                $caloriesConsumed, $caloriesBurned,
                $workoutCompleted, $workoutDuration,
                $notes, $existingLog['id']
            ]);
            
            $message = 'Progress updated successfully';
        } else {
            // Insert new log
            $stmt = $db->prepare("
                INSERT INTO progress_logs (
                    user_id, log_date, weight, body_fat_percentage, muscle_mass,
                    calories_consumed, calories_burned, workout_completed,
                    workout_duration, notes
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $userId, $logDate, $weight, $bodyFat, $muscleMass,
                $caloriesConsumed, $caloriesBurned, $workoutCompleted,
                $workoutDuration, $notes
            ]);
            
            $message = 'Progress logged successfully';
        }
        
        successResponse([], $message);
        
    } catch (Exception $e) {
        logError('Log progress error: ' . $e->getMessage());
        errorResponse('An error occurred. Please try again later', 500);
    }
}

else {
    errorResponse('Invalid request method', 405);
}
