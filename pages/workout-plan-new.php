<?php
/**
 * AI-Powered Workout Plan Page
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

initSession();

if (!isLoggedIn()) {
    redirect('/');
}

if (!isOnboardingCompleted(getCurrentUserId())) {
    redirect('/pages/onboarding.php');
}

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workout Plan - NutriCoach AI</title>
    <link rel="stylesheet" href="/xampp/NutriCoachAI/assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="container" style="padding: 2rem 0;">
        <h1 class="mb-4">💪 AI Workout Plan</h1>

        <!-- Muscle Group Selection -->
        <div class="card mb-4">
            <div class="card-header">
                <h2>Select Muscle Group</h2>
                <p>Choose which area you want to focus on today</p>
            </div>
            <div class="card-body">
                <div class="muscle-group-grid">
                    <button class="muscle-btn" data-muscle="chest" onclick="generateWorkout('chest')">
                        <span class="muscle-icon">💪</span>
                        <span class="muscle-name">Chest</span>
                    </button>
                    <button class="muscle-btn" data-muscle="back" onclick="generateWorkout('back')">
                        <span class="muscle-icon">🏋️</span>
                        <span class="muscle-name">Back</span>
                    </button>
                    <button class="muscle-btn" data-muscle="legs" onclick="generateWorkout('legs')">
                        <span class="muscle-icon">🦵</span>
                        <span class="muscle-name">Legs</span>
                    </button>
                    <button class="muscle-btn" data-muscle="arms" onclick="generateWorkout('arms')">
                        <span class="muscle-icon">💪</span>
                        <span class="muscle-name">Arms</span>
                    </button>
                    <button class="muscle-btn" data-muscle="shoulders" onclick="generateWorkout('shoulders')">
                        <span class="muscle-icon">🤸</span>
                        <span class="muscle-name">Shoulders</span>
                    </button>
                    <button class="muscle-btn" data-muscle="fullbody" onclick="generateWorkout('fullbody')">
                        <span class="muscle-icon">🏃</span>
                        <span class="muscle-name">Full Body</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Workout Display -->
        <div id="workoutContainer"></div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script src="/xampp/NutriCoachAI/assets/js/main.js"></script>
    <script>
        const { Utils, Chat } = window.NutriCoach;
        let currentMuscle = null;

        async function generateWorkout(muscleGroup) {
            currentMuscle = muscleGroup;
            const container = document.getElementById('workoutContainer');
            
            // Highlight selected button
            document.querySelectorAll('.muscle-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelector(`[data-muscle="${muscleGroup}"]`).classList.add('active');
            
            // Show loading
            container.innerHTML = `
                <div class="card">
                    <div class="card-body text-center" style="padding: 3rem;">
                        <div class="spinner"></div>
                        <p style="margin-top: 1rem;">AI is generating your personalized ${muscleGroup} workout...</p>
                    </div>
                </div>
            `;
            
            try {
                // Ask AI to generate workout
                const prompt = `Generate a ${muscleGroup} workout plan with 5-6 exercises. For each exercise, provide:
1. Exercise name
2. Sets (e.g., "3 sets")
3. Reps (e.g., "12 reps" or "30 seconds")
4. Brief instruction (1 sentence)

Format as a simple list. Make it suitable for intermediate fitness level.`;
                
                const response = await Chat.sendMessage(prompt);
                
                if (response && response.data && response.data.response) {
                    displayWorkout(muscleGroup, response.data.response);
                } else {
                    throw new Error('Invalid response from AI');
                }
                
            } catch (error) {
                console.error('Error generating workout:', error);
                container.innerHTML = `
                    <div class="alert alert-error">
                        <strong>Error:</strong> Failed to generate workout. Please try again.
                    </div>
                `;
            }
        }
        
        function displayWorkout(muscleGroup, aiResponse) {
            const container = document.getElementById('workoutContainer');
            
            const muscleEmojis = {
                chest: '💪',
                back: '🏋️',
                legs: '🦵',
                arms: '💪',
                shoulders: '🤸',
                fullbody: '🏃'
            };
            
            container.innerHTML = `
                <div class="card">
                    <div class="card-header">
                        <h2>${muscleEmojis[muscleGroup]} ${muscleGroup.charAt(0).toUpperCase() + muscleGroup.slice(1)} Workout</h2>
                        <p>AI-Generated personalized workout plan</p>
                    </div>
                    <div class="card-body">
                        <div class="ai-workout-response">
                            ${aiResponse.replace(/\n/g, '<br>')}
                        </div>
                        
                        <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
                            <h3 style="margin-bottom: 1rem;">💡 Pro Tips:</h3>
                            <ul style="list-style: none; padding: 0;">
                                <li style="padding: 0.5rem 0;">✅ Warm up for 5-10 minutes before starting</li>
                                <li style="padding: 0.5rem 0;">✅ Focus on proper form over heavy weights</li>
                                <li style="padding: 0.5rem 0;">✅ Rest 60-90 seconds between sets</li>
                                <li style="padding: 0.5rem 0;">✅ Stay hydrated throughout your workout</li>
                                <li style="padding: 0.5rem 0;">✅ Cool down and stretch after finishing</li>
                            </ul>
                        </div>
                        
                        <div style="margin-top: 2rem;">
                            <button onclick="generateWorkout('${muscleGroup}')" class="btn btn-secondary" style="margin-right: 1rem;">
                                🔄 Regenerate Workout
                            </button>
                            <button onclick="saveWorkout()" class="btn btn-primary">
                                💾 Save to My Plans
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }
        
        async function saveWorkout() {
            // TODO: Implement save functionality
            alert('Workout saved! (Feature coming soon)');
        }
    </script>

    <style>
        .muscle-group-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
        }
        
        .muscle-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            padding: 1.5rem 1rem;
            background: var(--bg-light);
            border: 2px solid var(--border-color);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .muscle-btn:hover {
            transform: translateY(-4px);
            border-color: var(--primary-color);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .muscle-btn.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .muscle-icon {
            font-size: 2.5rem;
        }
        
        .muscle-name {
            font-weight: 600;
            font-size: 1.125rem;
        }
        
        .ai-workout-response {
            background: var(--bg-light);
            padding: 1.5rem;
            border-radius: 8px;
            border-left: 4px solid var(--primary-color);
            line-height: 1.8;
        }
        
        @media (max-width: 768px) {
            .muscle-group-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .muscle-btn {
                padding: 1rem 0.5rem;
            }
            
            .muscle-icon {
                font-size: 2rem;
            }
            
            .muscle-name {
                font-size: 1rem;
            }
        }
    </style>
</body>
</html>
