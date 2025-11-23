<?php
/**
 * User Profile API Endpoint
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    errorResponse('Unauthorized', 401);
}

$userId = getCurrentUserId();
$db = getDB();

// GET - Retrieve user profile
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
elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
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
