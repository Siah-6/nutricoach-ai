<?php
/**
 * NutriCoach AI Configuration File (Example)
 * Copy this file to config.php and update with your actual credentials
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'your_database_username');
define('DB_PASS', 'your_database_password');
define('DB_NAME', 'nutricoach_db');
define('DB_CHARSET', 'utf8mb4');

// AI Configuration
// Option 1: Use Gemini (Google AI)
define('GEMINI_API_KEY', 'your_gemini_api_key_here');
define('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent');
define('USE_GROQ_API', false); // Set to false to use Gemini

// Option 2: Use Groq (Free alternative - no credit card needed!)
// 1. Get free API key from: https://console.groq.com/
// 2. Replace GEMINI_API_KEY with your Groq API key
// 3. Set USE_GROQ_API to true
// define('GEMINI_API_KEY', 'your_groq_api_key_here');
// define('USE_GROQ_API', true);

// Application Configuration
define('APP_NAME', 'NutriCoach AI');
define('APP_URL', 'http://localhost:8000');
define('APP_ENV', 'development'); // development or production

// Session Configuration
define('SESSION_LIFETIME', 3600); // 1 hour in seconds
define('SESSION_NAME', 'nutricoach_session');

// Security Configuration
define('HASH_ALGO', PASSWORD_BCRYPT);
define('HASH_COST', 12);

// File Upload Configuration
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
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
