<?php
/**
 * User Logout API Endpoint
 */

require_once __DIR__ . '/../../includes/functions.php';

initSession();

// Destroy session
session_unset();
session_destroy();

// Clear session cookie
if (isset($_COOKIE[SESSION_NAME])) {
    setcookie(SESSION_NAME, '', time() - 3600, '/');
}

// If it's a direct browser request (GET), redirect to homepage
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    redirect('/');
}

// If it's an API request (POST), return JSON
header('Content-Type: application/json');
successResponse([], 'Logout successful');
