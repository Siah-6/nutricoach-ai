<?php
/**
 * Meal Plan API Endpoint
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    errorResponse('Unauthorized', 401);
}

$userId = getCurrentUserId();
$db = getDB();

// GET - Retrieve meal plan
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $profile = getUserProfile($userId);
        
        if (!$profile) {
            errorResponse('Please complete onboarding first', 400);
        }
        
        $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
        
        // Check if meal plan exists for the date
        $stmt = $db->prepare("
            SELECT * FROM meal_plans
            WHERE user_id = ? AND date = ?
            ORDER BY FIELD(meal_type, 'breakfast', 'lunch', 'dinner', 'snack')
        ");
        $stmt->execute([$userId, $date]);
        $meals = $stmt->fetchAll();
        
        // If no meal plan exists, generate a default one
        if (empty($meals)) {
            $meals = generateMealPlan($userId, $profile, $date);
        }
        
        // Calculate totals
        $totals = [
            'calories' => 0,
            'protein' => 0,
            'carbs' => 0,
            'fats' => 0
        ];
        
        foreach ($meals as $meal) {
            $totals['calories'] += $meal['calories'];
            $totals['protein'] += $meal['protein'];
            $totals['carbs'] += $meal['carbs'];
            $totals['fats'] += $meal['fats'];
        }
        
        successResponse([
            'date' => $date,
            'meals' => $meals,
            'totals' => $totals,
            'targets' => [
                'calories' => $profile['daily_calories'],
                'protein' => $profile['protein_grams'],
                'carbs' => $profile['carbs_grams'],
                'fats' => $profile['fats_grams']
            ]
        ]);
        
    } catch (Exception $e) {
        logError('Get meal plan error: ' . $e->getMessage());
        errorResponse('An error occurred. Please try again later', 500);
    }
}

else {
    errorResponse('Invalid request method', 405);
}

/**
 * Generate meal plan based on user profile
 */
function generateMealPlan($userId, $profile, $date) {
    $db = getDB();
    $fitnessGoal = $profile['fitness_goal'];
    $dailyCalories = $profile['daily_calories'];
    
    // Distribute calories across meals (breakfast 25%, lunch 35%, dinner 30%, snack 10%)
    $breakfastCal = round($dailyCalories * 0.25);
    $lunchCal = round($dailyCalories * 0.35);
    $dinnerCal = round($dailyCalories * 0.30);
    $snackCal = round($dailyCalories * 0.10);
    
    $meals = [];
    
    // Breakfast
    if ($fitnessGoal === 'build_muscle') {
        $meals[] = [
            'meal_type' => 'breakfast',
            'meal_name' => 'Protein-Packed Breakfast',
            'description' => '4 egg whites, 2 whole eggs, oatmeal with berries, and a protein shake',
            'calories' => $breakfastCal,
            'protein' => round($breakfastCal * 0.30 / 4),
            'carbs' => round($breakfastCal * 0.45 / 4),
            'fats' => round($breakfastCal * 0.25 / 9)
        ];
    } elseif ($fitnessGoal === 'lose_weight') {
        $meals[] = [
            'meal_type' => 'breakfast',
            'meal_name' => 'Light & Nutritious Breakfast',
            'description' => 'Greek yogurt with berries, handful of almonds, and green tea',
            'calories' => $breakfastCal,
            'protein' => round($breakfastCal * 0.35 / 4),
            'carbs' => round($breakfastCal * 0.30 / 4),
            'fats' => round($breakfastCal * 0.35 / 9)
        ];
    } else {
        $meals[] = [
            'meal_type' => 'breakfast',
            'meal_name' => 'Balanced Breakfast',
            'description' => 'Scrambled eggs with whole grain toast, avocado, and orange juice',
            'calories' => $breakfastCal,
            'protein' => round($breakfastCal * 0.25 / 4),
            'carbs' => round($breakfastCal * 0.45 / 4),
            'fats' => round($breakfastCal * 0.30 / 9)
        ];
    }
    
    // Lunch
    if ($fitnessGoal === 'build_muscle') {
        $meals[] = [
            'meal_type' => 'lunch',
            'meal_name' => 'High-Protein Lunch',
            'description' => 'Grilled chicken breast, brown rice, steamed broccoli, and sweet potato',
            'calories' => $lunchCal,
            'protein' => round($lunchCal * 0.30 / 4),
            'carbs' => round($lunchCal * 0.45 / 4),
            'fats' => round($lunchCal * 0.25 / 9)
        ];
    } elseif ($fitnessGoal === 'lose_weight') {
        $meals[] = [
            'meal_type' => 'lunch',
            'meal_name' => 'Lean & Green Lunch',
            'description' => 'Grilled salmon, large mixed salad with olive oil dressing, quinoa',
            'calories' => $lunchCal,
            'protein' => round($lunchCal * 0.35 / 4),
            'carbs' => round($lunchCal * 0.30 / 4),
            'fats' => round($lunchCal * 0.35 / 9)
        ];
    } else {
        $meals[] = [
            'meal_type' => 'lunch',
            'meal_name' => 'Balanced Lunch',
            'description' => 'Turkey sandwich on whole grain bread, side salad, and fruit',
            'calories' => $lunchCal,
            'protein' => round($lunchCal * 0.25 / 4),
            'carbs' => round($lunchCal * 0.45 / 4),
            'fats' => round($lunchCal * 0.30 / 9)
        ];
    }
    
    // Dinner
    if ($fitnessGoal === 'build_muscle') {
        $meals[] = [
            'meal_type' => 'dinner',
            'meal_name' => 'Muscle-Building Dinner',
            'description' => 'Lean beef steak, roasted vegetables, baked potato, and side salad',
            'calories' => $dinnerCal,
            'protein' => round($dinnerCal * 0.30 / 4),
            'carbs' => round($dinnerCal * 0.45 / 4),
            'fats' => round($dinnerCal * 0.25 / 9)
        ];
    } elseif ($fitnessGoal === 'lose_weight') {
        $meals[] = [
            'meal_type' => 'dinner',
            'meal_name' => 'Light Dinner',
            'description' => 'Grilled chicken, cauliflower rice, asparagus, and mixed greens',
            'calories' => $dinnerCal,
            'protein' => round($dinnerCal * 0.35 / 4),
            'carbs' => round($dinnerCal * 0.30 / 4),
            'fats' => round($dinnerCal * 0.35 / 9)
        ];
    } else {
        $meals[] = [
            'meal_type' => 'dinner',
            'meal_name' => 'Balanced Dinner',
            'description' => 'Baked fish, wild rice, roasted vegetables, and garden salad',
            'calories' => $dinnerCal,
            'protein' => round($dinnerCal * 0.25 / 4),
            'carbs' => round($dinnerCal * 0.45 / 4),
            'fats' => round($dinnerCal * 0.30 / 9)
        ];
    }
    
    // Snack
    $meals[] = [
        'meal_type' => 'snack',
        'meal_name' => 'Healthy Snack',
        'description' => 'Protein bar, handful of nuts, or fruit with nut butter',
        'calories' => $snackCal,
        'protein' => round($snackCal * 0.25 / 4),
        'carbs' => round($snackCal * 0.40 / 4),
        'fats' => round($snackCal * 0.35 / 9)
    ];
    
    // Insert meals into database
    $stmt = $db->prepare("
        INSERT INTO meal_plans (user_id, date, meal_type, meal_name, description, calories, protein, carbs, fats)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    foreach ($meals as &$meal) {
        $stmt->execute([
            $userId,
            $date,
            $meal['meal_type'],
            $meal['meal_name'],
            $meal['description'],
            $meal['calories'],
            $meal['protein'],
            $meal['carbs'],
            $meal['fats']
        ]);
        $meal['id'] = $db->lastInsertId();
        $meal['date'] = $date;
    }
    
    return $meals;
}
