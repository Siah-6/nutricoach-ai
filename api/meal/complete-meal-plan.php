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
    $db = getDB();
    
    // Create table if it doesn't exist - suppress errors if table already exists
    try {
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
    } catch (PDOException $tableError) {
        // Table might already exist, continue
        error_log("Table creation warning: " . $tableError->getMessage());
    }
    
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
    
    // Also log each completed meal to meal_logs so it appears in the tracker
    $mealTypeMap = [
        'Breakfast' => 'breakfast',
        'Lunch' => 'lunch',
        'Dinner' => 'dinner',
        'Snack' => 'afternoon-snack'
    ];
    
    $mealsLogged = 0;
    foreach ($completedMeals as $index) {
        if (isset($meals[$index])) {
            $meal = $meals[$index];
            $mealType = $mealTypeMap[$meal['type']] ?? 'lunch';
            
            // Create foods array for the meal
            $foods = [[
                'name' => $meal['name'],
                'calories' => $meal['calories'] ?? 0,
                'protein' => $meal['protein'] ?? 0,
                'carbs' => $meal['carbs'] ?? 0,
                'fats' => $meal['fats'] ?? 0,
                'serving' => '1 serving'
            ]];
            
            // Insert into meal_logs
            $logStmt = $db->prepare("
                INSERT INTO meal_logs 
                (user_id, meal_type, foods, calories, carbs, protein, fats, logged_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $logStmt->execute([
                $userId,
                $mealType,
                json_encode($foods),
                $meal['calories'] ?? 0,
                $meal['carbs'] ?? 0,
                $meal['protein'] ?? 0,
                $meal['fats'] ?? 0
            ]);
            
            $mealsLogged++;
        }
    }
    
    // Add xp and level columns if they don't exist
    try {
        $db->exec("ALTER TABLE users ADD COLUMN xp INT DEFAULT 0");
    } catch (PDOException $e) {
        // Column already exists, continue
    }
    try {
        $db->exec("ALTER TABLE users ADD COLUMN level INT DEFAULT 1");
    } catch (PDOException $e) {
        // Column already exists, continue
    }
    
    // Award EXP for completing meal plan (50 EXP + 10 per meal logged)
    $expGained = 50 + ($mealsLogged * 10);
    $updateExp = $db->prepare("UPDATE users SET xp = xp + ? WHERE id = ?");
    $updateExp->execute([$expGained, $userId]);
    
    // Check if user leveled up
    $stmt = $db->prepare("SELECT xp, level FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    $newXp = $userData['xp'];
    $currentLevel = $userData['level'];
    $xpForNextLevel = $currentLevel * 100;
    
    if ($newXp >= $xpForNextLevel) {
        $newLevel = $currentLevel + 1;
        $db->prepare("UPDATE users SET level = ? WHERE id = ?")->execute([$newLevel, $userId]);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Meal plan completed successfully',
        'exp_gained' => $expGained,
        'meals_logged' => $mealsLogged,
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
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
