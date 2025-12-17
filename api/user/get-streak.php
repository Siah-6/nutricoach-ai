<?php
/**
 * Get User Workout Streak
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');
initSession();

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

try {
    $db = getDB();
    $userId = getCurrentUserId();
    
    // Get all workout completion dates (distinct dates)
    $stmt = $db->prepare("
        SELECT DISTINCT DATE(completed_at) as workout_date
        FROM workout_sessions
        WHERE user_id = ? AND status = 'completed'
        ORDER BY workout_date DESC
    ");
    $stmt->execute([$userId]);
    $workoutDates = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Calculate streak
    $streak = 0;
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    
    if (count($workoutDates) > 0) {
        $lastWorkout = $workoutDates[0];
        
        // Check if worked out today or yesterday
        if ($lastWorkout === $today || $lastWorkout === $yesterday) {
            $streak = 1;
            $currentDate = $lastWorkout;
            
            // Count consecutive days
            for ($i = 1; $i < count($workoutDates); $i++) {
                $expectedDate = date('Y-m-d', strtotime($currentDate . ' -1 day'));
                
                if ($workoutDates[$i] === $expectedDate) {
                    $streak++;
                    $currentDate = $workoutDates[$i];
                } else {
                    break; // Streak broken
                }
            }
        }
    }
    
    // Get this month's workout count
    $monthStart = date('Y-m-01');
    $monthEnd = date('Y-m-t');
    
    $monthStmt = $db->prepare("
        SELECT COUNT(DISTINCT DATE(completed_at)) as workout_days
        FROM workout_sessions
        WHERE user_id = ? 
        AND status = 'completed'
        AND DATE(completed_at) BETWEEN ? AND ?
    ");
    $monthStmt->execute([$userId, $monthStart, $monthEnd]);
    $monthData = $monthStmt->fetch(PDO::FETCH_ASSOC);
    $workoutDaysThisMonth = $monthData['workout_days'] ?? 0;
    
    // Calculate consistency percentage (assuming goal is 20 days/month)
    $daysInMonth = date('t');
    $targetDays = min(20, $daysInMonth);
    $consistency = min(100, round(($workoutDaysThisMonth / $targetDays) * 100));
    
    echo json_encode([
        'success' => true,
        'streak' => $streak,
        'workoutDaysThisMonth' => $workoutDaysThisMonth,
        'consistency' => $consistency,
        'workoutDates' => $workoutDates
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'streak' => 0,
        'consistency' => 0
    ]);
}
