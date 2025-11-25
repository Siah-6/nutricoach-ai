<?php
/**
 * Log Meal API
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
$mealType = $data['meal_type'] ?? '';
$foods = $data['foods'] ?? [];
$calories = intval($data['calories'] ?? 0);
$carbs = floatval($data['carbs'] ?? 0);
$protein = floatval($data['protein'] ?? 0);
$fats = floatval($data['fats'] ?? 0);
$date = $data['date'] ?? date('Y-m-d');

if (empty($mealType) || empty($foods)) {
    errorResponse('Meal type and foods are required');
}

$userId = getCurrentUserId();
$db = getDB();

try {
    // Check if meal already exists for this date
    $stmt = $db->prepare("
        SELECT id FROM meal_logs 
        WHERE user_id = ? AND meal_type = ? AND DATE(logged_at) = ?
    ");
    $stmt->execute([$userId, $mealType, $date]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        // Update existing meal
        $stmt = $db->prepare("
            UPDATE meal_logs 
            SET foods = ?, calories = ?, carbs = ?, protein = ?, fats = ?, logged_at = ?
            WHERE id = ?
        ");
        $stmt->execute([
            json_encode($foods),
            $calories,
            $carbs,
            $protein,
            $fats,
            $date . ' ' . date('H:i:s'),
            $existing['id']
        ]);
        $mealId = $existing['id'];
    } else {
        // Insert new meal
        $stmt = $db->prepare("
            INSERT INTO meal_logs (user_id, meal_type, foods, calories, carbs, protein, fats, logged_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            $mealType,
            json_encode($foods),
            $calories,
            $carbs,
            $protein,
            $fats,
            $date . ' ' . date('H:i:s')
        ]);
        $mealId = $db->lastInsertId();
    }
    
    successResponse([
        'meal_id' => $mealId,
        'message' => 'Meal logged successfully'
    ]);
    
} catch (Exception $e) {
    logError('Log meal error: ' . $e->getMessage());
    errorResponse('Failed to log meal', 500);
}
