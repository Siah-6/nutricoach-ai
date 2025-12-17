<?php
/**
 * Update User Goal/Target Weight
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
$targetWeight = isset($data['target_weight']) ? floatval($data['target_weight']) : null;

if (!$targetWeight || $targetWeight <= 0 || $targetWeight > 300) {
    echo json_encode(['success' => false, 'message' => 'Invalid target weight value']);
    exit;
}

try {
    $db = getDB();
    
    // Update user profile target weight
    $stmt = $db->prepare("UPDATE user_profiles SET target_weight = ?, updated_at = NOW() WHERE user_id = ?");
    $stmt->execute([$targetWeight, $userId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Goal weight updated successfully',
        'target_weight' => $targetWeight
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
