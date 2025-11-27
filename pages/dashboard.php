<?php
/**
 * User Dashboard
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

initSession();

// Redirect if not logged in
if (!isLoggedIn()) {
    redirect('/');
}

// Redirect if onboarding not completed
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
    <title>Dashboard - NutriCoach AI</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dark-theme.css">
    <link rel="stylesheet" href="../assets/css/dashboard-dark.css">
    <link rel="stylesheet" href="../assets/css/dashboard-modern.css">
</head>
<body class="dark-theme">
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="dashboard-container">
        <div class="container">
            <!-- Welcome Section -->
            <div class="welcome-section">
                <h1><?php echo htmlspecialchars($user['name']); ?></h1>
                <p class="welcome-subtitle">Ready to crush your fitness goals today?</p>
            </div>

            <!-- Stats Overview -->
            <div class="stats-grid">
                <div class="stat-card modern">
                    <div class="stat-icon-modern">🎯</div>
                    <div class="stat-label">DAILY CALORIES</div>
                    <div class="stat-value-large"><?php echo $profile['daily_calories']; ?> <span class="stat-unit">cal</span></div>
                </div>
                <div class="stat-card modern">
                    <div class="stat-icon-modern">💪</div>
                    <div class="stat-label">PROTEIN GOAL</div>
                    <div class="stat-value-large"><?php echo $profile['protein_grams']; ?><span class="stat-unit">g</span></div>
                </div>
                <div class="stat-card modern">
                    <div class="stat-icon-modern">⚖️</div>
                    <div class="stat-label">CURRENT BMI</div>
                    <div class="stat-value-large"><?php echo $profile['bmi']; ?></div>
                </div>
                <div class="stat-card modern">
                    <div class="stat-icon-modern">🔥</div>
                    <div class="stat-label">WORKOUT DAYS</div>
                    <div class="stat-value-large"><?php echo $profile['workout_frequency']; ?><span class="stat-unit">/week</span></div>
                </div>
            </div>

            <!-- XP and Level Widget - Compact -->
            <div class="xp-card-modern">
                <div class="xp-left">
                    <div class="xp-level-badge">Level <span id="userLevel">1</span> 💪</div>
                    <div class="xp-subtitle">Keep crushing your workouts!</div>
                </div>
                <div class="xp-right">
                    <div class="xp-total-modern" id="userXP">0</div>
                    <div class="xp-label-modern">Total XP</div>
                </div>
            </div>
            
            <div class="xp-progress-card">
                <div class="xp-progress-bar-modern">
                    <div id="xpProgressBar" class="xp-progress-fill-modern"></div>
                </div>
                <div class="xp-progress-info">
                    <span><span id="xpProgress">0</span> XP</span>
                    <span><span id="xpNeeded">100</span> XP to Level <span id="nextLevel">2</span></span>
                </div>
            </div>
            
            <div class="mini-stats">
                <div class="mini-stat">
                    <div class="mini-stat-value" id="totalWorkouts">0</div>
                    <div class="mini-stat-label">Workouts</div>
                </div>
                <div class="mini-stat">
                    <div class="mini-stat-value" id="totalExercises">0</div>
                    <div class="mini-stat-label">Exercises</div>
                </div>
                <div class="mini-stat">
                    <div class="mini-stat-value" id="totalAchievements">0</div>
                    <div class="mini-stat-label">Achievements</div>
                </div>
            </div>

            <!-- Progress Tracker -->
            <div class="progress-tracker-card">
                <h2 class="section-title">📊 Your Progress</h2>
                
                <!-- Progress Tabs -->
                <div class="progress-tabs">
                    <button class="progress-tab active" data-tab="weight">Weight</button>
                    <button class="progress-tab" data-tab="measurements">Measurements</button>
                    <button class="progress-tab" data-tab="streak">Streak</button>
                </div>

                <!-- Weight Tab -->
                <div class="progress-tab-content active" id="weight-tab">
                    <div class="weight-tracker">
                        <div class="weight-current">
                            <div class="weight-label">Current Weight</div>
                            <div class="weight-value" id="currentWeight">
                                <span class="weight-number">--</span>
                                <span class="weight-unit">kg</span>
                            </div>
                        </div>
                        <div class="weight-goal">
                            <div class="weight-label">Goal Weight</div>
                            <div class="weight-value" id="goalWeight">
                                <span class="weight-number">--</span>
                                <span class="weight-unit">kg</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="weight-change" id="weightChange">
                        <span class="change-label">This Week:</span>
                        <span class="change-value">--</span>
                    </div>

                    <div class="mini-chart" id="weightChart">
                        <canvas id="weightCanvas" width="300" height="100"></canvas>
                    </div>

                    <button class="btn-log-progress" onclick="showWeightModal()">
                        📝 Log Weight
                    </button>
                </div>

                <!-- Measurements Tab -->
                <div class="progress-tab-content" id="measurements-tab">
                    <div class="measurements-list">
                        <div class="measurement-item">
                            <div class="measurement-icon">💪</div>
                            <div class="measurement-info">
                                <div class="measurement-name">Arms</div>
                                <div class="measurement-value" id="armsValue">-- cm</div>
                            </div>
                            <div class="measurement-change" id="armsChange">--</div>
                        </div>
                        <div class="measurement-item">
                            <div class="measurement-icon">🫀</div>
                            <div class="measurement-info">
                                <div class="measurement-name">Chest</div>
                                <div class="measurement-value" id="chestValue">-- cm</div>
                            </div>
                            <div class="measurement-change" id="chestChange">--</div>
                        </div>
                        <div class="measurement-item">
                            <div class="measurement-icon">🤸</div>
                            <div class="measurement-info">
                                <div class="measurement-name">Waist</div>
                                <div class="measurement-value" id="waistValue">-- cm</div>
                            </div>
                            <div class="measurement-change" id="waistChange">--</div>
                        </div>
                        <div class="measurement-item">
                            <div class="measurement-icon">🦵</div>
                            <div class="measurement-info">
                                <div class="measurement-name">Legs</div>
                                <div class="measurement-value" id="legsValue">-- cm</div>
                            </div>
                            <div class="measurement-change" id="legsChange">--</div>
                        </div>
                    </div>

                    <button class="btn-log-progress" onclick="showMeasurementsModal()">
                        📏 Update Measurements
                    </button>
                </div>

                <!-- Streak Tab -->
                <div class="progress-tab-content" id="streak-tab">
                    <div class="streak-display">
                        <div class="streak-number" id="streakNumber">0</div>
                        <div class="streak-label">Day Streak 🔥</div>
                        <div class="streak-subtitle">Keep it going!</div>
                    </div>

                    <div class="consistency-bar">
                        <div class="consistency-label">
                            <span>This Month</span>
                            <span id="consistencyPercent">0%</span>
                        </div>
                        <div class="consistency-progress">
                            <div class="consistency-fill" id="consistencyFill"></div>
                        </div>
                    </div>

                    <div class="workout-calendar" id="workoutCalendar">
                        <!-- Will be populated by JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Quick Actions - Removed, moved to Today's Activity -->

            <!-- Today's Activity -->
            <div class="activity-section">
                <h2 class="section-title">Today's Activity</h2>
                
                <!-- Today's Workout -->
                <a href="workout-plan-improved.php" class="activity-card-modern">
                    <div class="activity-icon">�</div>
                    <div class="activity-info">
                        <div class="activity-title">Today's Workout</div>
                        <div class="activity-desc">Your personalized workout plan is ready. Start training to earn XP and level up!</div>
                    </div>
                    <div class="activity-badge-modern ready">Ready</div>
                </a>

                <!-- Today's Nutrition -->
                <a href="meal-plan-new.php" class="activity-card-modern">
                    <div class="activity-icon">🥗</div>
                    <div class="activity-info">
                        <div class="activity-title">Today's Nutrition</div>
                        <div class="activity-desc">Track your meals and stay on top of your nutrition goals.</div>
                    </div>
                    <div class="activity-badge-modern pending">Pending</div>
                </a>

                <!-- AI Coach -->
                <a href="chat.php" class="activity-card-modern">
                    <div class="activity-icon">🤖</div>
                    <div class="activity-info">
                        <div class="activity-title">Ask Your AI Coach</div>
                        <div class="activity-desc">Start a conversation with your AI fitness coach</div>
                    </div>
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="activity-arrow">
                        <path d="M9 18l6-6-6-6"/>
                    </svg>
                </a>

                <!-- Supplements -->
                <a href="supplements.php" class="activity-card-modern">
                    <div class="activity-icon">💊</div>
                    <div class="activity-info">
                        <div class="activity-title">Supplement Guide</div>
                        <div class="activity-desc">AI recommendations & complete supplement library</div>
                    </div>
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="activity-arrow">
                        <path d="M9 18l6-6-6-6"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/dashboard.js"></script>
    <script>
        // Wait for DOM to be ready
        document.addEventListener('DOMContentLoaded', function() {
            // Load XP and Level stats
            async function loadXPStats() {
                try {
                    const response = await fetch('../api/workout/get-stats.php');
                    const data = await response.json();
                    
                    console.log('📊 XP Stats loaded:', data);
                    
                    if (data.success) {
                        // Data is merged directly into response, not nested under 'data'
                        
                        // Update level and XP with fallback to defaults
                        const level = data.level || 1;
                        const isMilestone = data.is_milestone || false;
                        
                        // Add special styling for milestone levels (every 5 levels)
                        const xpCard = document.querySelector('.xp-card');
                        if (isMilestone) {
                            xpCard.style.background = 'linear-gradient(135deg, #FFD700 0%, #FFA500 100%)';
                            xpCard.style.border = '2px solid #FFD700';
                            xpCard.style.boxShadow = '0 8px 32px rgba(255, 215, 0, 0.3)';
                            document.getElementById('userLevel').innerHTML = `${level} 🏆`;
                        } else {
                            xpCard.style.background = '';
                            xpCard.style.border = '';
                            xpCard.style.boxShadow = '';
                            document.getElementById('userLevel').textContent = level;
                        }
                        
                        document.getElementById('userXP').textContent = data.xp || 0;
                        document.getElementById('nextLevel').textContent = level + 1;
                        
                        // Update progress bar
                        document.getElementById('xpProgress').textContent = data.xp_progress || 0;
                        document.getElementById('xpNeeded').textContent = data.xp_needed || 100;
                        document.getElementById('xpProgressBar').style.width = (data.progress_percent || 0) + '%';
                        
                        // Update stats
                        document.getElementById('totalWorkouts').textContent = data.total_workouts || 0;
                        document.getElementById('totalExercises').textContent = data.total_exercises || 0;
                        document.getElementById('totalAchievements').textContent = data.total_achievements || 0;
                    } else {
                        console.error('❌ Failed to load XP stats:', data.error || data.message);
                    }
                } catch (error) {
                    console.error('❌ Error loading XP stats:', error);
                    // Set defaults on error
                    document.getElementById('userLevel').textContent = 1;
                    document.getElementById('userXP').textContent = 0;
                    document.getElementById('nextLevel').textContent = 2;
                    document.getElementById('xpProgress').textContent = 0;
                    document.getElementById('xpNeeded').textContent = 100;
                    document.getElementById('xpProgressBar').style.width = '0%';
                }
            }
            
            // Load stats on page load
            loadXPStats();
            
            // Refresh stats every 30 seconds
            setInterval(loadXPStats, 30000);
            
            // Refresh stats when user returns to the page (e.g., from workout)
            document.addEventListener('visibilitychange', function() {
                if (!document.hidden) {
                    console.log('🔄 Page visible again, refreshing XP stats...');
                    loadXPStats();
                }
            });
            
            // Also refresh when window gains focus (navigation from other pages)
            window.addEventListener('focus', function() {
                console.log('🔄 Window focused, refreshing XP stats...');
                loadXPStats();
            });

            // Progress Tracker Tabs
            document.querySelectorAll('.progress-tab').forEach(tab => {
                tab.addEventListener('click', () => {
                    const tabName = tab.dataset.tab;
                    
                    // Update tabs
                    document.querySelectorAll('.progress-tab').forEach(t => t.classList.remove('active'));
                    tab.classList.add('active');
                    
                    // Update content
                    document.querySelectorAll('.progress-tab-content').forEach(c => c.classList.remove('active'));
                    document.getElementById(tabName + '-tab').classList.add('active');
                });
            });

            // Load Progress Data
            loadProgressData();
        });

        // Load Progress Data
        function loadProgressData() {
            // Load from localStorage (demo data)
            const weightData = JSON.parse(localStorage.getItem('weightData') || '{"current": 75, "goal": 80, "history": [73, 73.5, 74, 74.5, 75]}');
            const measurements = JSON.parse(localStorage.getItem('measurements') || '{"arms": 38, "chest": 95, "waist": 82, "legs": 58}');
            
            // Update Weight
            document.querySelector('#currentWeight .weight-number').textContent = weightData.current;
            document.querySelector('#goalWeight .weight-number').textContent = weightData.goal;
            
            const weekChange = weightData.current - weightData.history[weightData.history.length - 2];
            const changeEl = document.querySelector('#weightChange .change-value');
            changeEl.textContent = (weekChange >= 0 ? '+' : '') + weekChange.toFixed(1) + ' kg';
            changeEl.className = 'change-value ' + (weekChange >= 0 ? '' : 'negative');
            
            // Draw mini chart
            drawWeightChart(weightData.history);
            
            // Update Measurements
            document.getElementById('armsValue').textContent = measurements.arms + ' cm';
            document.getElementById('chestValue').textContent = measurements.chest + ' cm';
            document.getElementById('waistValue').textContent = measurements.waist + ' cm';
            document.getElementById('legsValue').textContent = measurements.legs + ' cm';
            
            // Update Streak
            const streak = parseInt(localStorage.getItem('workoutStreak') || '0');
            document.getElementById('streakNumber').textContent = streak;
            
            // Update Consistency
            const consistency = 75; // Demo: 75%
            document.getElementById('consistencyPercent').textContent = consistency + '%';
            document.getElementById('consistencyFill').style.width = consistency + '%';
            
            // Generate Calendar
            generateWorkoutCalendar();
        }

        function drawWeightChart(data) {
            const canvas = document.getElementById('weightCanvas');
            const ctx = canvas.getContext('2d');
            const width = canvas.width;
            const height = canvas.height;
            
            ctx.clearRect(0, 0, width, height);
            
            const max = Math.max(...data);
            const min = Math.min(...data);
            const range = max - min || 1;
            const paddingTop = 15;
            const paddingBottom = 25;
            const paddingSide = 20;
            
            ctx.strokeStyle = '#4a9eff';
            ctx.lineWidth = 2;
            ctx.beginPath();
            
            data.forEach((value, index) => {
                const x = paddingSide + (index / (data.length - 1)) * (width - paddingSide * 2);
                const y = height - paddingBottom - ((value - min) / range) * (height - paddingTop - paddingBottom);
                
                if (index === 0) {
                    ctx.moveTo(x, y);
                } else {
                    ctx.lineTo(x, y);
                }
            });
            
            ctx.stroke();
            
            // Draw points
            ctx.fillStyle = '#4a9eff';
            data.forEach((value, index) => {
                const x = paddingSide + (index / (data.length - 1)) * (width - paddingSide * 2);
                const y = height - paddingBottom - ((value - min) / range) * (height - paddingTop - paddingBottom);
                ctx.beginPath();
                ctx.arc(x, y, 4, 0, Math.PI * 2);
                ctx.fill();
            });
        }

        function generateWorkoutCalendar() {
            const calendar = document.getElementById('workoutCalendar');
            calendar.innerHTML = '';
            
            // Generate last 28 days (4 weeks)
            const today = new Date();
            const completedDays = [1, 2, 4, 6, 8, 9, 11, 13, 15, 16, 18, 20, 22, 23, 25, 27]; // Demo data
            
            for (let i = 27; i >= 0; i--) {
                const day = document.createElement('div');
                day.className = 'calendar-day';
                
                if (i === 0) {
                    day.classList.add('today');
                }
                
                if (completedDays.includes(i)) {
                    day.classList.add('completed');
                    day.textContent = '✓';
                } else if (i < 27) {
                    day.classList.add('missed');
                }
                
                calendar.appendChild(day);
            }
        }

        // Modal Functions
        function showWeightModal() {
            const weight = prompt('Enter your current weight (kg):');
            if (weight && !isNaN(weight)) {
                const weightData = JSON.parse(localStorage.getItem('weightData') || '{"current": 75, "goal": 80, "history": [73, 73.5, 74, 74.5, 75]}');
                weightData.current = parseFloat(weight);
                weightData.history.push(parseFloat(weight));
                if (weightData.history.length > 10) weightData.history.shift();
                localStorage.setItem('weightData', JSON.stringify(weightData));
                loadProgressData();
                alert('Weight logged successfully! 💪');
            }
        }

        function showMeasurementsModal() {
            const arms = prompt('Arms (cm):');
            const chest = prompt('Chest (cm):');
            const waist = prompt('Waist (cm):');
            const legs = prompt('Legs (cm):');
            
            if (arms && chest && waist && legs) {
                const measurements = {
                    arms: parseFloat(arms),
                    chest: parseFloat(chest),
                    waist: parseFloat(waist),
                    legs: parseFloat(legs)
                };
                localStorage.setItem('measurements', JSON.stringify(measurements));
                loadProgressData();
                alert('Measurements updated! 📏');
            }
        }
    </script>
</body>
</html>
