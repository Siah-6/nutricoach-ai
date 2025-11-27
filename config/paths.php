<?php
/**
 * Path Configuration - Works on both Localhost and Hostinger
 */

// Detect if we're on localhost or production
$isLocalhost = in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1', '::1']);

// Set base path
if ($isLocalhost) {
    // On localhost with XAMPP, include the project folder
    define('BASE_PATH', '/NutriCoachAI');
} else {
    // On Hostinger (production), use root
    define('BASE_PATH', '');
}

// Helper function to get asset path
function asset($path) {
    return BASE_PATH . '/' . ltrim($path, '/');
}
?>
