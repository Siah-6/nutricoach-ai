<?php
/**
 * Common Helper Functions
 */

// Load config if not already loaded
if (!defined('SESSION_NAME')) {
    require_once __DIR__ . '/../config/config.php';
}

// Load database functions if not already loaded
if (!function_exists('getDB')) {
    require_once __DIR__ . '/../config/database.php';
}

// Start session if not already started
function initSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_start();
    }
}

// Check if user is logged in
function isLoggedIn() {
    initSession();
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Get current user ID
function getCurrentUserId() {
    initSession();
    return $_SESSION['user_id'] ?? null;
}

// Get current user data
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $db = getDB();
    $stmt = $db->prepare("SELECT id, name, email, created_at FROM users WHERE id = ?");
    $stmt->execute([getCurrentUserId()]);
    return $stmt->fetch();
}

// Redirect to a page
function redirect($url) {
    // Add XAMPP path prefix if not already present
    if (strpos($url, '/xampp/NutriCoachAI') === false && strpos($url, 'http') === false) {
        $url = '/xampp/NutriCoachAI' . $url;
    }
    header("Location: " . $url);
    exit();
}

// Sanitize input
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Hash password
function hashPassword($password) {
    return password_hash($password, HASH_ALGO, ['cost' => HASH_COST]);
}

// Verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Generate random token
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

// Generate CSRF token
function generateCSRFToken() {
    initSession();
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = generateToken();
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    initSession();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// JSON response helper
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

// Error response helper
function errorResponse($message, $statusCode = 400) {
    jsonResponse(['success' => false, 'error' => $message], $statusCode);
}

// Success response helper
function successResponse($data = [], $message = 'Success') {
    jsonResponse(['success' => true, 'message' => $message, 'data' => $data]);
}

// Calculate BMI
function calculateBMI($weight, $height, $weightUnit = 'kg', $heightUnit = 'cm') {
    // Convert to metric if needed
    if ($weightUnit === 'lbs') {
        $weight = $weight * 0.453592; // Convert lbs to kg
    }
    if ($heightUnit === 'ft') {
        $height = $height * 30.48; // Convert ft to cm
    }
    
    $heightInMeters = $height / 100;
    $bmi = $weight / ($heightInMeters * $heightInMeters);
    return round($bmi, 2);
}

// Calculate BMR (Basal Metabolic Rate) using Mifflin-St Jeor Equation
function calculateBMR($weight, $height, $age, $gender, $weightUnit = 'kg', $heightUnit = 'cm') {
    // Convert to metric if needed
    if ($weightUnit === 'lbs') {
        $weight = $weight * 0.453592;
    }
    if ($heightUnit === 'ft') {
        $height = $height * 30.48;
    }
    
    // BMR calculation
    $bmr = (10 * $weight) + (6.25 * $height) - (5 * $age);
    
    if ($gender === 'male') {
        $bmr += 5;
    } else {
        $bmr -= 161;
    }
    
    return round($bmr, 2);
}

// Calculate daily calorie needs based on activity level
function calculateDailyCalories($bmr, $activityLevel, $fitnessGoal) {
    // Activity multipliers
    $multipliers = [
        'sedentary' => 1.2,
        'lightly_active' => 1.375,
        'moderately_active' => 1.55,
        'very_active' => 1.725
    ];
    
    $tdee = $bmr * ($multipliers[$activityLevel] ?? 1.2);
    
    // Adjust based on fitness goal
    switch ($fitnessGoal) {
        case 'lose_weight':
            $calories = $tdee - 500; // 500 calorie deficit
            break;
        case 'build_muscle':
            $calories = $tdee + 300; // 300 calorie surplus
            break;
        case 'look_better':
            $calories = $tdee - 200; // Slight deficit
            break;
        case 'stay_in_shape':
        default:
            $calories = $tdee; // Maintenance
            break;
    }
    
    return round($calories);
}

// Calculate macros
function calculateMacros($dailyCalories, $fitnessGoal) {
    $macros = [];
    
    switch ($fitnessGoal) {
        case 'build_muscle':
            // High protein, moderate carbs, low fat
            $macros['protein'] = round(($dailyCalories * 0.30) / 4); // 30% protein
            $macros['carbs'] = round(($dailyCalories * 0.45) / 4);   // 45% carbs
            $macros['fats'] = round(($dailyCalories * 0.25) / 9);    // 25% fats
            break;
        case 'lose_weight':
            // High protein, low carbs, moderate fat
            $macros['protein'] = round(($dailyCalories * 0.35) / 4); // 35% protein
            $macros['carbs'] = round(($dailyCalories * 0.30) / 4);   // 30% carbs
            $macros['fats'] = round(($dailyCalories * 0.35) / 9);    // 35% fats
            break;
        default:
            // Balanced macros
            $macros['protein'] = round(($dailyCalories * 0.25) / 4); // 25% protein
            $macros['carbs'] = round(($dailyCalories * 0.45) / 4);   // 45% carbs
            $macros['fats'] = round(($dailyCalories * 0.30) / 9);    // 30% fats
            break;
    }
    
    return $macros;
}

// Format date
function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

// Time ago helper
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;
    
    if ($difference < 60) {
        return 'just now';
    } elseif ($difference < 3600) {
        $minutes = floor($difference / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($difference < 86400) {
        $hours = floor($difference / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($difference < 604800) {
        $days = floor($difference / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M d, Y', $timestamp);
    }
}

// Log error to file
function logError($message, $context = []) {
    $logFile = __DIR__ . '/../logs/error.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? json_encode($context) : '';
    $logMessage = "[$timestamp] $message $contextStr" . PHP_EOL;
    
    error_log($logMessage, 3, $logFile);
}

// Check if onboarding is completed
function isOnboardingCompleted($userId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT onboarding_completed FROM user_profiles WHERE user_id = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch();
    
    return $result && $result['onboarding_completed'] == 1;
}

// Get user profile
function getUserProfile($userId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch();
}
