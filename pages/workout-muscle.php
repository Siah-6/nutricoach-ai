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
        ['name' => 'Push-ups', 'sets' => '3', 'reps' => '12-15', 'gif' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Push-Up.gif', 'tips' => 'Keep your body straight, core engaged'],
        ['name' => 'Bench Press', 'sets' => '4', 'reps' => '8-10', 'gif' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Barbell-Bench-Press.gif', 'tips' => 'Lower bar to chest, push up explosively'],
        ['name' => 'Dumbbell Flyes', 'sets' => '3', 'reps' => '10-12', 'gif' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Dumbbell-Fly.gif', 'tips' => 'Slight bend in elbows, feel the stretch'],
        ['name' => 'Incline Press', 'sets' => '3', 'reps' => '10-12', 'gif' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Incline-Barbell-Bench-Press.gif', 'tips' => 'Target upper chest, 30-45 degree angle'],
        ['name' => 'Decline Press', 'sets' => '3', 'reps' => '10-12', 'gif' => 'https://fitnessprogramer.com/wp-content/uploads/2021/06/Decline-Dumbbell-Press.gif', 'tips' => 'Target lower chest, control the weight'],
        ['name' => 'Cable Crossover', 'sets' => '3', 'reps' => '12-15', 'gif' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/cable-cross-over.gif', 'tips' => 'Squeeze at center, control the movement'],
        ['name' => 'Dumbbell Press', 'sets' => '4', 'reps' => '8-10', 'gif' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Dumbbell-Bench-Press.gif', 'tips' => 'Full range of motion, press straight up'],
        ['name' => 'Chest Dips', 'sets' => '3', 'reps' => '8-12', 'gif' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Chest-Dips.gif', 'tips' => 'Lean forward, lower until stretch'],
    ],
    'back' => [
        ['name' => 'Pull-ups', 'sets' => '3', 'reps' => '8-10', 'gif' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Pull-up.gif', 'tips' => 'Full range of motion, control the descent'],
        ['name' => 'Barbell Rows', 'sets' => '4', 'reps' => '8-10', 'gif' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Barbell-Row.gif', 'tips' => 'Pull to lower chest, squeeze shoulder blades'],
        ['name' => 'Lat Pulldowns', 'sets' => '3', 'reps' => '10-12', 'gif' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Lat-Pulldown.gif', 'tips' => 'Pull down to upper chest, control the weight'],
        ['name' => 'Deadlifts', 'sets' => '3', 'reps' => '6-8', 'gif' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Barbell-Deadlift.gif', 'tips' => 'Keep back straight, drive through heels'],
        ['name' => 'T-Bar Rows', 'sets' => '3', 'reps' => '10-12', 'gif' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/T-Bar-Row.gif', 'tips' => 'Pull to chest, keep core tight'],
        ['name' => 'Seated Cable Rows', 'sets' => '3', 'reps' => '10-12', 'gif' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Cable-Seated-Row.gif', 'tips' => 'Pull to abdomen, squeeze back'],
        ['name' => 'Dumbbell Rows', 'sets' => '3', 'reps' => '10-12', 'gif' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Dumbbell-Row.gif', 'tips' => 'One arm at a time, full stretch'],
        ['name' => 'Face Pulls', 'sets' => '3', 'reps' => '15-20', 'gif' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Face-Pull.gif', 'tips' => 'Pull to face level, external rotation'],
    ],
    'legs' => [
        ['name' => 'Squats', 'sets' => '4', 'reps' => '10-12', 'gif' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/BARBELL-SQUAT.gif', 'tips' => 'Depth to parallel, knees track over toes'],
        ['name' => 'Lunges', 'sets' => '3', 'reps' => '12 each leg', 'gif' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Dumbbell-Lunge.gif', 'tips' => 'Step forward, 90 degree angles'],
        ['name' => 'Leg Press', 'sets' => '3', 'reps' => '12-15', 'gif' => 'https://fitnessprogramer.com/wp-content/uploads/2022/02/Leg-Press.gif', 'tips' => 'Full range, don\'t lock knees'],
        ['name' => 'Romanian Deadlifts', 'sets' => '3', 'reps' => '10-12', 'gif' => 'https://fitnessprogramer.com/wp-content/uploads/2022/02/Barbell-Romanian-Deadlift.gif', 'tips' => 'Hinge at hips, feel hamstring stretch'],
        ['name' => 'Leg Curls', 'sets' => '3', 'reps' => '12-15', 'gif' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Lying-Leg-Curl.gif', 'tips' => 'Control the weight, squeeze at top'],
        ['name' => 'Leg Extensions', 'sets' => '3', 'reps' => '12-15', 'gif' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/LEG-EXTENSION.gif', 'tips' => 'Extend fully, control the descent'],
        ['name' => 'Bulgarian Split Squats', 'sets' => '3', 'reps' => '10 each leg', 'gif' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Dumbbell-Bulgarian-Split-Squat.gif', 'tips' => 'Rear foot elevated, front leg works'],
        ['name' => 'Calf Raises', 'sets' => '4', 'reps' => '15-20', 'gif' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Barbell-Standing-Calf-Raise.gif', 'tips' => 'Full extension, pause at top'],
    ],
    'shoulders' => [
        ['name' => 'Shoulder Press', 'sets' => '4', 'reps' => '8-10', 'gif' => 'https://newlife.com.cy/wp-content/uploads/2019/11/Dumbbell-Shoulder-Press_shoulder.gif', 'tips' => 'Press overhead, control descent'],
        ['name' => 'Lateral Raises', 'sets' => '3', 'reps' => '12-15', 'gif' => 'https://newlife.com.cy/wp-content/uploads/2019/11/dumbbell-lateral-raise.gif', 'tips' => 'Raise to shoulder height, slight bend in elbows'],
        ['name' => 'Front Raises', 'sets' => '3', 'reps' => '12-15', 'gif' => 'https://newlife.com.cy/wp-content/uploads/2019/11/dumbbell-front-raise.gif', 'tips' => 'Raise to eye level, control the weight'],
        ['name' => 'Rear Delt Flyes', 'sets' => '3', 'reps' => '12-15', 'gif' => 'https://newlife.com.cy/wp-content/uploads/2019/11/Dumbbell-Reverse-Fly.gif', 'tips' => 'Bend forward, raise arms to sides'],
        ['name' => 'Arnold Press', 'sets' => '3', 'reps' => '10-12', 'gif' => 'https://newlife.com.cy/wp-content/uploads/2019/11/Arnold-Dumbbell-Press.gif', 'tips' => 'Rotate palms as you press up'],
        ['name' => 'Upright Rows', 'sets' => '3', 'reps' => '10-12', 'gif' => 'https://newlife.com.cy/wp-content/uploads/2019/11/barbell-upright-row.gif', 'tips' => 'Pull to chin, elbows high'],
        ['name' => 'Cable Lateral Raises', 'sets' => '3', 'reps' => '12-15', 'gif' => 'https://newlife.com.cy/wp-content/uploads/2019/11/Cable-Lateral-Raise.gif', 'tips' => 'Constant tension, control movement'],
        ['name' => 'Shrugs', 'sets' => '3', 'reps' => '12-15', 'gif' => 'https://newlife.com.cy/wp-content/uploads/2019/11/barbell-shrug.gif', 'tips' => 'Lift shoulders straight up, squeeze traps'],
    ],
    'arms' => [
        ['name' => 'Bicep Curls', 'sets' => '3', 'reps' => '10-12', 'gif' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Dumbbell-Curl.gif', 'tips' => 'Keep elbows stationary, full contraction'],
        ['name' => 'Tricep Dips', 'sets' => '3', 'reps' => '10-15', 'gif' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Bench-Dips.gif', 'tips' => 'Lower until 90 degrees, push back up'],
        ['name' => 'Hammer Curls', 'sets' => '3', 'reps' => '10-12', 'gif' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Hammer-Curl.gif', 'tips' => 'Neutral grip, control the movement'],
        ['name' => 'Tricep Extensions', 'sets' => '3', 'reps' => '12-15', 'gif' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Dumbbell-Triceps-Extension.gif', 'tips' => 'Keep upper arms still, extend fully'],
        ['name' => 'Cable Curls', 'sets' => '3', 'reps' => '12-15', 'gif' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Standing-Cable-Curl.gif', 'tips' => 'Constant tension, squeeze at top'],
        ['name' => 'Skull Crushers', 'sets' => '3', 'reps' => '10-12', 'gif' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Barbell-Lying-Triceps-Extension.gif', 'tips' => 'Lower to forehead, extend fully'],
        ['name' => 'Preacher Curls', 'sets' => '3', 'reps' => '10-12', 'gif' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Dumbbell-Preacher-Curl.gif', 'tips' => 'Isolate biceps, full range'],
        ['name' => 'Tricep Pushdowns', 'sets' => '3', 'reps' => '12-15', 'gif' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Cable-Pushdown.gif', 'tips' => 'Keep elbows tucked, extend fully'],
        ['name' => 'Concentration Curls', 'sets' => '3', 'reps' => '10-12', 'gif' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Concentration-Curl.gif', 'tips' => 'Focus on peak contraction'],
    ],
    'abs' => [
        ['name' => 'Crunches', 'sets' => '3', 'reps' => '15-20', 'gif' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Crunches.gif', 'tips' => 'Lift shoulder blades, don\'t pull neck'],
        ['name' => 'Plank', 'sets' => '3', 'reps' => '45-60 sec', 'gif' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Front-Plank.gif', 'tips' => 'Keep body straight, engage core'],
        ['name' => 'Russian Twists', 'sets' => '3', 'reps' => '20-30', 'gif' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Russian-Twist.gif', 'tips' => 'Rotate torso, touch floor each side'],
        ['name' => 'Leg Raises', 'sets' => '3', 'reps' => '12-15', 'gif' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Leg-Raises.gif', 'tips' => 'Keep legs straight, lower slowly'],
        ['name' => 'Bicycle Crunches', 'sets' => '3', 'reps' => '20-30', 'gif' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Bicycle-Crunches.gif', 'tips' => 'Alternate sides, touch elbow to knee'],
        ['name' => 'Mountain Climbers', 'sets' => '3', 'reps' => '30-40', 'gif' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Mountain-Climbers.gif', 'tips' => 'Fast pace, keep core tight'],
        ['name' => 'Ab Wheel Rollouts', 'sets' => '3', 'reps' => '8-12', 'gif' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Ab-Rollout.gif', 'tips' => 'Roll out slowly, engage core'],
        ['name' => 'Side Plank', 'sets' => '3', 'reps' => '30-45 sec each', 'gif' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Elbow-Side-Plank.gif', 'tips' => 'Keep body straight, hold position'],
        ['name' => 'V-Ups', 'sets' => '3', 'reps' => '12-15', 'gif' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/V-up.gif', 'tips' => 'Touch hands to feet, control descent'],
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
                    showSuccessModal(data.xp_earned || 0, data.already_completed_today);
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
