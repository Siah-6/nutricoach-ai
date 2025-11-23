<?php
/**
 * User Onboarding API Endpoint
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
$userId = getCurrentUserId();

// Validate required fields
$required = ['gender', 'fitness_goal', 'fitness_level', 'activity_level', 'age', 'height', 'weight', 'workout_frequency'];
foreach ($required as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        errorResponse("Field '$field' is required");
    }
}

// Sanitize and validate data
$gender = sanitize($data['gender']);
$fitnessGoal = sanitize($data['fitness_goal']);
$fitnessLevel = sanitize($data['fitness_level']);
$activityLevel = sanitize($data['activity_level']);
$age = (int)$data['age'];
$height = (float)$data['height'];
$heightUnit = sanitize($data['height_unit'] ?? 'cm');
$weight = (float)$data['weight'];
$weightUnit = sanitize($data['weight_unit'] ?? 'kg');
$workoutFrequency = (int)$data['workout_frequency'];
$workoutDays = json_encode($data['workout_days'] ?? []);
$targetWeight = isset($data['target_weight']) ? (float)$data['target_weight'] : null;

// Validate enums
$validGenders = ['male', 'female', 'other'];
$validGoals = ['build_muscle', 'lose_weight', 'look_better', 'stay_in_shape'];
$validLevels = ['beginner', 'intermediate', 'advanced'];
$validActivity = ['sedentary', 'lightly_active', 'moderately_active', 'very_active'];

if (!in_array($gender, $validGenders)) {
    errorResponse('Invalid gender');
}
if (!in_array($fitnessGoal, $validGoals)) {
    errorResponse('Invalid fitness goal');
}
if (!in_array($fitnessLevel, $validLevels)) {
    errorResponse('Invalid fitness level');
}
if (!in_array($activityLevel, $validActivity)) {
    errorResponse('Invalid activity level');
}

// Validate numeric ranges
if ($age < 13 || $age > 100) {
    errorResponse('Age must be between 13 and 100');
}
if ($height <= 0 || $weight <= 0) {
    errorResponse('Height and weight must be positive numbers');
}
if ($workoutFrequency < 1 || $workoutFrequency > 7) {
    errorResponse('Workout frequency must be between 1 and 7 days');
}

try {
    $db = getDB();
    
    // Calculate health metrics
    $bmi = calculateBMI($weight, $height, $weightUnit, $heightUnit);
    $bmr = calculateBMR($weight, $height, $age, $gender, $weightUnit, $heightUnit);
    $dailyCalories = calculateDailyCalories($bmr, $activityLevel, $fitnessGoal);
    $macros = calculateMacros($dailyCalories, $fitnessGoal);
    
    // Check if profile exists
    $stmt = $db->prepare("SELECT id FROM user_profiles WHERE user_id = ?");
    $stmt->execute([$userId]);
    $existingProfile = $stmt->fetch();
    
    if ($existingProfile) {
        // Update existing profile
        $stmt = $db->prepare("
            UPDATE user_profiles SET
                gender = ?,
                fitness_goal = ?,
                fitness_level = ?,
                activity_level = ?,
                age = ?,
                height = ?,
                height_unit = ?,
                weight = ?,
                weight_unit = ?,
                workout_frequency = ?,
                workout_days = ?,
                target_weight = ?,
                bmi = ?,
                bmr = ?,
                daily_calories = ?,
                protein_grams = ?,
                carbs_grams = ?,
                fats_grams = ?,
                onboarding_completed = TRUE,
                updated_at = NOW()
            WHERE user_id = ?
        ");
        
        $stmt->execute([
            $gender, $fitnessGoal, $fitnessLevel, $activityLevel,
            $age, $height, $heightUnit, $weight, $weightUnit,
            $workoutFrequency, $workoutDays, $targetWeight,
            $bmi, $bmr, $dailyCalories,
            $macros['protein'], $macros['carbs'], $macros['fats'],
            $userId
        ]);
    } else {
        // Insert new profile
        $stmt = $db->prepare("
            INSERT INTO user_profiles (
                user_id, gender, fitness_goal, fitness_level, activity_level,
                age, height, height_unit, weight, weight_unit,
                workout_frequency, workout_days, target_weight,
                bmi, bmr, daily_calories, protein_grams, carbs_grams, fats_grams,
                onboarding_completed
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, TRUE)
        ");
        
        $stmt->execute([
            $userId, $gender, $fitnessGoal, $fitnessLevel, $activityLevel,
            $age, $height, $heightUnit, $weight, $weightUnit,
            $workoutFrequency, $workoutDays, $targetWeight,
            $bmi, $bmr, $dailyCalories,
            $macros['protein'], $macros['carbs'], $macros['fats']
        ]);
    }
    
    successResponse([
        'bmi' => $bmi,
        'bmr' => $bmr,
        'daily_calories' => $dailyCalories,
        'macros' => $macros
    ], 'Onboarding completed successfully');
    
} catch (Exception $e) {
    logError('Onboarding error: ' . $e->getMessage());
    errorResponse('An error occurred. Please try again later', 500);
}
