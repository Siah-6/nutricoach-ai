<?php
/**
 * Chat History API Endpoint
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    errorResponse('Invalid request method', 405);
}

if (!isLoggedIn()) {
    errorResponse('Unauthorized', 401);
}

$userId = getCurrentUserId();
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$limit = min($limit, 100); // Max 100 messages

try {
    $db = getDB();
    
    $stmt = $db->prepare("
        SELECT id, message, response, created_at
        FROM chat_history
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT ?
    ");
    $stmt->execute([$userId, $limit]);
    $history = $stmt->fetchAll();
    
    // Reverse to show oldest first
    $history = array_reverse($history);
    
    successResponse(['history' => $history]);
    
} catch (Exception $e) {
    logError('Chat history error: ' . $e->getMessage());
    errorResponse('An error occurred. Please try again later', 500);
}
