<?php
/**
 * Support Ticket API Endpoint
 */

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
$subject = sanitize($data['subject'] ?? '');
$message = sanitize($data['message'] ?? '');

if (empty($name) || empty($email) || empty($subject) || empty($message)) {
    errorResponse('All fields are required');
}

if (!isValidEmail($email)) {
    errorResponse('Invalid email address');
}

if (strlen($message) < 10) {
    errorResponse('Message must be at least 10 characters long');
}

try {
    $db = getDB();
    $userId = isLoggedIn() ? getCurrentUserId() : null;
    
    $stmt = $db->prepare("
        INSERT INTO support_tickets (user_id, name, email, subject, message)
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([$userId, $name, $email, $subject, $message]);
    
    successResponse([
        'ticket_id' => $db->lastInsertId()
    ], 'Support ticket submitted successfully. We will get back to you soon.');
    
} catch (Exception $e) {
    logError('Support ticket error: ' . $e->getMessage());
    errorResponse('An error occurred. Please try again later', 500);
}
