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
    
    // Build context for AI - IMPROVED FOR BETTER RESPONSES
    $context = "You are NutriCoach AI, a friendly and supportive fitness coach. ";
    $context .= "IMPORTANT RULES:\n";
    $context .= "1. Keep responses SHORT (3-5 sentences max)\n";
    $context .= "2. Be conversational and friendly (like texting a friend)\n";
    $context .= "3. Use emojis occasionally (💪 🔥 ✅ 🎯)\n";
    $context .= "4. Use bullet points for lists\n";
    $context .= "5. Ask follow-up questions to keep conversation going\n";
    $context .= "6. NO long paragraphs or walls of text\n";
    $context .= "7. Focus on ONE topic at a time\n\n";
    
    if ($profile) {
        $context .= "User Info:\n";
        $context .= "- Name: {$user['name']}\n";
        $context .= "- Age: {$profile['age']}, {$profile['gender']}\n";
        $context .= "- Goal: {$profile['fitness_goal']}\n";
        $context .= "- Level: {$profile['fitness_level']}\n";
        $context .= "- Daily calories: {$profile['daily_calories']}\n";
        $context .= "- Macros: {$profile['protein_grams']}g protein, {$profile['carbs_grams']}g carbs, {$profile['fats_grams']}g fats\n";
        $context .= "- Workouts: {$profile['workout_frequency']}x/week\n\n";
    }
    
    $context .= "Respond in a casual, supportive tone. Keep it brief and actionable!";
    
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
