<?php
/**
 * User Profile API Endpoint
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check authentication
if (!isLoggedIn()) {
    errorResponse('Unauthorized', 401);
}

$userId = getCurrentUserId();
$db = getDB();

// GET - Retrieve user profile
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Allow CORS preflight / non-mutating checks
    successResponse([], 'OK');
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Get user data
        $stmt = $db->prepare("SELECT id, name, email, created_at, last_login FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user) {
            errorResponse('User not found', 404);
        }
        
        // Get profile data
        $stmt = $db->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
        $stmt->execute([$userId]);
        $profile = $stmt->fetch();
        
        $response = [
            'user' => $user,
            'profile' => $profile
        ];
        
        successResponse($response);
        
    } catch (Exception $e) {
        logError('Get profile error: ' . $e->getMessage());
        errorResponse('An error occurred. Please try again later', 500);
    }
}

// PUT - Update user profile
elseif (in_array($_SERVER['REQUEST_METHOD'], ['PUT', 'POST'])) {
    // Support JSON payloads and regular form posts
    $data = json_decode(file_get_contents('php://input'), true);
    if (!is_array($data)) {
        $data = [];
    }
    if (empty($data) && !empty($_POST)) {
        $data = $_POST;
    }

    try {
        $db->beginTransaction();

        // Update user basic info if provided
        if (isset($data['name']) && !empty($data['name'])) {
            $name = sanitize($data['name']);
            $stmt = $db->prepare("UPDATE users SET name = ? WHERE id = ?");
            $stmt->execute([$name, $userId]);
            $_SESSION['user_name'] = $name;
        }

        if (isset($data['email']) && !empty($data['email'])) {
            $email = sanitize($data['email']);

            if (!isValidEmail($email)) {
                $db->rollBack();
                errorResponse('Invalid email address');
            }

            // Check if email is already taken by another user
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $userId]);

            if ($stmt->fetch()) {
                $db->rollBack();
                errorResponse('Email already in use');
            }

            $stmt = $db->prepare("UPDATE users SET email = ? WHERE id = ?");
            $stmt->execute([$email, $userId]);
            $_SESSION['user_email'] = $email;
        }

        // Update password if provided
        if (isset($data['current_password']) && isset($data['new_password'])) {
            $currentPassword = $data['current_password'];
            $newPassword = $data['new_password'];

            // Verify current password
            $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            if (!verifyPassword($currentPassword, $user['password'])) {
                $db->rollBack();
                errorResponse('Current password is incorrect');
            }

            if (strlen($newPassword) < 8) {
                $db->rollBack();
                errorResponse('New password must be at least 8 characters long');
            }

            $hashedPassword = hashPassword($newPassword);
            $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $userId]);
        }

        // Update fitness profile fields if provided
        $fitnessFields = ['fitness_goal', 'fitness_level', 'activity_level', 'height', 'height_unit'];
        $hasFitnessUpdate = false;
        foreach ($fitnessFields as $field) {
            if (isset($data[$field]) && $data[$field] !== '') {
                $hasFitnessUpdate = true;
                break;
            }
        }

        if ($hasFitnessUpdate) {
            $allowedGoals = ['build_muscle', 'lose_weight', 'look_better', 'stay_in_shape'];
            $allowedLevels = ['beginner', 'intermediate', 'advanced'];
            $allowedActivity = ['sedentary', 'lightly_active', 'moderately_active', 'very_active'];
            $allowedHeightUnits = ['cm', 'ft'];

            $stmt = $db->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
            $stmt->execute([$userId]);
            $profile = $stmt->fetch();

            if (!$profile) {
                $db->rollBack();
                errorResponse('Fitness profile not found');
            }

            $fitnessGoal = $data['fitness_goal'] ?? $profile['fitness_goal'];
            if (!in_array($fitnessGoal, $allowedGoals)) {
                $db->rollBack();
                errorResponse('Invalid fitness goal');
            }

            $fitnessLevel = $data['fitness_level'] ?? $profile['fitness_level'];
            if (!in_array($fitnessLevel, $allowedLevels)) {
                $db->rollBack();
                errorResponse('Invalid fitness level');
            }

            $activityLevel = $data['activity_level'] ?? $profile['activity_level'];
            if (!in_array($activityLevel, $allowedActivity)) {
                $db->rollBack();
                errorResponse('Invalid activity level');
            }

            $height = isset($data['height']) && $data['height'] !== '' ? (float)$data['height'] : (float)$profile['height'];
            if ($height <= 0) {
                $db->rollBack();
                errorResponse('Height must be greater than 0');
            }

            $heightUnit = $data['height_unit'] ?? $profile['height_unit'];
            if (!in_array($heightUnit, $allowedHeightUnits)) {
                $db->rollBack();
                errorResponse('Invalid height unit');
            }

            // Use existing measurements for dependent calculations
            $weight = (float)$profile['weight'];
            $weightUnit = $profile['weight_unit'];
            $age = (int)$profile['age'];
            $gender = $profile['gender'];

            $bmi = calculateBMI($weight, $height, $weightUnit, $heightUnit);
            $bmr = calculateBMR($weight, $height, $age, $gender, $weightUnit, $heightUnit);
            $dailyCalories = calculateDailyCalories($bmr, $activityLevel, $fitnessGoal);
            $macros = calculateMacros($dailyCalories, $fitnessGoal);

            $stmt = $db->prepare("
                UPDATE user_profiles SET
                    fitness_goal = ?,
                    fitness_level = ?,
                    activity_level = ?,
                    height = ?,
                    height_unit = ?,
                    bmi = ?,
                    bmr = ?,
                    daily_calories = ?,
                    protein_grams = ?,
                    carbs_grams = ?,
                    fats_grams = ?,
                    updated_at = NOW()
                WHERE user_id = ?
            ");

            $stmt->execute([
                $fitnessGoal,
                $fitnessLevel,
                $activityLevel,
                $height,
                $heightUnit,
                $bmi,
                $bmr,
                $dailyCalories,
                $macros['protein'],
                $macros['carbs'],
                $macros['fats'],
                $userId
            ]);
        }

        $db->commit();

        successResponse([], 'Profile updated successfully');

    } catch (Exception $e) {
        $db->rollBack();
        logError('Update profile error: ' . $e->getMessage());
        errorResponse('An error occurred. Please try again later', 500);
    }
}

else {
    errorResponse('Invalid request method', 405);
}
