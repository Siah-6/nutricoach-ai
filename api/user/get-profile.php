<?php
/**
 * Get User Profile API
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    errorResponse('Unauthorized', 401);
}

$userId = getCurrentUserId();

try {
    $db = getDB();
    
    // Get user profile
    $stmt = $db->prepare("
        SELECT 
            user_id,
            gender,
            fitness_goal,
            fitness_level,
            activity_level,
            age,
            height,
            height_unit,
            weight,
            weight_unit,
            target_weight,
            workout_frequency,
            workout_days,
            bmi,
            bmr,
            daily_calories,
            protein_grams,
            carbs_grams,
            fats_grams,
            onboarding_completed,
            created_at,
            updated_at
        FROM user_profiles 
        WHERE user_id = ?
    ");
    $stmt->execute([$userId]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$profile) {
        errorResponse('Profile not found', 404);
    }
    
    // Decode workout_days JSON
    if ($profile['workout_days']) {
        $profile['workout_days'] = json_decode($profile['workout_days'], true);
    }
    
    successResponse([
        'profile' => $profile
    ]);
    
} catch (Exception $e) {
    logError('Get profile error: ' . $e->getMessage());
    errorResponse('Failed to get profile', 500);
}
