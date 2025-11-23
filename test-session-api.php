<?php
// Test the session API directly
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();
$_SESSION['user_id'] = 1; // Simulate logged in user

// Set request method
$_SERVER['REQUEST_METHOD'] = 'GET';

// Include the API file
try {
    require_once __DIR__ . '/api/workout/get-current-session.php';
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString();
}
