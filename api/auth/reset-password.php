<?php
/**
 * Reset Password API Endpoint
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Invalid request method', 405);
}

$data = json_decode(file_get_contents('php://input'), true);

$token = sanitize($data['token'] ?? '');
$password = $data['password'] ?? '';
$confirmPassword = $data['confirm_password'] ?? '';

if (empty($token) || empty($password)) {
    errorResponse('Token and password are required');
}

if (strlen($password) < 8) {
    errorResponse('Password must be at least 8 characters long');
}

if ($password !== $confirmPassword) {
    errorResponse('Passwords do not match');
}

try {
    $db = getDB();
    
    // Verify token
    $stmt = $db->prepare("
        SELECT pr.id, pr.user_id, pr.expires_at, pr.used
        FROM password_resets pr
        WHERE pr.token = ? AND pr.used = FALSE
    ");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();
    
    if (!$reset) {
        errorResponse('Invalid or expired reset token');
    }
    
    // Check if token has expired
    if (strtotime($reset['expires_at']) < time()) {
        errorResponse('Reset token has expired');
    }
    
    // Hash new password
    $hashedPassword = hashPassword($password);
    
    // Update user password
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hashedPassword, $reset['user_id']]);
    
    // Mark token as used
    $stmt = $db->prepare("UPDATE password_resets SET used = TRUE WHERE id = ?");
    $stmt->execute([$reset['id']]);
    
    successResponse([], 'Password reset successfully');
    
} catch (Exception $e) {
    logError('Reset password error: ' . $e->getMessage());
    errorResponse('An error occurred. Please try again later', 500);
}
