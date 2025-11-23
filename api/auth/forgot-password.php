<?php
/**
 * Forgot Password API Endpoint
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Invalid request method', 405);
}

$data = json_decode(file_get_contents('php://input'), true);

$email = sanitize($data['email'] ?? '');

if (empty($email)) {
    errorResponse('Email is required');
}

if (!isValidEmail($email)) {
    errorResponse('Invalid email address');
}

try {
    $db = getDB();
    
    // Check if user exists
    $stmt = $db->prepare("SELECT id, name FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        // Don't reveal if email exists or not for security
        successResponse([], 'If the email exists, a password reset link has been sent');
    }
    
    // Generate reset token
    $token = generateToken(32);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Save token to database
    $stmt = $db->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$user['id'], $token, $expiresAt]);
    
    // In production, send email with reset link
    // For now, we'll just return success
    // Reset link would be: APP_URL/reset-password.php?token=$token
    
    $resetLink = APP_URL . "/pages/reset-password.php?token=" . $token;
    
    // TODO: Send email with reset link
    // For development, you can log the link
    logError("Password reset link for {$email}: {$resetLink}");
    
    successResponse([
        'reset_link' => APP_ENV === 'development' ? $resetLink : null
    ], 'If the email exists, a password reset link has been sent');
    
} catch (Exception $e) {
    logError('Forgot password error: ' . $e->getMessage());
    errorResponse('An error occurred. Please try again later', 500);
}
