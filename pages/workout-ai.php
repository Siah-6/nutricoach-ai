<?php
/**
 * AI Suggested Workout Page
 */

require_once __DIR__ . '/../includes/functions.php';

initSession();

if (!isLoggedIn()) {
    redirect('/');
}

if (!isOnboardingCompleted(getCurrentUserId())) {
    redirect('/pages/onboarding.php');
}

$user = getCurrentUser();
$profile = getUserProfile(getCurrentUserId());
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0A1628">
    <title>AI Suggested Workout - NutriCoach AI</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dark-theme.css">
    <link rel="stylesheet" href="../assets/css/workout-session-dark.css">
</head>
<body class="dark-theme">
    <div class="workout-session-container">
        <!-- Header with Back Button -->
        <div class="workout-session-header">
            <button class="back-btn" onclick="window.history.back()">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
            </button>
            <div class="header-title">
                <h1>🎯 AI Suggested</h1>
                <p>Personalized for your goals</p>
            </div>
        </div>

        <!-- Loading State -->
        <div class="loading-state" id="loadingState">
            <div class="spinner"></div>
            <p>Generating your personalized workout...</p>
        </div>

        <!-- Completion Screen -->
        <div class="completion-screen" id="completionScreen" style="display: none;">
            <div class="completion-content">
                <div class="completion-icon">🎉</div>
                <h2>Workout Already Completed!</h2>
                <p>You've already crushed today's AI workout!</p>
                <div class="completion-stats">
                    <div class="stat-item">
                        <span class="stat-icon">✅</span>
                        <span class="stat-label">Completed Today</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-icon">🔥</span>
                        <span class="stat-label">Come back tomorrow!</span>
                    </div>
                </div>
                <button class="btn-back" onclick="window.history.back()">← Back to Workouts</button>
            </div>
        </div>

        <!-- Workout Content -->
        <div class="workout-content" id="workoutContent" style="display: none;">
            <!-- Workout Info -->
            <div class="workout-info">
                <div class="info-badge" id="workoutBadge">Ready to Start</div>
                <h2 id="workoutTitle">AI Suggested Workout</h2>
                <div class="workout-stats">
                    <div class="stat-item">
                        <span class="stat-icon">💪</span>
                        <span class="stat-value" id="exerciseCount">5</span>
                        <span class="stat-label">Exercises</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-icon">⭐</span>
                        <span class="stat-value" id="xpReward">100</span>
                        <span class="stat-label">XP Reward</span>
                    </div>
                </div>
            </div>

            <!-- Start Workout Button -->
            <div class="start-workout-section" id="startSection">
                <button class="btn-start-workout" onclick="startWorkout()">
                    🚀 Start Workout
                </button>
            </div>

            <!-- Exercise List -->
            <div class="exercise-list" id="exerciseList"></div>

            <!-- Complete Workout Button -->
            <div class="complete-section" id="completeSection" style="display: none;">
                <button class="btn-complete-workout" onclick="completeWorkout()">
                    ✅ Complete Workout
                </button>
            </div>
        </div>
    </div>

    <script>
        let currentExercises = [];
        let completedExercises = new Set();
        let currentSession = null;

        // Generate AI workout on page load
        window.addEventListener('DOMContentLoaded', async () => {
            // Check if already completed today
            const isCompleted = await checkCompletionStatus();
            
            if (!isCompleted) {
                // Try to restore from sessionStorage first
                const savedState = sessionStorage.getItem('aiWorkoutState');
                if (savedState) {
                    restoreWorkoutState(JSON.parse(savedState));
                } else {
                    generateAIWorkout();
                }
            }
        });

        async function checkCompletionStatus() {
            try {
                const response = await fetch(`../api/workout/check-completion.php?workout_type=AI Suggested Workout`);
                const data = await response.json();
                
                if (data.success && data.completed_today) {
                    // Show completion screen and hide everything else
                    document.getElementById('loadingState').style.display = 'none';
                    document.getElementById('workoutContent').style.display = 'none';
                    document.getElementById('completionScreen').style.display = 'block';
                    
                    // Clear any saved workout state
                    sessionStorage.removeItem('aiWorkoutState');
                    
                    return true; // Workout already completed
                }
                return false; // Not completed yet
            } catch (error) {
                console.error('Error checking completion:', error);
                return false;
            }
        }

        async function generateAIWorkout() {
            const loadingState = document.getElementById('loadingState');
            const workoutContent = document.getElementById('workoutContent');

            try {
                const prompt = "Create a workout plan with EXACTLY 5 exercises. " +
                              "User: <?php echo $profile['fitness_level']; ?> level, goal: <?php echo $profile['fitness_goal']; ?>. " +
                              "IMPORTANT: No greetings, no explanations, ONLY list exercises. " +
                              "Format STRICTLY as:\n" +
                              "1. Exercise Name: 3 sets x 10 reps\n" +
                              "2. Exercise Name: 3 sets x 12 reps\n" +
                              "etc.";

                const response = await fetch('../api/fitness/generate-workout.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ prompt: prompt })
                });

                const data = await response.json();
                
                if (data.success || data.response) {
                    const aiResponse = data.response || data.data?.response;
                    parseAndDisplayWorkout(aiResponse);
                    
                    loadingState.style.display = 'none';
                    workoutContent.style.display = 'block';
                } else {
                    throw new Error('Failed to generate workout');
                }
            } catch (error) {
                console.error('Error:', error);
                loadingState.innerHTML = '<p style="color: #ff6b6b;">Failed to generate workout. Please try again.</p>';
            }
        }

        function parseAndDisplayWorkout(content) {
            const lines = content.split('\n').filter(line => line.trim());
            currentExercises = [];
            
            lines.forEach(line => {
                line = line.trim();
                if (line.match(/^\d+\.|^-|^•/)) {
                    const cleanLine = line.replace(/^\d+\.|^-|^•/, '').trim();
                    const parts = cleanLine.split(':');
                    
                    if (parts.length >= 2) {
                        const name = parts[0].trim();
                        const details = parts[1].trim();
                        
                        if (details.match(/\d+/) && (details.includes('set') || details.includes('rep') || details.includes('x'))) {
                            currentExercises.push({ name, details });
                        }
                    }
                }
            });

            document.getElementById('exerciseCount').textContent = currentExercises.length;
            document.getElementById('xpReward').textContent = currentExercises.length * 20 + 50;
            
            displayExercises();
        }

        function displayExercises() {
            const exerciseList = document.getElementById('exerciseList');
            exerciseList.innerHTML = '';

            currentExercises.forEach((exercise, index) => {
                const isCompleted = completedExercises.has(index);
                const exerciseCard = document.createElement('div');
                exerciseCard.className = `exercise-card ${isCompleted ? 'completed' : ''}`;
                exerciseCard.innerHTML = `
                    <div class="exercise-header">
                        <div class="exercise-number">${index + 1}</div>
                        <div class="exercise-info">
                            <h3>${exercise.name}</h3>
                            <p>${exercise.details}</p>
                        </div>
                        <button class="btn-check ${isCompleted ? 'checked' : ''}" onclick="toggleExercise(${index})">
                            ${isCompleted ? '✓' : ''}
                        </button>
                    </div>
                `;
                exerciseList.appendChild(exerciseCard);
            });
        }

        async function startWorkout() {
            document.getElementById('startSection').style.display = 'none';
            document.getElementById('workoutBadge').textContent = 'In Progress';
            document.getElementById('workoutBadge').style.background = '#FF9800';

            try {
                const response = await fetch('../api/workout/start-session.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        workout_type: 'AI Suggested Workout',
                        total_exercises: currentExercises.length,
                        exercises: currentExercises
                    })
                });

                const data = await response.json();
                if (data.success) {
                    currentSession = data.session_id;
                    saveWorkoutState(); // Save state after starting
                }
            } catch (error) {
                console.error('Error starting session:', error);
            }
        }

        function saveWorkoutState() {
            const state = {
                exercises: currentExercises,
                completed: Array.from(completedExercises),
                sessionId: currentSession,
                hasStarted: currentSession !== null
            };
            sessionStorage.setItem('aiWorkoutState', JSON.stringify(state));
        }

        function restoreWorkoutState(state) {
            currentExercises = state.exercises;
            completedExercises = new Set(state.completed);
            currentSession = state.sessionId;

            document.getElementById('loadingState').style.display = 'none';
            document.getElementById('workoutContent').style.display = 'block';
            document.getElementById('exerciseCount').textContent = currentExercises.length;
            document.getElementById('xpReward').textContent = currentExercises.length * 20 + 50;

            if (state.hasStarted) {
                document.getElementById('startSection').style.display = 'none';
                document.getElementById('workoutBadge').textContent = 'In Progress';
                document.getElementById('workoutBadge').style.background = '#FF9800';
            }

            displayExercises();

            if (completedExercises.size === currentExercises.length) {
                document.getElementById('completeSection').style.display = 'block';
            }
        }

        async function toggleExercise(index) {
            if (!currentSession) {
                alert('Please start the workout first!');
                return;
            }

            if (completedExercises.has(index)) {
                completedExercises.delete(index);
            } else {
                completedExercises.add(index);

                try {
                    await fetch('../api/workout/complete-exercise.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            session_id: currentSession,
                            exercise_name: currentExercises[index].name
                        })
                    });
                } catch (error) {
                    console.error('Error completing exercise:', error);
                }
            }

            displayExercises();
            saveWorkoutState(); // Save state after each change

            if (completedExercises.size === currentExercises.length) {
                document.getElementById('completeSection').style.display = 'block';
            } else {
                document.getElementById('completeSection').style.display = 'none';
            }
        }

        async function completeWorkout() {
            if (completedExercises.size !== currentExercises.length) {
                alert('Please complete all exercises first!');
                return;
            }

            try {
                const response = await fetch('../api/workout/finish-session.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ session_id: currentSession })
                });

                const data = await response.json();
                if (data.success) {
                    // Clear saved state
                    sessionStorage.removeItem('aiWorkoutState');
                    
                    // Show success modal
                    showSuccessModal(data.total_xp_earned || 0, data.already_completed_today);
                }
            } catch (error) {
                console.error('Error completing workout:', error);
            }
        }

        function showSuccessModal(xpEarned, alreadyCompleted = false) {
            const modal = document.createElement('div');
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.8);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 10000;
                animation: fadeIn 0.3s ease;
            `;
            
            const emoji = alreadyCompleted ? '✅' : '🎉';
            const title = alreadyCompleted ? 'Workout Logged!' : 'Workout Complete!';
            const message = alreadyCompleted 
                ? 'You already completed this workout today.<br>No XP awarded, but great job staying active!' 
                : `You earned ${xpEarned} XP!`;
            
            modal.innerHTML = `
                <div style="
                    background: linear-gradient(135deg, #1a2332 0%, #0a1628 100%);
                    border-radius: 20px;
                    padding: 2rem;
                    text-align: center;
                    max-width: 400px;
                    border: 1px solid rgba(255, 255, 255, 0.1);
                    animation: slideUp 0.3s ease;
                ">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">${emoji}</div>
                    <h2 style="color: #fff; font-size: 1.5rem; margin-bottom: 0.5rem;">${title}</h2>
                    <p style="color: #a0aec0; margin-bottom: 1.5rem;">${message}</p>
                    <button onclick="window.location.href='workout-plan-improved.php'" style="
                        background: linear-gradient(135deg, #4a9eff 0%, #357abd 100%);
                        color: white;
                        border: none;
                        padding: 0.75rem 2rem;
                        border-radius: 10px;
                        font-size: 1rem;
                        font-weight: 600;
                        cursor: pointer;
                    ">Continue</button>
                </div>
            `;
            
            document.body.appendChild(modal);
        }
    </script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</body>
</html>
