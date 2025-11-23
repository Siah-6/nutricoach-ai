<?php
/**
 * User Signup API Endpoint
 */
header("Content-Type: application/json");
var_dump("PHP reached here");


require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Invalid request method', 405);
}

$data = json_decode(file_get_contents('php://input'), true);

// Validate input
$name = sanitize($data['name'] ?? '');
$email = sanitize($data['email'] ?? '');
$password = $data['password'] ?? '';
$confirmPassword = $data['confirm_password'] ?? '';

// Validation
if (empty($name) || empty($email) || empty($password)) {
    errorResponse('All fields are required');
}

if (!isValidEmail($email)) {
    errorResponse('Invalid email address');
}

if (strlen($password) < 8) {
    errorResponse('Password must be at least 8 characters long');
}

if ($password !== $confirmPassword) {
    errorResponse('Passwords do not match');
}

try {
    $db = getDB();
    
    // Check if email already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        errorResponse('Email already registered');
    }
    
    // Hash password
    $hashedPassword = hashPassword($password);
    
    // Insert user
    $stmt = $db->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$name, $email, $hashedPassword]);
    
    $userId = $db->lastInsertId();
    
    // Start session and log user in
    initSession();
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;
    
    successResponse([
        'user_id' => $userId,
        'name' => $name,
        'email' => $email
    ], 'Account created successfully');
    
} catch (Exception $e) {
    logError('Signup error: ' . $e->getMessage());
    errorResponse('An error occurred. Please try again later', 500);
}
