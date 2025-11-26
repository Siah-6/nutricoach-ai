<?php
/**
 * Check if user has completed meal plan today
 */

require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

initSession();

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$userId = getCurrentUserId();
$today = date('Y-m-d');

try {
    $db = getDB();
    
    // Create table if it doesn't exist
    $db->exec("
        CREATE TABLE IF NOT EXISTS meal_plan_completions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            meals_data TEXT NOT NULL,
            total_calories INT DEFAULT 0,
            total_protein INT DEFAULT 0,
            total_carbs INT DEFAULT 0,
            total_fats INT DEFAULT 0,
            completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_date (user_id, completed_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Check if meal plan was completed today
    $stmt = $db->prepare("
        SELECT id, completed_at 
        FROM meal_plan_completions 
        WHERE user_id = ? 
        AND DATE(completed_at) = ? 
        LIMIT 1
    ");
    
    $stmt->execute([$userId, $today]);
    $completion = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($completion) {
        echo json_encode([
            'success' => true,
            'completed_today' => true,
            'completed_at' => $completion['completed_at']
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'completed_today' => false
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Database error in check-meal-plan-completion.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
