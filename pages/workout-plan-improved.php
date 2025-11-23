<?php
/**
 * Improved AI-Powered Workout Plan Page
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
$profile = getUserProfile(getCurrentUserId());
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workout Plan - NutriCoach AI</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/mobile-redesign.css">
    <style>
        .workout-container {
            padding: 1rem;
            max-width: 800px;
            margin: 0 auto;
            padding-bottom: 80px;
        }
        
        /* Quick Action Cards */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .action-card {
            background: linear-gradient(135deg, #4A9DB5 0%, #3D8BA3 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 16px;
            text-align: center;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            border: none;
            box-shadow: 0 4px 12px rgba(74, 157, 181, 0.3);
        }
        
        .action-card:active {
            transform: scale(0.98);
        }
        
        .action-card .icon {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .action-card .title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .action-card .subtitle {
            font-size: 0.75rem;
            opacity: 0.9;
        }
        
        /* Muscle Group Grid */
        .muscle-groups {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
            margin-bottom: 2rem;
        }
        
        .muscle-btn {
            background: white;
            border: 2px solid #E8EEF2;
            padding: 1.25rem;
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .muscle-btn:active {
            transform: scale(0.98);
            border-color: #4A9DB5;
            background: #F0F8FA;
        }
        
        .muscle-btn .icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .muscle-btn .name {
            font-size: 0.95rem;
            font-weight: 600;
            color: #2c3e50;
        }
        
        /* Workout Display */
        .workout-display {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
            display: none;
        }
        
        .workout-display.active {
            display: block;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .workout-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #E8EEF2;
        }
        
        .workout-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #2c3e50;
        }
        
        .workout-badge {
            background: #4A9DB5;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .exercise-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .exercise-item {
            background: #F8FAFB;
            padding: 1rem;
            border-radius: 12px;
            border-left: 3px solid #4A9DB5;
        }
        
        .exercise-name {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        
        .exercise-details {
            font-size: 0.875rem;
            color: #7f8c8d;
        }
        
        .loading {
            text-align: center;
            padding: 2rem;
            color: #7f8c8d;
        }
        
        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #E8EEF2;
            border-top-color: #4A9DB5;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        @media (max-width: 768px) {
            .workout-container {
                padding: 0.75rem;
            }
            
            .quick-actions {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="workout-container">
        <h1 style="margin-bottom: 1.5rem; color: #2c3e50;">💪 Workout Plan</h1>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <button class="action-card" onclick="generateSuggestedWorkout()">
                <div class="icon">🎯</div>
                <div class="title">AI Suggested</div>
                <div class="subtitle">Based on your goals</div>
            </button>
            <button class="action-card" onclick="generateTodayWorkout()">
                <div class="icon">📅</div>
                <div class="title">Today's Plan</div>
                <div class="subtitle">Your daily routine</div>
            </button>
        </div>

        <!-- Muscle Group Selection -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <h2>Target Specific Muscle</h2>
                <p style="font-size: 0.875rem; opacity: 0.8;">Choose which area to focus on</p>
            </div>
            <div class="card-body">
                <div class="muscle-groups">
                    <button class="muscle-btn" onclick="generateMuscleWorkout('chest')">
                        <div class="icon">💪</div>
                        <div class="name">Chest</div>
                    </button>
                    <button class="muscle-btn" onclick="generateMuscleWorkout('back')">
                        <div class="icon">🏋️</div>
                        <div class="name">Back</div>
                    </button>
                    <button class="muscle-btn" onclick="generateMuscleWorkout('legs')">
                        <div class="icon">🦵</div>
                        <div class="name">Legs</div>
                    </button>
                    <button class="muscle-btn" onclick="generateMuscleWorkout('arms')">
                        <div class="icon">💪</div>
                        <div class="name">Arms</div>
                    </button>
                    <button class="muscle-btn" onclick="generateMuscleWorkout('shoulders')">
                        <div class="icon">🏋️</div>
                        <div class="name">Shoulders</div>
                    </button>
                    <button class="muscle-btn" onclick="generateMuscleWorkout('full body')">
                        <div class="icon">🔥</div>
                        <div class="name">Full Body</div>
                    </button>
                </div>
            </div>
        </div>

        <!-- Workout Display -->
        <div id="workoutDisplay" class="workout-display">
            <div class="workout-header">
                <div class="workout-title" id="workoutTitle">Loading...</div>
                <div class="workout-badge" id="workoutBadge">AI Generated</div>
            </div>
            <div id="workoutContent" class="exercise-list">
                <div class="loading">
                    <div class="loading-spinner"></div>
                    <p>Generating your personalized workout...</p>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        const workoutDisplay = document.getElementById('workoutDisplay');
        const workoutTitle = document.getElementById('workoutTitle');
        const workoutBadge = document.getElementById('workoutBadge');
        const workoutContent = document.getElementById('workoutContent');

        // Current workout session
        let currentSession = null;
        let currentExercises = [];
        let completedExercises = new Set();

        // Generate AI Suggested Workout (based on user's goals and chat history)
        async function generateSuggestedWorkout() {
            showLoading('AI Suggested Workout', 'Personalized');
            
            const prompt = "Create a workout plan with EXACTLY 5 exercises. " +
                          "User: <?php echo $profile['fitness_level']; ?> level, goal: <?php echo $profile['fitness_goal']; ?>. " +
                          "IMPORTANT: No greetings, no explanations, ONLY list exercises. " +
                          "Format STRICTLY as:\n" +
                          "1. Exercise Name: 3 sets x 10 reps\n" +
                          "2. Exercise Name: 3 sets x 12 reps\n" +
                          "etc.";
            
            await generateWorkoutWithTracking(prompt, 'AI Suggested for You');
        }

        // Generate Today's Workout
        async function generateTodayWorkout() {
            showLoading('Today\'s Workout', 'Daily Plan');
            
            const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            const today = days[new Date().getDay()];
            
            const prompt = `Create a ${today} workout with EXACTLY 5 exercises. ` +
                          `User: <?php echo $profile['fitness_level']; ?> level, goal: <?php echo $profile['fitness_goal']; ?>. ` +
                          `IMPORTANT: No greetings, ONLY list exercises. ` +
                          `Format: 1. Exercise: 3 sets x 10 reps`;
            
            await generateWorkout(prompt, `${today}'s Workout`);
        }

        // Generate Muscle-Specific Workout with Exercise Library
        async function generateMuscleWorkout(muscle) {
            showLoading(`${muscle.charAt(0).toUpperCase() + muscle.slice(1)} Exercises`, 'Exercise Library');
            
            // Show exercise library instead of AI workout
            await showExerciseLibrary(muscle);
        }

        // Show loading state
        function showLoading(title, badge) {
            workoutDisplay.classList.add('active');
            workoutTitle.textContent = title;
            workoutBadge.textContent = badge;
            workoutContent.innerHTML = `
                <div class="loading">
                    <div class="loading-spinner"></div>
                    <p>Generating your personalized workout...</p>
                </div>
            `;
            workoutDisplay.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        // Generate workout using AI
        async function generateWorkout(prompt, title) {
            try {
                const response = await window.NutriCoach.Chat.sendMessage(prompt);
                
                if (response.success || response.response) {
                    const aiResponse = response.response || response.data?.response;
                    displayWorkout(aiResponse, title);
                } else {
                    showError('Failed to generate workout. Please try again.');
                }
            } catch (error) {
                console.error('Error:', error);
                showError('An error occurred. Please try again.');
            }
        }

        // Display workout
        function displayWorkout(content, title) {
            workoutTitle.textContent = title;
            
            // Parse AI response and format it nicely
            const lines = content.split('\n').filter(line => line.trim());
            let html = '';
            
            lines.forEach(line => {
                line = line.trim();
                if (line.match(/^\d+\.|^-|^•/)) {
                    // Exercise line
                    const cleanLine = line.replace(/^\d+\.|^-|^•/, '').trim();
                    const parts = cleanLine.split(':');
                    const name = parts[0].trim();
                    const details = parts[1] ? parts[1].trim() : '';
                    
                    html += `
                        <div class="exercise-item">
                            <div class="exercise-name">${name}</div>
                            ${details ? `<div class="exercise-details">${details}</div>` : ''}
                        </div>
                    `;
                }
            });
            
            if (!html) {
                // Fallback: just show the content
                html = `<div class="exercise-item"><div class="exercise-name">${content}</div></div>`;
            }
            
            workoutContent.innerHTML = html;
        }

        // Show error
        function showError(message) {
            workoutContent.innerHTML = `
                <div class="loading">
                    <p style="color: #e74c3c;">❌ ${message}</p>
                </div>
            `;
        }

        // Exercise Library - Curated exercises with GIFs
        const exerciseLibrary = {
            'chest': [
                {
                    name: 'Barbell Bench Press',
                    gif: 'https://v2.exercisedb.io/image/sFz8PYxdXxLqxq',
                    description: 'Classic chest builder',
                    sets: '3-4 sets',
                    reps: '8-12 reps',
                    tips: 'Keep your feet flat, arch your back slightly, and lower the bar to mid-chest.'
                },
                {
                    name: 'Incline Dumbbell Press',
                    gif: 'https://v2.exercisedb.io/image/Wd6MwGxGkpZqmU',
                    description: 'Upper chest focus',
                    sets: '3 sets',
                    reps: '10-12 reps',
                    tips: 'Set bench to 30-45 degrees. Press dumbbells up and slightly together.'
                },
                {
                    name: 'Push-Ups',
                    gif: 'https://v2.exercisedb.io/image/sFz8PYxdXxLqxq',
                    description: 'Bodyweight classic',
                    sets: '3 sets',
                    reps: '15-20 reps',
                    tips: 'Keep core tight, elbows at 45 degrees, full range of motion.'
                },
                {
                    name: 'Dumbbell Flyes',
                    gif: 'https://v2.exercisedb.io/image/Wd6MwGxGkpZqmU',
                    description: 'Chest stretch and squeeze',
                    sets: '3 sets',
                    reps: '12-15 reps',
                    tips: 'Slight bend in elbows, focus on the stretch at bottom.'
                },
                {
                    name: 'Cable Crossover',
                    gif: 'https://v2.exercisedb.io/image/sFz8PYxdXxLqxq',
                    description: 'Inner chest definition',
                    sets: '3 sets',
                    reps: '12-15 reps',
                    tips: 'Cross hands at the center, squeeze chest at peak contraction.'
                }
            ],
            'back': [
                {
                    name: 'Pull-Ups',
                    gif: 'https://v2.exercisedb.io/image/sFz8PYxdXxLqxq',
                    description: 'Best back builder',
                    sets: '3-4 sets',
                    reps: '6-10 reps',
                    tips: 'Full hang at bottom, pull until chin over bar.'
                },
                {
                    name: 'Barbell Rows',
                    gif: 'https://v2.exercisedb.io/image/Wd6MwGxGkpZqmU',
                    description: 'Thick back developer',
                    sets: '3-4 sets',
                    reps: '8-12 reps',
                    tips: 'Hinge at hips, pull to lower chest, squeeze shoulder blades.'
                },
                {
                    name: 'Lat Pulldown',
                    gif: 'https://v2.exercisedb.io/image/sFz8PYxdXxLqxq',
                    description: 'Lat width builder',
                    sets: '3 sets',
                    reps: '10-12 reps',
                    tips: 'Pull to upper chest, lean back slightly, control the negative.'
                },
                {
                    name: 'Seated Cable Row',
                    gif: 'https://v2.exercisedb.io/image/Wd6MwGxGkpZqmU',
                    description: 'Mid-back thickness',
                    sets: '3 sets',
                    reps: '10-12 reps',
                    tips: 'Keep chest up, pull to lower chest, squeeze at peak.'
                },
                {
                    name: 'Deadlift',
                    gif: 'https://v2.exercisedb.io/image/sFz8PYxdXxLqxq',
                    description: 'Full back power',
                    sets: '3 sets',
                    reps: '5-8 reps',
                    tips: 'Keep back straight, drive through heels, hinge at hips.'
                }
            ],
            'legs': [
                {
                    name: 'Barbell Squat',
                    gif: 'https://v2.exercisedb.io/image/sFz8PYxdXxLqxq',
                    description: 'King of leg exercises',
                    sets: '3-4 sets',
                    reps: '8-12 reps',
                    tips: 'Depth to parallel, knees track over toes, chest up.'
                },
                {
                    name: 'Romanian Deadlift',
                    gif: 'https://v2.exercisedb.io/image/Wd6MwGxGkpZqmU',
                    description: 'Hamstring builder',
                    sets: '3 sets',
                    reps: '10-12 reps',
                    tips: 'Slight knee bend, push hips back, feel hamstring stretch.'
                },
                {
                    name: 'Leg Press',
                    gif: 'https://v2.exercisedb.io/image/sFz8PYxdXxLqxq',
                    description: 'Quad mass builder',
                    sets: '3 sets',
                    reps: '12-15 reps',
                    tips: 'Feet shoulder-width, lower until 90 degrees, push through heels.'
                },
                {
                    name: 'Walking Lunges',
                    gif: 'https://v2.exercisedb.io/image/Wd6MwGxGkpZqmU',
                    description: 'Functional leg strength',
                    sets: '3 sets',
                    reps: '10-12 each leg',
                    tips: 'Long stride, back knee almost touches ground, keep torso upright.'
                },
                {
                    name: 'Leg Curl',
                    gif: 'https://v2.exercisedb.io/image/sFz8PYxdXxLqxq',
                    description: 'Hamstring isolation',
                    sets: '3 sets',
                    reps: '12-15 reps',
                    tips: 'Curl heels to glutes, squeeze at top, control the negative.'
                }
            ],
            'arms': [
                {
                    name: 'Barbell Curl',
                    gif: 'https://v2.exercisedb.io/image/sFz8PYxdXxLqxq',
                    description: 'Bicep mass builder',
                    sets: '3 sets',
                    reps: '8-12 reps',
                    tips: 'Keep elbows stationary, curl to shoulders, squeeze at top.'
                },
                {
                    name: 'Tricep Dips',
                    gif: 'https://v2.exercisedb.io/image/Wd6MwGxGkpZqmU',
                    description: 'Tricep mass builder',
                    sets: '3 sets',
                    reps: '8-12 reps',
                    tips: 'Lean forward slightly, lower until 90 degrees, push up explosively.'
                },
                {
                    name: 'Hammer Curls',
                    gif: 'https://v2.exercisedb.io/image/sFz8PYxdXxLqxq',
                    description: 'Bicep thickness',
                    sets: '3 sets',
                    reps: '10-12 reps',
                    tips: 'Neutral grip, curl up, keep elbows close to body.'
                },
                {
                    name: 'Overhead Tricep Extension',
                    gif: 'https://v2.exercisedb.io/image/Wd6MwGxGkpZqmU',
                    description: 'Long head tricep',
                    sets: '3 sets',
                    reps: '10-12 reps',
                    tips: 'Keep elbows close to head, lower behind head, extend fully.'
                },
                {
                    name: 'Cable Curl',
                    gif: 'https://v2.exercisedb.io/image/sFz8PYxdXxLqxq',
                    description: 'Bicep peak',
                    sets: '3 sets',
                    reps: '12-15 reps',
                    tips: 'Constant tension, squeeze at top, control the descent.'
                }
            ],
            'shoulders': [
                {
                    name: 'Overhead Press',
                    gif: 'https://v2.exercisedb.io/image/sFz8PYxdXxLqxq',
                    description: 'Overall shoulder mass',
                    sets: '3-4 sets',
                    reps: '8-12 reps',
                    tips: 'Press straight up, lock out at top, control the descent.'
                },
                {
                    name: 'Lateral Raises',
                    gif: 'https://v2.exercisedb.io/image/Wd6MwGxGkpZqmU',
                    description: 'Side delt width',
                    sets: '3 sets',
                    reps: '12-15 reps',
                    tips: 'Slight bend in elbows, raise to shoulder height, control down.'
                },
                {
                    name: 'Front Raises',
                    gif: 'https://v2.exercisedb.io/image/sFz8PYxdXxLqxq',
                    description: 'Front delt focus',
                    sets: '3 sets',
                    reps: '12-15 reps',
                    tips: 'Raise to eye level, keep core tight, alternate arms.'
                },
                {
                    name: 'Face Pulls',
                    gif: 'https://v2.exercisedb.io/image/Wd6MwGxGkpZqmU',
                    description: 'Rear delt health',
                    sets: '3 sets',
                    reps: '15-20 reps',
                    tips: 'Pull to face, external rotation, squeeze shoulder blades.'
                },
                {
                    name: 'Arnold Press',
                    gif: 'https://v2.exercisedb.io/image/sFz8PYxdXxLqxq',
                    description: 'All three heads',
                    sets: '3 sets',
                    reps: '10-12 reps',
                    tips: 'Start palms facing you, rotate as you press, full range.'
                }
            ],
            'full body': [
                {
                    name: 'Burpees',
                    gif: 'https://v2.exercisedb.io/image/sFz8PYxdXxLqxq',
                    description: 'Full body cardio',
                    sets: '3 sets',
                    reps: '10-15 reps',
                    tips: 'Jump down, push-up, jump up, explosive movement.'
                },
                {
                    name: 'Deadlift',
                    gif: 'https://v2.exercisedb.io/image/Wd6MwGxGkpZqmU',
                    description: 'Total body strength',
                    sets: '3 sets',
                    reps: '5-8 reps',
                    tips: 'Hinge at hips, back straight, drive through heels.'
                },
                {
                    name: 'Thrusters',
                    gif: 'https://v2.exercisedb.io/image/sFz8PYxdXxLqxq',
                    description: 'Squat to press',
                    sets: '3 sets',
                    reps: '10-12 reps',
                    tips: 'Squat deep, explode up, press overhead in one motion.'
                },
                {
                    name: 'Mountain Climbers',
                    gif: 'https://v2.exercisedb.io/image/Wd6MwGxGkpZqmU',
                    description: 'Core and cardio',
                    sets: '3 sets',
                    reps: '20-30 reps',
                    tips: 'Plank position, drive knees to chest, keep core tight.'
                },
                {
                    name: 'Kettlebell Swings',
                    gif: 'https://v2.exercisedb.io/image/sFz8PYxdXxLqxq',
                    description: 'Power and conditioning',
                    sets: '3 sets',
                    reps: '15-20 reps',
                    tips: 'Hip hinge, explosive swing to shoulder height, control descent.'
                }
            ]
        };

        // Show Exercise Library
        async function showExerciseLibrary(muscle) {
            const exercises = exerciseLibrary[muscle] || [];
            
            if (exercises.length === 0) {
                showError('No exercises found for this muscle group');
                return;
            }
            
            workoutTitle.textContent = `${muscle.charAt(0).toUpperCase() + muscle.slice(1)} Exercises`;
            workoutBadge.textContent = `${exercises.length} Exercises`;
            
            let html = '';
            exercises.forEach((exercise, index) => {
                html += `
                    <div class="exercise-card" style="background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.08); margin-bottom: 1.5rem;">
                        <div class="exercise-gif" style="width: 100%; height: 200px; background: #F0F8FA; display: flex; align-items: center; justify-content: center; position: relative;">
                            <img src="${exercise.gif}" alt="${exercise.name}" 
                                 style="max-width: 100%; max-height: 100%; object-fit: contain;"
                                 onerror="this.src='https://via.placeholder.com/300x200?text=Exercise+Demo'">
                            <div style="position: absolute; top: 10px; right: 10px; background: rgba(74, 157, 181, 0.9); color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600;">
                                #${index + 1}
                            </div>
                        </div>
                        <div style="padding: 1.25rem;">
                            <h3 style="font-size: 1.125rem; font-weight: 700; color: #2c3e50; margin-bottom: 0.5rem;">
                                ${exercise.name}
                            </h3>
                            <p style="font-size: 0.875rem; color: #7f8c8d; margin-bottom: 1rem;">
                                ${exercise.description}
                            </p>
                            <div style="display: flex; gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap;">
                                <div style="background: #F0F8FA; padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.875rem;">
                                    <strong style="color: #4A9DB5;">📊 ${exercise.sets}</strong>
                                </div>
                                <div style="background: #F0F8FA; padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.875rem;">
                                    <strong style="color: #4A9DB5;">🔢 ${exercise.reps}</strong>
                                </div>
                            </div>
                            <div style="background: #FFF9E6; border-left: 3px solid #FFD93D; padding: 0.75rem; border-radius: 8px;">
                                <div style="font-size: 0.75rem; font-weight: 600; color: #F39C12; margin-bottom: 0.25rem;">💡 PRO TIP</div>
                                <div style="font-size: 0.875rem; color: #7f8c8d;">${exercise.tips}</div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            workoutContent.innerHTML = html;
        }

        // Generate workout with tracking capability
        async function generateWorkoutWithTracking(prompt, title) {
            try {
                const response = await window.NutriCoach.Chat.sendMessage(prompt);
                
                if (response.success || response.response) {
                    const aiResponse = response.response || response.data?.response;
                    displayWorkoutWithTracking(aiResponse, title);
                } else {
                    showError('Failed to generate workout. Please try again.');
                }
            } catch (error) {
                console.error('Error:', error);
                showError('An error occurred. Please try again.');
            }
        }

        // Display workout with Start button and tracking
        function displayWorkoutWithTracking(content, title) {
            workoutTitle.textContent = title;
            workoutBadge.textContent = 'Ready to Start';
            
            // Parse exercises from AI response
            const lines = content.split('\n').filter(line => line.trim());
            currentExercises = [];
            
            lines.forEach(line => {
                line = line.trim();
                
                // Skip greeting lines and non-exercise content
                if (line.toLowerCase().includes('hey') || 
                    line.toLowerCase().includes('hello') ||
                    line.toLowerCase().includes('let\'s') ||
                    line.toLowerCase().includes('here\'s') ||
                    line.toLowerCase().includes('goal') ||
                    line.length < 10) {
                    return;
                }
                
                // Only parse lines that look like exercises
                if (line.match(/^\d+\.|^-|^•/)) {
                    const cleanLine = line.replace(/^\d+\.|^-|^•/, '').trim();
                    const parts = cleanLine.split(':');
                    
                    if (parts.length >= 2) {
                        const name = parts[0].trim();
                        const details = parts[1].trim();
                        
                        // Make sure it looks like an exercise (has sets/reps or similar)
                        if (details.match(/\d+/) && (details.includes('set') || details.includes('rep') || details.includes('x'))) {
                            currentExercises.push({ name, details });
                        }
                    }
                }
            });
            
            if (currentExercises.length === 0) {
                showError('Could not parse workout. Please try again.');
                return;
            }
            
            // Show Start Workout button
            let html = `
                <div style="text-align: center; padding: 2rem; background: linear-gradient(135deg, #4A9DB5 0%, #3D8BA3 100%); border-radius: 16px; color: white; margin-bottom: 2rem;">
                    <h2 style="margin-bottom: 1rem; font-size: 1.5rem;">💪 Ready to Start?</h2>
                    <p style="margin-bottom: 1.5rem; opacity: 0.9;">${currentExercises.length} exercises • Earn ${currentExercises.length * 10 + 50} XP</p>
                    <button onclick="startWorkoutSession()" style="background: white; color: #4A9DB5; border: none; padding: 1rem 2rem; border-radius: 12px; font-size: 1.125rem; font-weight: 700; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.2);">
                        🚀 Start Workout
                    </button>
                </div>
                
                <div style="background: #F8FAFB; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem;">
                    <h3 style="margin-bottom: 0.5rem; color: #2c3e50;">📋 Workout Plan:</h3>
            `;
            
            currentExercises.forEach((ex, index) => {
                html += `
                    <div style="padding: 0.75rem; margin: 0.5rem 0; background: white; border-radius: 8px; border-left: 3px solid #4A9DB5;">
                        <strong>${index + 1}. ${ex.name}</strong>
                        <div style="font-size: 0.875rem; color: #7f8c8d; margin-top: 0.25rem;">${ex.details}</div>
                    </div>
                `;
            });
            
            html += '</div>';
            workoutContent.innerHTML = html;
        }

        // Start workout session
        async function startWorkoutSession() {
            try {
                const response = await fetch('../api/workout/start-session.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        workout_type: workoutTitle.textContent,
                        total_exercises: currentExercises.length,
                        exercises: currentExercises
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    currentSession = data.data.session_id;
                    completedExercises.clear();
                    displayTrackingWorkout();
                    
                    if (data.data.resumed) {
                        showNotification('💪 Continuing your workout!', 'success');
                    } else {
                        showNotification('💪 Workout Started! Let\'s go!', 'success');
                    }
                } else {
                    // Show error message (e.g., already completed today)
                    showAlreadyCompletedMessage(data.message || 'Cannot start workout');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('An error occurred', 'error');
            }
        }
        
        // Show message when workout already completed today
        function showAlreadyCompletedMessage(message) {
            workoutContent.innerHTML = `
                <div style="text-align: center; padding: 3rem; background: linear-gradient(135deg, #FFD93D 0%, #F39C12 100%); border-radius: 16px; color: white;">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">🏆</div>
                    <h2 style="font-size: 1.75rem; margin-bottom: 1rem;">Workout Already Complete!</h2>
                    <p style="font-size: 1.125rem; margin-bottom: 2rem; opacity: 0.95;">
                        ${message}
                    </p>
                    <div style="background: rgba(255,255,255,0.2); padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem;">
                        <p style="font-size: 0.875rem; margin-bottom: 0.5rem; opacity: 0.9;">Rest is important for muscle growth!</p>
                        <p style="font-size: 1rem; font-weight: 600;">Come back tomorrow to earn more XP 💪</p>
                    </div>
                    <button onclick="location.href='dashboard.php'" style="background: white; color: #F39C12; border: none; padding: 1rem 2rem; border-radius: 12px; font-size: 1.125rem; font-weight: 700; cursor: pointer;">
                        Back to Dashboard
                    </button>
                </div>
            `;
            showNotification(message, 'info', 5000);
        }

        // Display workout with tracking buttons
        function displayTrackingWorkout() {
            workoutBadge.textContent = 'In Progress';
            
            let html = '';
            currentExercises.forEach((ex, index) => {
                const isCompleted = completedExercises.has(index);
                html += `
                    <div class="exercise-card" style="background: ${isCompleted ? '#E8F5E9' : 'white'}; border-radius: 16px; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border-left: 4px solid ${isCompleted ? '#4CAF50' : '#4A9DB5'};">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                            <div style="flex: 1;">
                                <h3 style="font-size: 1.125rem; font-weight: 700; color: #2c3e50; margin-bottom: 0.5rem;">
                                    ${isCompleted ? '✅' : `${index + 1}.`} ${ex.name}
                                </h3>
                                <p style="font-size: 0.875rem; color: #7f8c8d;">${ex.details}</p>
                            </div>
                        </div>
                        ${!isCompleted ? `
                            <button onclick="completeExercise(${index}, '${ex.name.replace(/'/g, "\\'")}', '${ex.details}')" 
                                    style="width: 100%; background: linear-gradient(135deg, #4A9DB5 0%, #3D8BA3 100%); color: white; border: none; padding: 1rem; border-radius: 12px; font-size: 1rem; font-weight: 600; cursor: pointer;">
                                ✅ Complete Exercise (+10 XP)
                            </button>
                        ` : `
                            <div style="text-align: center; padding: 1rem; background: #4CAF50; color: white; border-radius: 12px; font-weight: 600;">
                                ✅ Completed! +10 XP
                            </div>
                        `}
                    </div>
                `;
            });
            
            // Add Finish Workout button if all exercises completed
            if (completedExercises.size === currentExercises.length) {
                html += `
                    <div style="text-align: center; padding: 2rem; background: linear-gradient(135deg, #4CAF50 0%, #45A049 100%); border-radius: 16px; color: white; margin-top: 2rem;">
                        <h2 style="margin-bottom: 1rem; font-size: 1.5rem;">🎉 All Exercises Complete!</h2>
                        <p style="margin-bottom: 1.5rem; opacity: 0.9;">Finish workout to earn +50 XP bonus!</p>
                        <button onclick="finishWorkout()" style="background: white; color: #4CAF50; border: none; padding: 1rem 2rem; border-radius: 12px; font-size: 1.125rem; font-weight: 700; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.2);">
                            🏆 Finish Workout (+50 XP)
                        </button>
                    </div>
                `;
            }
            
            workoutContent.innerHTML = html;
        }

        // Complete exercise
        async function completeExercise(index, name, details) {
            try {
                const response = await fetch('../api/workout/complete-exercise.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        session_id: currentSession,
                        exercise_name: name,
                        sets: 3,
                        reps: 10
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    completedExercises.add(index);
                    displayTrackingWorkout();
                    
                    if (data.data.leveled_up) {
                        showNotification(`🎉 LEVEL UP! You're now Level ${data.data.level}!`, 'success', 5000);
                    } else {
                        showNotification(`💪 +${data.data.xp_earned} XP! Total: ${data.data.total_xp} XP`, 'success');
                    }
                } else {
                    showNotification('Failed to complete exercise', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('An error occurred', 'error');
            }
        }

        // Finish workout
        async function finishWorkout() {
            try {
                const response = await fetch('../api/workout/finish-session.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        session_id: currentSession
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    const result = data.data;
                    let message = `🎉 Workout Complete!\n\n`;
                    message += `💪 Exercises: ${result.exercises_completed}\n`;
                    message += `⭐ Total XP: +${result.total_xp_earned}\n`;
                    
                    if (result.leveled_up) {
                        message += `\n🎊 LEVEL UP to Level ${result.level}!`;
                    }
                    
                    if (result.achievements && result.achievements.length > 0) {
                        message += `\n\n🏆 Achievements Unlocked:\n`;
                        result.achievements.forEach(ach => {
                            message += `• ${ach.name} (+${ach.xp} XP)\n`;
                        });
                    }
                    
                    showNotification(message, 'success', 8000);
                    
                    // Show summary
                    workoutContent.innerHTML = `
                        <div style="text-align: center; padding: 3rem; background: linear-gradient(135deg, #4CAF50 0%, #45A049 100%); border-radius: 16px; color: white;">
                            <div style="font-size: 4rem; margin-bottom: 1rem;">🏆</div>
                            <h2 style="font-size: 2rem; margin-bottom: 1rem;">Workout Complete!</h2>
                            <div style="font-size: 1.25rem; margin-bottom: 2rem;">
                                <div>💪 ${result.exercises_completed} exercises completed</div>
                                <div>⭐ +${result.total_xp_earned} XP earned</div>
                                ${result.leveled_up ? `<div style="margin-top: 1rem; font-size: 1.5rem;">🎊 Level ${result.level}!</div>` : ''}
                            </div>
                            <button onclick="location.reload()" style="background: white; color: #4CAF50; border: none; padding: 1rem 2rem; border-radius: 12px; font-size: 1.125rem; font-weight: 700; cursor: pointer;">
                                Start New Workout
                            </button>
                        </div>
                    `;
                    
                    currentSession = null;
                    completedExercises.clear();
                } else {
                    showNotification('Failed to finish workout', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('An error occurred', 'error');
            }
        }

        // Check for existing workout session on page load
        async function checkExistingSession() {
            try {
                const response = await fetch('../api/workout/get-current-session.php');
                const data = await response.json();
                
                if (data.success && data.data.has_session) {
                    const session = data.data;
                    currentSession = session.session_id;
                    currentExercises = session.exercises;
                    
                    // Mark completed exercises
                    completedExercises.clear();
                    session.completed_exercises.forEach(exerciseName => {
                        const index = currentExercises.findIndex(ex => ex.name === exerciseName);
                        if (index !== -1) {
                            completedExercises.add(index);
                        }
                    });
                    
                    // Show the workout in progress
                    workoutDisplay.classList.add('active');
                    workoutTitle.textContent = session.workout_type;
                    workoutBadge.textContent = 'In Progress';
                    displayTrackingWorkout();
                    
                    showNotification('💪 Resuming your workout!', 'success');
                }
            } catch (error) {
                console.error('Error checking session:', error);
            }
        }
        
        // Check for existing session on page load
        checkExistingSession();

        // Show notification
        function showNotification(message, type = 'info', duration = 3000) {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${type === 'success' ? '#4CAF50' : type === 'error' ? '#f44336' : '#4A9DB5'};
                color: white;
                padding: 1rem 1.5rem;
                border-radius: 12px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                z-index: 10000;
                max-width: 300px;
                white-space: pre-line;
                animation: slideIn 0.3s ease;
            `;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, duration);
        }
    </script>
    <style>
        @keyframes slideIn {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(400px); opacity: 0; }
        }
    </style>
</body>
</html>
