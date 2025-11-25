<?php
/**
 * Get Today's Meals API
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

// Get date from query parameter or use today
$date = $_GET['date'] ?? date('Y-m-d');

try {
    // Get meals for specified date
    $stmt = $db->prepare("
        SELECT 
            id,
            meal_type,
            foods,
            calories,
            carbs,
            protein,
            fats,
            logged_at
        FROM meal_logs 
        WHERE user_id = ? AND DATE(logged_at) = ?
        ORDER BY logged_at ASC
    ");
    $stmt->execute([$userId, $date]);
    $meals = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Parse foods JSON
    foreach ($meals as &$meal) {
        $meal['foods'] = json_decode($meal['foods'], true);
    }
    
    successResponse([
        'meals' => $meals,
        'count' => count($meals)
    ]);
    
} catch (Exception $e) {
    logError('Get meals error: ' . $e->getMessage());
    errorResponse('Failed to get meals', 500);
}
