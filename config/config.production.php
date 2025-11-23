<?php
/**
 * NutriCoach AI Configuration File
 */

// Database Configuration
define('DB_HOST', 'localhost');       // Or your server IP (for VPS)
define('DB_NAME', 'u149335938_nutricoach_db');   // Replace with your actual database name
define('DB_USER', 'u149335938_nutricoach_db'); // Replace with your actual username
define('DB_PASS', 'Siah010603');    // Replace with your MySQL password
define('DB_CHARSET', 'utf8mb4');

// AI Configuration
define('GEMINI_API_KEY', ''); // Your Groq key
define('USE_GROQ_API', true); // ADD THIS LINE!

// Application Configuration
define('APP_NAME', 'NutriCoach AI');
define('APP_URL', 'http://localhost/xampp/NutriCoachAI');
define('APP_ENV', 'development'); // development or production

// Session Configuration
define('SESSION_LIFETIME', 3600); // 1 hour in seconds
define('SESSION_NAME', 'nutricoach_session');

// Security Configuration
define('HASH_ALGO', PASSWORD_BCRYPT);
define('HASH_COST', 12);

// File Upload Configuration
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);

// Email Configuration (for password reset)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your_email@gmail.com');
define('SMTP_PASS', 'your_email_password');
define('SMTP_FROM', 'noreply@nutricoach.ai');
define('SMTP_FROM_NAME', 'NutriCoach AI');

// Timezone
date_default_timezone_set('UTC');

// Error Reporting (disable in production)
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}