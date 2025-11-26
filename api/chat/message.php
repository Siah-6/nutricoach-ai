<?php
/**
 * AI Chatbot API Endpoint (Gemini Integration)
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Invalid request method', 405);
}

if (!isLoggedIn()) {
    errorResponse('Unauthorized', 401);
}

$data = json_decode(file_get_contents('php://input'), true);
$message = trim($data['message'] ?? '');

if (empty($message)) {
    errorResponse('Message is required');
}

if (strlen($message) > 1000) {
    errorResponse('Message is too long (max 1000 characters)');
}

$userId = getCurrentUserId();

try {
    $db = getDB();
    
    // Get user profile for context
    $user = getCurrentUser();
    $profile = getUserProfile($userId);
    
    // Get user's recent activity for better context (with error handling)
    try {
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM workout_sessions WHERE user_id = ? AND status = 'completed' AND DATE(created_at) >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
        $stmt->execute([$userId]);
        $recentWorkouts = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    } catch (Exception $e) {
        $recentWorkouts = 0;
    }
    
    try {
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM meal_logs WHERE user_id = ? AND DATE(logged_at) = CURDATE()");
        $stmt->execute([$userId]);
        $todayMeals = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    } catch (Exception $e) {
        $todayMeals = 0;
    }
    
    try {
        $stmt = $db->prepare("SELECT xp, level FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $userStats = $stmt->fetch(PDO::FETCH_ASSOC);
        $userXP = $userStats['xp'] ?? 0;
        $userLevel = $userStats['level'] ?? 1;
    } catch (Exception $e) {
        $userXP = 0;
        $userLevel = 1;
    }
    
    // Get last workout
    try {
        $stmt = $db->prepare("SELECT muscle_group, created_at FROM workout_sessions WHERE user_id = ? AND status = 'completed' ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$userId]);
        $lastWorkout = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $lastWorkout = false;
    }
    
    // Build context for AI - CONCISE VERSION
    $context = "You are NutriCoach AI, {$user['name']}'s personal fitness coach. Be supportive, energetic, and brief (2-4 sentences max). Use emojis naturally 💪🔥\n\n";
    
    $context .= "USER: {$user['name']}\n";
    if ($profile) {
        $context .= "Goal: {$profile['fitness_goal']} | Level: {$userLevel} ({$userXP} XP)\n";
        $context .= "Target: {$profile['daily_calories']} cal, {$profile['protein_grams']}g protein\n";
    }
    
    $context .= "This week: {$recentWorkouts} workouts | Today: {$todayMeals} meals logged\n";
    if ($lastWorkout) {
        $context .= "Last workout: {$lastWorkout['muscle_group']}\n";
    }
    
    $context .= "\nCall them by name. Reference their stats. Keep it short and actionable!";
    
    // Prepare AI API request (supports both Gemini and Groq)
    $useGroq = defined('USE_GROQ_API') && USE_GROQ_API === true;
    
    if ($useGroq) {
        // Groq API (Free alternative)
        $apiUrl = 'https://api.groq.com/openai/v1/chat/completions';
        
        $requestBody = [
            'model' => 'llama-3.3-70b-versatile', // Updated model name
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $context
                ],
                [
                    'role' => 'user',
                    'content' => $message
                ]
            ],
            'temperature' => 0.7,
            'max_tokens' => 1024,
        ];
        
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . GEMINI_API_KEY // Reusing same config key for Groq
        ];
    } else {
        // Gemini API (Original)
        $apiUrl = GEMINI_API_URL . '?key=' . GEMINI_API_KEY;
        
        $requestBody = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $context],
                        ['text' => "User question: " . $message]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 1024,
            ]
        ];
        
        $headers = [
            'Content-Type: application/json'
        ];
    }
    
    // Make API request
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBody));
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        logError('AI API curl error: ' . $curlError);
        errorResponse('Failed to connect to AI service', 500);
    }
    
    if ($httpCode !== 200) {
        logError('AI API error: ' . $response);
        errorResponse('AI service returned an error', 500);
    }
    
    $responseData = json_decode($response, true);
    
    // Parse response based on API type
    if ($useGroq) {
        if (!isset($responseData['choices'][0]['message']['content'])) {
            logError('Unexpected Groq API response: ' . $response);
            errorResponse('Unexpected response from AI service', 500);
        }
        $aiResponse = $responseData['choices'][0]['message']['content'];
    } else {
        if (!isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
            logError('Unexpected Gemini API response: ' . $response);
            errorResponse('Unexpected response from AI service', 500);
        }
        $aiResponse = $responseData['candidates'][0]['content']['parts'][0]['text'];
    }
    
    // Save chat history
    $stmt = $db->prepare("INSERT INTO chat_history (user_id, message, response) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $message, $aiResponse]);
    
    successResponse([
        'message' => $message,
        'response' => $aiResponse,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    logError('Chat error: ' . $e->getMessage());
    errorResponse('An error occurred. Please try again later', 500);
}
