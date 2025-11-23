<?php
/**
 * User Login API Endpoint
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Invalid request method', 405);
}

$data = json_decode(file_get_contents('php://input'), true);

// Validate input
$email = sanitize($data['email'] ?? '');
$password = $data['password'] ?? '';

if (empty($email) || empty($password)) {
    errorResponse('Email and password are required');
}

if (!isValidEmail($email)) {
    errorResponse('Invalid email address');
}

try {
    $db = getDB();
    
    // Get user by email
    $stmt = $db->prepare("SELECT id, name, email, password, is_active FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        errorResponse('Invalid email or password');
    }
    
    if (!$user['is_active']) {
        errorResponse('Account is disabled. Please contact support');
    }
    
    // Verify password
    if (!verifyPassword($password, $user['password'])) {
        errorResponse('Invalid email or password');
    }
    
    // Update last login
    $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $stmt->execute([$user['id']]);
    
    // Start session
    initSession();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    
    // Check if onboarding is completed
    $onboardingCompleted = isOnboardingCompleted($user['id']);
    
    successResponse([
        'user_id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'onboarding_completed' => $onboardingCompleted
    ], 'Login successful');
    
} catch (Exception $e) {
    logError('Login error: ' . $e->getMessage());
    errorResponse('An error occurred. Please try again later', 500);
}
