<?php
/**
 * Complete meal plan for today
 */

require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

initSession();

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$userId = getCurrentUserId();
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['meals']) || !isset($input['completed_meals'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required data']);
    exit;
}

$meals = $input['meals'];
$completedMeals = $input['completed_meals'];

// Calculate total nutrition from completed meals
$totalCalories = 0;
$totalProtein = 0;
$totalCarbs = 0;
$totalFats = 0;

foreach ($completedMeals as $index) {
    if (isset($meals[$index])) {
        $meal = $meals[$index];
        $totalCalories += $meal['calories'] ?? 0;
        $totalProtein += $meal['protein'] ?? 0;
        $totalCarbs += $meal['carbs'] ?? 0;
        $totalFats += $meal['fats'] ?? 0;
    }
}

try {
    $db = getDBConnection();
    
    // Check if already completed today
    $today = date('Y-m-d');
    $stmt = $db->prepare("
        SELECT id FROM meal_plan_completions 
        WHERE user_id = ? AND DATE(completed_at) = ?
    ");
    $stmt->execute([$userId, $today]);
    
    if ($stmt->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'Meal plan already completed today'
        ]);
        exit;
    }
    
    // Insert completion record
    $stmt = $db->prepare("
        INSERT INTO meal_plan_completions 
        (user_id, meals_data, total_calories, total_protein, total_carbs, total_fats, completed_at) 
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $mealsJson = json_encode($meals);
    $stmt->execute([
        $userId,
        $mealsJson,
        $totalCalories,
        $totalProtein,
        $totalCarbs,
        $totalFats
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Meal plan completed successfully',
        'nutrition' => [
            'calories' => $totalCalories,
            'protein' => $totalProtein,
            'carbs' => $totalCarbs,
            'fats' => $totalFats
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in complete-meal-plan.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to complete meal plan'
    ]);
}
