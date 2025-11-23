<?php
/**
 * Workout Plan API Endpoint
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    errorResponse('Unauthorized', 401);
}

$userId = getCurrentUserId();
$db = getDB();

// GET - Retrieve workout plan
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $profile = getUserProfile($userId);
        
        if (!$profile) {
            errorResponse('Please complete onboarding first', 400);
        }
        
        // Check if user has an active workout plan
        $stmt = $db->prepare("
            SELECT * FROM workout_plans
            WHERE user_id = ? AND is_active = TRUE
            ORDER BY created_at DESC
            LIMIT 1
        ");
        $stmt->execute([$userId]);
        $plan = $stmt->fetch();
        
        // If no plan exists, generate a default one based on fitness level
        if (!$plan) {
            $exercises = generateWorkoutPlan($profile);
            
            $stmt = $db->prepare("
                INSERT INTO workout_plans (user_id, plan_name, description, exercises)
                VALUES (?, ?, ?, ?)
            ");
            
            $planName = ucfirst($profile['fitness_level']) . " " . str_replace('_', ' ', ucwords($profile['fitness_goal'], '_')) . " Plan";
            $description = "Personalized workout plan based on your fitness level and goals";
            
            $stmt->execute([$userId, $planName, $description, json_encode($exercises)]);
            
            $plan = [
                'id' => $db->lastInsertId(),
                'plan_name' => $planName,
                'description' => $description,
                'exercises' => $exercises,
                'duration_weeks' => 4
            ];
        } else {
            $plan['exercises'] = json_decode($plan['exercises'], true);
        }
        
        successResponse(['plan' => $plan]);
        
    } catch (Exception $e) {
        logError('Get workout plan error: ' . $e->getMessage());
        errorResponse('An error occurred. Please try again later', 500);
    }
}

else {
    errorResponse('Invalid request method', 405);
}

/**
 * Generate workout plan based on user profile
 */
function generateWorkoutPlan($profile) {
    $fitnessLevel = $profile['fitness_level'];
    $fitnessGoal = $profile['fitness_goal'];
    
    $exercises = [];
    
    // Define exercises based on fitness level and goal
    if ($fitnessGoal === 'build_muscle') {
        $exercises = [
            ['day' => 'Monday', 'focus' => 'Chest & Triceps', 'exercises' => [
                ['name' => 'Bench Press', 'sets' => $fitnessLevel === 'beginner' ? 3 : 4, 'reps' => '8-12'],
                ['name' => 'Incline Dumbbell Press', 'sets' => 3, 'reps' => '10-12'],
                ['name' => 'Tricep Dips', 'sets' => 3, 'reps' => '10-15'],
                ['name' => 'Cable Flyes', 'sets' => 3, 'reps' => '12-15']
            ]],
            ['day' => 'Wednesday', 'focus' => 'Back & Biceps', 'exercises' => [
                ['name' => 'Pull-ups', 'sets' => 3, 'reps' => '6-10'],
                ['name' => 'Barbell Rows', 'sets' => 4, 'reps' => '8-12'],
                ['name' => 'Bicep Curls', 'sets' => 3, 'reps' => '10-12'],
                ['name' => 'Lat Pulldowns', 'sets' => 3, 'reps' => '10-12']
            ]],
            ['day' => 'Friday', 'focus' => 'Legs & Shoulders', 'exercises' => [
                ['name' => 'Squats', 'sets' => 4, 'reps' => '8-12'],
                ['name' => 'Leg Press', 'sets' => 3, 'reps' => '10-15'],
                ['name' => 'Shoulder Press', 'sets' => 3, 'reps' => '8-12'],
                ['name' => 'Lateral Raises', 'sets' => 3, 'reps' => '12-15']
            ]]
        ];
    } elseif ($fitnessGoal === 'lose_weight') {
        $exercises = [
            ['day' => 'Monday', 'focus' => 'Full Body HIIT', 'exercises' => [
                ['name' => 'Burpees', 'sets' => 4, 'reps' => '15-20'],
                ['name' => 'Mountain Climbers', 'sets' => 4, 'reps' => '30 seconds'],
                ['name' => 'Jump Squats', 'sets' => 3, 'reps' => '15-20'],
                ['name' => 'High Knees', 'sets' => 3, 'reps' => '30 seconds']
            ]],
            ['day' => 'Wednesday', 'focus' => 'Cardio & Core', 'exercises' => [
                ['name' => 'Running/Jogging', 'sets' => 1, 'reps' => '30 minutes'],
                ['name' => 'Plank', 'sets' => 3, 'reps' => '45-60 seconds'],
                ['name' => 'Russian Twists', 'sets' => 3, 'reps' => '20-30'],
                ['name' => 'Bicycle Crunches', 'sets' => 3, 'reps' => '20-30']
            ]],
            ['day' => 'Friday', 'focus' => 'Circuit Training', 'exercises' => [
                ['name' => 'Jump Rope', 'sets' => 4, 'reps' => '1 minute'],
                ['name' => 'Push-ups', 'sets' => 3, 'reps' => '15-20'],
                ['name' => 'Lunges', 'sets' => 3, 'reps' => '12-15 each leg'],
                ['name' => 'Box Jumps', 'sets' => 3, 'reps' => '10-15']
            ]]
        ];
    } else {
        // General fitness / stay in shape
        $exercises = [
            ['day' => 'Monday', 'focus' => 'Upper Body', 'exercises' => [
                ['name' => 'Push-ups', 'sets' => 3, 'reps' => '12-15'],
                ['name' => 'Dumbbell Rows', 'sets' => 3, 'reps' => '12-15'],
                ['name' => 'Shoulder Press', 'sets' => 3, 'reps' => '10-12'],
                ['name' => 'Plank', 'sets' => 3, 'reps' => '30-45 seconds']
            ]],
            ['day' => 'Wednesday', 'focus' => 'Lower Body', 'exercises' => [
                ['name' => 'Squats', 'sets' => 3, 'reps' => '12-15'],
                ['name' => 'Lunges', 'sets' => 3, 'reps' => '10-12 each leg'],
                ['name' => 'Leg Raises', 'sets' => 3, 'reps' => '15-20'],
                ['name' => 'Calf Raises', 'sets' => 3, 'reps' => '15-20']
            ]],
            ['day' => 'Friday', 'focus' => 'Full Body', 'exercises' => [
                ['name' => 'Burpees', 'sets' => 3, 'reps' => '10-15'],
                ['name' => 'Mountain Climbers', 'sets' => 3, 'reps' => '20-30'],
                ['name' => 'Jumping Jacks', 'sets' => 3, 'reps' => '30-40'],
                ['name' => 'Bicycle Crunches', 'sets' => 3, 'reps' => '20-30']
            ]]
        ];
    }
    
    return $exercises;
}
