<?php
/**
 * Update User Weight
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');
initSession();

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$userId = getCurrentUserId();
$weight = isset($data['weight']) ? floatval($data['weight']) : null;

if (!$weight || $weight <= 0 || $weight > 300) {
    echo json_encode(['success' => false, 'message' => 'Invalid weight value']);
    exit;
}

try {
    $db = getDB();
    
    // Update user profile weight
    $stmt = $db->prepare("UPDATE user_profiles SET weight = ?, updated_at = NOW() WHERE user_id = ?");
    $stmt->execute([$weight, $userId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Weight updated successfully',
        'weight' => $weight
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
