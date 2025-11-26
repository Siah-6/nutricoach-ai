<?php
/**
 * AI Workout Generation API (Does NOT save to chat history)
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
$prompt = trim($data['prompt'] ?? '');

if (empty($prompt)) {
    errorResponse('Prompt is required');
}

$userId = getCurrentUserId();

try {
    // Get user profile for context
    $user = getCurrentUser();
    $profile = getUserProfile($userId);
    
    // Build context for AI
    $context = "You are a professional gym fitness coach. Generate a GYM workout plan using EQUIPMENT.\n";
    $context .= "IMPORTANT RULES:\n";
    $context .= "1. ONLY use GYM EQUIPMENT (barbells, dumbbells, machines, cables)\n";
    $context .= "2. NO bodyweight exercises (no push-ups, pull-ups, planks, burpees, mountain climbers)\n";
    $context .= "3. Focus on compound movements and isolation exercises with weights\n";
    $context .= "4. Include exercises like: Bench Press, Squats, Deadlifts, Dumbbell Rows, Cable Flyes, etc.\n\n";
    $context .= "FORMAT: Respond ONLY with exercises in this exact format:\n";
    $context .= "1. Exercise Name: X sets x Y reps\n";
    $context .= "2. Exercise Name: X sets x Y reps\n";
    $context .= "NO greetings, NO explanations, ONLY the numbered exercise list.\n\n";
    
    if ($profile) {
        $context .= "User: {$profile['fitness_level']} level, Goal: {$profile['fitness_goal']}\n\n";
    }
    
    // Prepare AI API request
    $useGroq = defined('USE_GROQ_API') && USE_GROQ_API === true;
    
    if ($useGroq) {
        $apiUrl = 'https://api.groq.com/openai/v1/chat/completions';
        
        $requestBody = [
            'model' => 'llama-3.3-70b-versatile',
            'messages' => [
                ['role' => 'system', 'content' => $context],
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.7,
            'max_tokens' => 512,
        ];
        
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . GEMINI_API_KEY
        ];
    } else {
        $apiUrl = GEMINI_API_URL . '?key=' . GEMINI_API_KEY;
        
        $requestBody = [
            'contents' => [
                ['parts' => [
                    ['text' => $context],
                    ['text' => $prompt]
                ]]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 512,
            ]
        ];
        
        $headers = ['Content-Type: application/json'];
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
    
    // Parse response
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
    
    // NOTE: We do NOT save to chat_history table
    // This is workout generation, not chat conversation
    
    successResponse([
        'response' => $aiResponse,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    logError('Workout generation error: ' . $e->getMessage());
    errorResponse('An error occurred. Please try again later', 500);
}
