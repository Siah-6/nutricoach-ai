<?php
/**
 * Muscle-Specific Workout Page with Exercise Demonstrations
 */

require_once __DIR__ . '/../includes/functions.php';

initSession();

if (!isLoggedIn()) {
    redirect('/');
}

$muscle = isset($_GET['muscle']) ? $_GET['muscle'] : 'chest';
$muscleTitle = ucfirst($muscle);
$muscleIcon = [
    'chest' => '💪',
    'back' => '🏋️',
    'legs' => '🦵',
    'shoulders' => '💪',
    'arms' => '💪',
    'abs' => '🔥'
][$muscle] ?? '💪';

// Exercise library - Ready for local videos or GIFs
// TODO: Add 'video' => '../assets/videos/exercises/chest/pushups.mp4' after downloading
$exerciseLibrary = [
    'chest' => [
        ['name' => 'Push-ups', 'sets' => '3', 'reps' => '12-15', 'gif' => '../assets/videos/exercises/chest/push-ups.gif', 'tips' => 'Keep your body straight, core engaged'],
        ['name' => 'Bench Press', 'sets' => '4', 'reps' => '8-10', 'gif' => '../assets/videos/exercises/chest/bench-press.gif', 'tips' => 'Lower bar to chest, push up explosively'],
        ['name' => 'Dumbbell Flyes', 'sets' => '3', 'reps' => '10-12', 'gif' => '../assets/videos/exercises/chest/dumbbell-flyes.gif', 'tips' => 'Slight bend in elbows, feel the stretch'],
        ['name' => 'Incline Press', 'sets' => '3', 'reps' => '10-12', 'gif' => '../assets/videos/exercises/chest/incline-press.gif', 'tips' => 'Target upper chest, 30-45 degree angle'],
    ],
    'back' => [
        ['name' => 'Pull-ups', 'sets' => '3', 'reps' => '8-10', 'gif' => '../assets/videos/exercises/back/pull-ups.gif', 'tips' => 'Full range of motion, control the descent'],
        ['name' => 'Lat Pulldowns', 'sets' => '3', 'reps' => '10-12', 'gif' => '../assets/videos/exercises/back/lat-pulldowns.gif', 'tips' => 'Pull down to upper chest, control the weight'],
        ['name' => 'Deadlifts', 'sets' => '3', 'reps' => '6-8', 'gif' => '../assets/videos/exercises/back/deadlifts.gif', 'tips' => 'Keep back straight, drive through heels'],
        ['name' => 'Dumbbell Rows', 'sets' => '3', 'reps' => '10-12', 'gif' => '../assets/videos/exercises/back/dumbbell-rows.gif', 'tips' => 'One arm at a time, full stretch'],
        ['name' => 'Face Pulls', 'sets' => '3', 'reps' => '15-20', 'gif' => '../assets/videos/exercises/back/face-pulls.gif', 'tips' => 'Pull to face level, external rotation'],
    ],
    'legs' => [
        ['name' => 'Squats', 'sets' => '4', 'reps' => '10-12', 'gif' => '../assets/videos/exercises/legs/squats.gif', 'tips' => 'Depth to parallel, knees track over toes'],
        ['name' => 'Lunges', 'sets' => '3', 'reps' => '12 each leg', 'gif' => '../assets/videos/exercises/legs/lunges.gif', 'tips' => 'Step forward, 90 degree angles'],
        ['name' => 'Leg Extensions', 'sets' => '3', 'reps' => '12-15', 'gif' => '../assets/videos/exercises/legs/leg-extensions.gif', 'tips' => 'Extend fully, control the descent'],
    ],
    'shoulders' => [
        ['name' => 'Shoulder Press', 'sets' => '4', 'reps' => '8-10', 'gif' => 'https://www.inspireusafoundation.org/wp-content/uploads/2022/02/dumbbell-shoulder-press.gif', 'tips' => 'Press overhead, control descent'],
        ['name' => 'Lateral Raises', 'sets' => '3', 'reps' => '12-15', 'gif' => 'https://www.inspireusafoundation.org/wp-content/uploads/2022/02/dumbbell-lateral-raise.gif', 'tips' => 'Raise to shoulder height, slight bend in elbows'],
        ['name' => 'Front Raises', 'sets' => '3', 'reps' => '12-15', 'gif' => 'https://www.inspireusafoundation.org/wp-content/uploads/2022/02/dumbbell-front-raise.gif', 'tips' => 'Raise to eye level, control the weight'],
        ['name' => 'Rear Delt Flyes', 'sets' => '3', 'reps' => '12-15', 'gif' => 'https://www.inspireusafoundation.org/wp-content/uploads/2022/10/reverse-dumbbell-flys.gif', 'tips' => 'Bend forward, raise arms to sides'],
        ['name' => 'Arnold Press', 'sets' => '3', 'reps' => '10-12', 'gif' => 'https://www.inspireusafoundation.org/wp-content/uploads/2022/01/arnold-press.gif', 'tips' => 'Rotate palms as you press up'],
        ['name' => 'Upright Rows', 'sets' => '3', 'reps' => '10-12', 'gif' => 'https://www.inspireusafoundation.org/wp-content/uploads/2022/02/barbell-upright-row.gif', 'tips' => 'Pull to chin, elbows high'],
        ['name' => 'Shrugs', 'sets' => '3', 'reps' => '12-15', 'gif' => 'https://www.inspireusafoundation.org/wp-content/uploads/2022/02/dumbbell-shrug.gif', 'tips' => 'Lift shoulders straight up, squeeze traps'],
    ],
    'arms' => [
        ['name' => 'Bicep Curls', 'sets' => '3', 'reps' => '10-12', 'gif' => '../assets/videos/exercises/arms/bicep-curls.gif', 'tips' => 'Keep elbows stationary, full contraction'],
        ['name' => 'Tricep Dips', 'sets' => '3', 'reps' => '10-15', 'gif' => '../assets/videos/exercises/arms/tricep-dips.gif', 'tips' => 'Lower until 90 degrees, push back up'],
        ['name' => 'Hammer Curls', 'sets' => '3', 'reps' => '10-12', 'gif' => '../assets/videos/exercises/arms/hammer-curls.gif', 'tips' => 'Neutral grip, control the movement'],
        ['name' => 'Tricep Extensions', 'sets' => '3', 'reps' => '12-15', 'gif' => '../assets/videos/exercises/arms/tricep-extensions.gif', 'tips' => 'Keep upper arms still, extend fully'],
        ['name' => 'Preacher Curls', 'sets' => '3', 'reps' => '10-12', 'gif' => '../assets/videos/exercises/arms/preacher-curls.gif', 'tips' => 'Isolate biceps, full range'],
        ['name' => 'Concentration Curls', 'sets' => '3', 'reps' => '10-12', 'gif' => '../assets/videos/exercises/arms/concentration-curls.gif', 'tips' => 'Focus on peak contraction'],
    ],
    'abs' => [
        ['name' => 'Russian Twists', 'sets' => '3', 'reps' => '20-30', 'gif' => '../assets/videos/exercises/abs/russian-twists.gif', 'tips' => 'Rotate torso, touch floor each side'],
        ['name' => 'Crunches', 'sets' => '3', 'reps' => '15-20', 'gif' => 'https://www.inspireusafoundation.org/wp-content/uploads/2022/03/crunch.gif', 'tips' => 'Lift shoulder blades, don\'t pull neck'],
        ['name' => 'Plank', 'sets' => '3', 'reps' => '45-60 sec', 'gif' => 'https://www.inspireusafoundation.org/wp-content/uploads/2022/09/forearm-plank.gif', 'tips' => 'Keep body straight, engage core'],
        ['name' => 'Leg Raises', 'sets' => '3', 'reps' => '12-15', 'gif' => 'https://www.inspireusafoundation.org/wp-content/uploads/2022/03/lying-leg-raise.gif', 'tips' => 'Keep legs straight, lower slowly'],
        ['name' => 'Bicycle Crunches', 'sets' => '3', 'reps' => '20-30', 'gif' => 'https://www.inspireusafoundation.org/wp-content/uploads/2022/03/bicycle-crunch.gif', 'tips' => 'Alternate sides, touch elbow to knee'],
        ['name' => 'Mountain Climbers', 'sets' => '3', 'reps' => '30-40', 'gif' => 'https://www.inspireusafoundation.org/wp-content/uploads/2022/10/mountain-climber.gif', 'tips' => 'Fast pace, keep core tight'],
        ['name' => 'Side Plank', 'sets' => '3', 'reps' => '30-45 sec each', 'gif' => 'https://www.inspireusafoundation.org/wp-content/uploads/2022/09/side-plank.gif', 'tips' => 'Keep body straight, hold position'],
        ['name' => 'V-Ups', 'sets' => '3', 'reps' => '12-15', 'gif' => 'https://www.inspireusafoundation.org/wp-content/uploads/2023/06/v-up.gif', 'tips' => 'Touch hands to feet, control descent'],
    ],
];

$exercises = $exerciseLibrary[$muscle] ?? $exerciseLibrary['chest'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0A1628">
    <title><?php echo $muscleTitle; ?> Workout - NutriCoach AI</title>
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
                <h1><?php echo $muscleIcon; ?> <?php echo $muscleTitle; ?> Workout</h1>
                <p>Exercise demonstrations included</p>
            </div>
        </div>

        <!-- Workout Content -->
        <div class="workout-content">
            <!-- Workout Info -->
            <div class="workout-info">
                <div class="info-badge" id="workoutBadge">Ready to Start</div>
                <h2><?php echo $muscleTitle; ?> Training</h2>
                <div class="workout-stats">
                    <div class="stat-item">
                        <span class="stat-icon">💪</span>
                        <span class="stat-value"><?php echo count($exercises); ?></span>
                        <span class="stat-label">Exercises</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-icon">⭐</span>
                        <span class="stat-value"><?php echo count($exercises) * 20 + 50; ?></span>
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

            <!-- Exercise List with Video Demonstrations -->
            <div class="exercise-list" id="exerciseList">
                <?php foreach ($exercises as $index => $exercise): ?>
                <div class="exercise-card-with-demo" id="exercise-<?php echo $index; ?>">
                    <div class="exercise-demo">
                        <?php if (isset($exercise['video'])): ?>
                            <video class="exercise-video" autoplay loop muted playsinline crossorigin="anonymous">
                                <source src="<?php echo $exercise['video']; ?>" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                            <div class="video-fallback" style="display: none;">
                                <div class="exercise-placeholder">
                                    <div class="placeholder-icon">🏋️</div>
                                    <div class="placeholder-name"><?php echo $exercise['name']; ?></div>
                                    <div class="placeholder-label">Follow the tips below</div>
                                </div>
                            </div>
                        <?php elseif (isset($exercise['gif'])): ?>
                            <img src="<?php echo $exercise['gif']; ?>" alt="<?php echo $exercise['name']; ?>" class="exercise-gif">
                        <?php else: ?>
                            <div class="exercise-placeholder">
                                <div class="placeholder-icon">🏋️</div>
                                <div class="placeholder-name"><?php echo $exercise['name']; ?></div>
                                <div class="placeholder-label">Follow the tips below</div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="exercise-details">
                        <div class="exercise-header">
                            <div class="exercise-number"><?php echo $index + 1; ?></div>
                            <div class="exercise-info">
                                <h3><?php echo $exercise['name']; ?></h3>
                                <p><?php echo $exercise['sets']; ?> sets × <?php echo $exercise['reps']; ?> reps</p>
                                <p class="exercise-tips">💡 <?php echo $exercise['tips']; ?></p>
                            </div>
                            <button class="btn-check" onclick="toggleExercise(<?php echo $index; ?>)"></button>
                        </div>
                        <!-- Rest Timer -->
                        <div class="rest-timer-container" id="restTimer<?php echo $index; ?>" style="display: none;">
                            <div class="rest-timer-content">
                                <div class="rest-timer-label">Rest Time</div>
                                <div class="rest-timer-display" id="restDisplay<?php echo $index; ?>">01:30</div>
                                <div class="rest-timer-progress">
                                    <div class="rest-timer-bar" id="restBar<?php echo $index; ?>"></div>
                                </div>
                                <div class="rest-timer-actions">
                                    <button class="btn-rest-action" onclick="skipRest(<?php echo $index; ?>)">Skip</button>
                                    <button class="btn-rest-action" onclick="addRestTime(<?php echo $index; ?>, 30)">+30s</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Complete Workout Button -->
            <div class="complete-section" id="completeSection" style="display: none;">
                <button class="btn-complete-workout" onclick="completeWorkout()">
                    ✅ Complete Workout
                </button>
            </div>
        </div>
    </div>

    <script>
        const totalExercises = <?php echo count($exercises); ?>;
        const muscleType = '<?php echo $muscle; ?>';
        let completedExercises = new Set();
        let currentSession = null;
        let restTimer = null;
        let restTimeRemaining = 0;
        let currentRestExercise = null;

        // Restore state on page load
        window.addEventListener('DOMContentLoaded', async () => {
            // Check if already completed today
            await checkCompletionStatus();
            
            const savedState = sessionStorage.getItem(`muscleWorkout_${muscleType}`);
            if (savedState) {
                restoreWorkoutState(JSON.parse(savedState));
            }
            
            // Add error handling for videos and images
            document.querySelectorAll('.exercise-video').forEach(video => {
                video.addEventListener('error', function(e) {
                    console.error('Video failed to load:', this.querySelector('source').src);
                    console.error('Error details:', e);
                    this.style.display = 'none';
                    const fallback = this.nextElementSibling;
                    if (fallback && fallback.classList.contains('video-fallback')) {
                        fallback.style.display = 'block';
                    }
                });
                
                video.addEventListener('loadeddata', function() {
                    console.log('Video loaded successfully:', this.querySelector('source').src);
                });
            });
            
            document.querySelectorAll('.exercise-gif').forEach(img => {
                img.onerror = function() {
                    console.log('GIF failed to load:', this.src);
                    this.style.display = 'none';
                    const exerciseName = this.alt;
                    const placeholder = document.createElement('div');
                    placeholder.className = 'exercise-placeholder';
                    placeholder.innerHTML = `
                        <div class="placeholder-icon">🏋️</div>
                        <div class="placeholder-name">${exerciseName}</div>
                        <div class="placeholder-label">Follow the tips below</div>
                    `;
                    this.parentElement.appendChild(placeholder);
                };
            });
        });

        async function checkCompletionStatus() {
            try {
                const response = await fetch(`../api/workout/check-completion.php?workout_type=<?php echo urlencode($muscleTitle . ' Workout'); ?>`);
                const data = await response.json();
                
                if (data.success && data.completed_today) {
                    // Show completed badge
                    const badge = document.getElementById('workoutBadge');
                    badge.textContent = '✅ Completed Today';
                    badge.style.background = '#10b981';
                    
                    // Show info message
                    const infoDiv = document.createElement('div');
                    infoDiv.style.cssText = `
                        background: rgba(16, 185, 129, 0.1);
                        border: 1px solid rgba(16, 185, 129, 0.3);
                        border-radius: 10px;
                        padding: 1rem;
                        margin-bottom: 1rem;
                        color: #10b981;
                        text-align: center;
                    `;
                    infoDiv.innerHTML = '✅ You already completed this workout today! You can still do it again, but no XP will be awarded.';
                    
                    document.querySelector('.workout-content').insertBefore(infoDiv, document.querySelector('.workout-content').firstChild);
                }
            } catch (error) {
                console.error('Error checking completion:', error);
            }
        }

        function saveWorkoutState() {
            const state = {
                muscle: muscleType,
                completed: Array.from(completedExercises),
                sessionId: currentSession,
                hasStarted: currentSession !== null
            };
            sessionStorage.setItem(`muscleWorkout_${muscleType}`, JSON.stringify(state));
        }

        function restoreWorkoutState(state) {
            completedExercises = new Set(state.completed);
            currentSession = state.sessionId;

            if (state.hasStarted) {
                document.getElementById('startSection').style.display = 'none';
                document.getElementById('workoutBadge').textContent = 'In Progress';
                document.getElementById('workoutBadge').style.background = '#FF9800';

                // Restore checked exercises
                state.completed.forEach(index => {
                    const card = document.getElementById(`exercise-${index}`);
                    if (card) {
                        const btn = card.querySelector('.btn-check');
                        card.classList.add('completed');
                        btn.classList.add('checked');
                        btn.textContent = '✓';
                    }
                });

                if (completedExercises.size === totalExercises) {
                    document.getElementById('completeSection').style.display = 'block';
                }
            }
        }

        async function startWorkout() {
            document.getElementById('startSection').style.display = 'none';
            document.getElementById('workoutBadge').textContent = 'In Progress';
            document.getElementById('workoutBadge').style.background = '#FF9800';

            const exercises = <?php echo json_encode(array_map(function($ex) {
                return ['name' => $ex['name'], 'details' => $ex['sets'] . ' sets × ' . $ex['reps'] . ' reps'];
            }, $exercises)); ?>;

            try {
                const response = await fetch('../api/workout/start-session.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        workout_type: '<?php echo $muscleTitle; ?> Workout',
                        total_exercises: totalExercises,
                        exercises: exercises
                    })
                });

                const data = await response.json();
                if (data.success) {
                    currentSession = data.session_id;
                    saveWorkoutState(); // Save after starting
                }
            } catch (error) {
                console.error('Error starting session:', error);
            }
        }

        async function toggleExercise(index) {
            if (!currentSession) {
                alert('Please start the workout first!');
                return;
            }

            const card = document.getElementById(`exercise-${index}`);
            const btn = card.querySelector('.btn-check');

            if (completedExercises.has(index)) {
                completedExercises.delete(index);
                card.classList.remove('completed');
                btn.classList.remove('checked');
                btn.textContent = '';
            } else {
                completedExercises.add(index);
                card.classList.add('completed');
                btn.classList.add('checked');
                btn.textContent = '✓';

                const exerciseName = card.querySelector('h3').textContent;
                try {
                    await fetch('../api/workout/complete-exercise.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            session_id: currentSession,
                            exercise_name: exerciseName
                        })
                    });
                } catch (error) {
                    console.error('Error completing exercise:', error);
                }

                // Start rest timer if not the last exercise
                if (index < totalExercises - 1) {
                    startRestTimer(index, exerciseName);
                }
            }

            saveWorkoutState(); // Save after each toggle

            if (completedExercises.size === totalExercises) {
                document.getElementById('completeSection').style.display = 'block';
            } else {
                document.getElementById('completeSection').style.display = 'none';
            }
        }

        async function completeWorkout() {
            if (completedExercises.size !== totalExercises) {
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
                    sessionStorage.removeItem(`muscleWorkout_${muscleType}`);
                    
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

        // Rest Timer Functions
        function getRestTime(exerciseName) {
            const name = exerciseName.toLowerCase();
            
            // Compound exercises: 2-3 minutes
            if (name.includes('squat') || name.includes('deadlift') || name.includes('bench press') || 
                name.includes('overhead press') || name.includes('row')) {
                return 150; // 2.5 minutes
            }
            
            // Bodyweight/cardio: 30-60 seconds
            if (name.includes('push-up') || name.includes('pull-up') || name.includes('burpee') || 
                name.includes('mountain climber') || name.includes('plank')) {
                return 45; // 45 seconds
            }
            
            // Isolation exercises: 60-90 seconds (default)
            return 75; // 1 minute 15 seconds
        }

        function startRestTimer(index, exerciseName) {
            const restTime = getRestTime(exerciseName);
            restTimeRemaining = restTime;
            currentRestExercise = index;
            
            const timerContainer = document.getElementById(`restTimer${index}`);
            if (timerContainer) {
                timerContainer.style.display = 'block';
                updateRestDisplay(index);
                
                restTimer = setInterval(() => {
                    restTimeRemaining--;
                    updateRestDisplay(index);
                    
                    if (restTimeRemaining <= 0) {
                        finishRest(index);
                    }
                }, 1000);
            }
        }

        function updateRestDisplay(index) {
            const minutes = Math.floor(restTimeRemaining / 60);
            const seconds = restTimeRemaining % 60;
            const display = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            const displayEl = document.getElementById(`restDisplay${index}`);
            const barEl = document.getElementById(`restBar${index}`);
            
            if (displayEl) displayEl.textContent = display;
            if (barEl) {
                const card = document.getElementById(`exercise-${index}`);
                const exerciseName = card.querySelector('h3').textContent;
                const totalTime = getRestTime(exerciseName);
                const progress = ((totalTime - restTimeRemaining) / totalTime) * 100;
                barEl.style.width = progress + '%';
            }
        }

        function finishRest(index) {
            clearInterval(restTimer);
            restTimer = null;
            
            const timerContainer = document.getElementById(`restTimer${index}`);
            if (timerContainer) {
                timerContainer.style.display = 'none';
            }
            
            // Vibrate and play sound
            if (navigator.vibrate) {
                navigator.vibrate([200, 100, 200]);
            }
            
            // Show notification
            showNotification('✅ Rest complete! Ready for next exercise?');
        }

        function skipRest(index) {
            clearInterval(restTimer);
            restTimer = null;
            restTimeRemaining = 0;
            
            const timerContainer = document.getElementById(`restTimer${index}`);
            if (timerContainer) {
                timerContainer.style.display = 'none';
            }
        }

        function addRestTime(index, seconds) {
            restTimeRemaining += seconds;
            updateRestDisplay(index);
        }

        function showNotification(message) {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                left: 50%;
                transform: translateX(-50%);
                background: #4CAF50;
                color: white;
                padding: 1rem 2rem;
                border-radius: 10px;
                z-index: 10001;
                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                font-weight: 500;
            `;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.transition = 'opacity 0.3s ease';
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
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
