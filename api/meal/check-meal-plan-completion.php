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
    $db = getDBConnection();
    
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
