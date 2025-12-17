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
                    <div class="stat-label">STREAK</div>
                    <div class="stat-value-large">0 <span class="stat-unit">day/s</span></div>
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
                            <div class="weight-label">CURRENT WEIGHT</div>
                            <div class="weight-value" id="currentWeight">
                                <span class="weight-number">--</span>
                                <span class="weight-unit">kg</span>
                            </div>
                        </div>
                        <div class="weight-goal" onclick="showGoalWeightModal()" style="cursor: pointer;" title="Click to edit goal weight">
                            <div class="weight-label">GOAL WEIGHT</div>
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
                <a href="meal-tracker.php" class="activity-card-modern">
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

    <!-- Weight Modal -->
    <div id="weightModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>📊 Log Your Weight</h3>
                <button class="modal-close" onclick="closeWeightModal()">&times;</button>
            </div>
            <div class="modal-body">
                <label for="weightInput" class="modal-label">Current Weight (kg)</label>
                <input type="number" id="weightInput" class="modal-input" placeholder="e.g., 75.5" step="0.1" min="0" max="300">
            </div>
            <div class="modal-footer">
                <button class="modal-btn modal-btn-cancel" onclick="closeWeightModal()">Cancel</button>
                <button class="modal-btn modal-btn-primary" onclick="saveWeight()">Save Weight</button>
            </div>
        </div>
    </div>

    <!-- Goal Weight Modal -->
    <div id="goalWeightModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>🎯 Update Goal Weight</h3>
                <button class="modal-close" onclick="closeGoalWeightModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p style="color: var(--text-secondary); margin-bottom: 1rem; font-size: 0.9rem;">
                    💡 Tip: Ask the AI Coach what weight is ideal for your fitness goal!
                </p>
                <label for="goalWeightInput" class="modal-label">Goal Weight (kg)</label>
                <input type="number" id="goalWeightInput" class="modal-input" placeholder="e.g., 70.0" step="0.1" min="0" max="300">
            </div>
            <div class="modal-footer">
                <button class="modal-btn modal-btn-cancel" onclick="closeGoalWeightModal()">Cancel</button>
                <button class="modal-btn modal-btn-primary" onclick="saveGoalWeight()">Save Goal</button>
            </div>
        </div>
    </div>

    <!-- Measurements Modal -->
    <div id="measurementsModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>📏 Update Measurements</h3>
                <button class="modal-close" onclick="closeMeasurementsModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="modal-input-group">
                    <label for="armsInput" class="modal-label">💪 Arms (cm)</label>
                    <input type="number" id="armsInput" class="modal-input" placeholder="e.g., 38" step="0.1" min="0" max="100">
                </div>
                <div class="modal-input-group">
                    <label for="chestInput" class="modal-label">🫀 Chest (cm)</label>
                    <input type="number" id="chestInput" class="modal-input" placeholder="e.g., 95" step="0.1" min="0" max="200">
                </div>
                <div class="modal-input-group">
                    <label for="waistInput" class="modal-label">🤸 Waist (cm)</label>
                    <input type="number" id="waistInput" class="modal-input" placeholder="e.g., 82" step="0.1" min="0" max="200">
                </div>
                <div class="modal-input-group">
                    <label for="legsInput" class="modal-label">🦵 Legs (cm)</label>
                    <input type="number" id="legsInput" class="modal-input" placeholder="e.g., 58" step="0.1" min="0" max="100">
                </div>
            </div>
            <div class="modal-footer">
                <button class="modal-btn modal-btn-cancel" onclick="closeMeasurementsModal()">Cancel</button>
                <button class="modal-btn modal-btn-primary" onclick="saveMeasurements()">Save Measurements</button>
            </div>
        </div>
    </div>

    <!-- Success Toast -->
    <div id="successToast" class="success-toast" style="display: none;"></div>

    <style>
        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(4px);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.2s ease;
        }

        .modal-content {
            background: linear-gradient(135deg, #1a2332 0%, #0f1621 100%);
            border-radius: 20px;
            width: 90%;
            max-width: 450px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            animation: slideUp 0.3s ease;
            border: 1px solid rgba(74, 157, 181, 0.2);
        }

        .modal-header {
            padding: 24px 24px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            color: #fff;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .modal-close {
            background: none;
            border: none;
            color: #fff;
            font-size: 2rem;
            cursor: pointer;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all 0.2s;
            line-height: 1;
        }

        .modal-close:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .modal-body {
            padding: 24px;
        }

        .modal-input-group {
            margin-bottom: 20px;
        }

        .modal-input-group:last-child {
            margin-bottom: 0;
        }

        .modal-label {
            display: block;
            color: #a0aec0;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .modal-input {
            width: 100%;
            padding: 14px 16px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(74, 157, 181, 0.3);
            border-radius: 12px;
            color: #fff;
            font-size: 1.125rem;
            font-weight: 600;
            transition: all 0.3s;
            box-sizing: border-box;
        }

        .modal-input:focus {
            outline: none;
            border-color: #4A9DB5;
            background: rgba(74, 157, 181, 0.1);
            box-shadow: 0 0 0 4px rgba(74, 157, 181, 0.1);
        }

        .modal-input::placeholder {
            color: rgba(255, 255, 255, 0.3);
        }

        .modal-footer {
            padding: 16px 24px 24px;
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }

        .modal-btn {
            padding: 12px 24px;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
        }

        .modal-btn-cancel {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .modal-btn-cancel:hover {
            background: rgba(255, 255, 255, 0.15);
        }

        .modal-btn-primary {
            background: linear-gradient(135deg, #4A9DB5 0%, #3D8BA3 100%);
            color: #fff;
            box-shadow: 0 4px 12px rgba(74, 157, 181, 0.3);
        }

        .modal-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(74, 157, 181, 0.4);
        }

        .modal-btn-primary:active {
            transform: translateY(0);
        }

        /* Success Toast */
        .success-toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            padding: 16px 24px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(76, 175, 80, 0.4);
            font-weight: 600;
            z-index: 10000;
            animation: slideInRight 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
    </style>

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
                        const xpCard = document.querySelector('.xp-card-modern');
                        if (xpCard && isMilestone) {
                            xpCard.style.background = 'linear-gradient(135deg, #FFD700 0%, #FFA500 100%)';
                            xpCard.style.border = '2px solid #FFD700';
                            xpCard.style.boxShadow = '0 8px 32px rgba(255, 215, 0, 0.3)';
                            document.getElementById('userLevel').innerHTML = `${level} 🏆`;
                        } else if (xpCard) {
                            xpCard.style.background = '';
                            xpCard.style.border = '';
                            xpCard.style.boxShadow = '';
                            document.getElementById('userLevel').textContent = level;
                        } else {
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
        async function loadProgressData() {
            // Load actual weight data from user profile
            try {
                const response = await fetch('../api/user/get-profile.php');
                const data = await response.json();
                
                if (data.success && data.profile) {
                    const currentWeight = parseFloat(data.profile.weight) || 0;
                    const goalWeight = parseFloat(data.profile.target_weight) || currentWeight || 0;
                    
                    // Update Weight
                    document.querySelector('#currentWeight .weight-number').textContent = currentWeight ? currentWeight.toFixed(1) : '--';
                    document.querySelector('#goalWeight .weight-number').textContent = goalWeight ? goalWeight.toFixed(1) : '--';
                    
                    // Load history from localStorage (scoped per user, no demo data)
                    const profileUserId = data.profile.user_id;
                    let weightData = JSON.parse(localStorage.getItem('weightData') || 'null');
                    if (!weightData || !Array.isArray(weightData.history) || weightData.user_id !== profileUserId) {
                        weightData = { user_id: profileUserId, current: currentWeight, goal: goalWeight, history: [] };
                    }
                    
                    // Update localStorage with actual values
                    weightData.current = currentWeight;
                    weightData.goal = goalWeight;
                    localStorage.setItem('weightData', JSON.stringify(weightData));
                    
                    const changeEl = document.querySelector('#weightChange .change-value');
                    if (weightData.history.length > 1) {
                        const weekChange = currentWeight - weightData.history[weightData.history.length - 2];
                        changeEl.textContent = (weekChange >= 0 ? '+' : '') + weekChange.toFixed(1) + ' kg';
                        changeEl.className = 'change-value ' + (weekChange >= 0 ? '' : 'negative');
                    } else {
                        changeEl.textContent = '--';
                        changeEl.className = 'change-value';
                    }
                    
                    // Draw chart only if real history exists
                    if (weightData.history && weightData.history.length > 1) {
                        drawWeightChart(weightData.history);
                    } else {
                        clearWeightChart();
                    }
                } else {
                    clearWeightChart();
                }
            } catch (error) {
                console.error('Error loading weight data:', error);
                clearWeightChart();
            }
            
            const measurements = JSON.parse(localStorage.getItem('measurements') || '{"arms": 38, "chest": 95, "waist": 82, "legs": 58}');
            
            // Update Measurements
            document.getElementById('armsValue').textContent = measurements.arms + ' cm';
            document.getElementById('chestValue').textContent = measurements.chest + ' cm';
            document.getElementById('waistValue').textContent = measurements.waist + ' cm';
            document.getElementById('legsValue').textContent = measurements.legs + ' cm';
            
            // Load Streak Data from API
            loadStreakData();
            
            // Generate Calendar
            generateWorkoutCalendar();
        }

        async function loadStreakData() {
            try {
                const response = await fetch('../api/user/get-streak.php');
                const data = await response.json();
                
                if (data.success) {
                    // Update Streak
                    document.getElementById('streakNumber').textContent = data.streak;
                    
                    // Update Consistency
                    document.getElementById('consistencyPercent').textContent = data.consistency + '%';
                    document.getElementById('consistencyFill').style.width = data.consistency + '%';
                    
                    // Update calendar with actual workout dates
                    if (data.workoutDates) {
                        generateWorkoutCalendar(data.workoutDates);
                    }
                } else {
                    console.error('Failed to load streak:', data.message);
                }
            } catch (error) {
                console.error('Error loading streak:', error);
            }
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

        function generateWorkoutCalendar(workoutDates = []) {
            const calendar = document.getElementById('workoutCalendar');
            calendar.innerHTML = '';
            
            // Generate last 28 days (4 weeks)
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            for (let i = 27; i >= 0; i--) {
                const day = document.createElement('div');
                day.className = 'calendar-day';
                
                // Calculate date for this day
                const date = new Date(today);
                date.setDate(date.getDate() - i);
                const dateStr = date.toISOString().split('T')[0];
                
                if (i === 0) {
                    day.classList.add('today');
                }
                
                // Check if workout was completed on this date
                if (workoutDates.includes(dateStr)) {
                    day.classList.add('completed');
                    day.textContent = '✓';
                } else if (i > 0) {
                    day.classList.add('missed');
                }
                
                calendar.appendChild(day);
            }
        }

        // Modal Functions
        function showWeightModal() {
            document.getElementById('weightModal').style.display = 'flex';
            document.getElementById('weightInput').value = '';
            setTimeout(() => document.getElementById('weightInput').focus(), 100);
        }

        function closeWeightModal() {
            document.getElementById('weightModal').style.display = 'none';
        }

        function showGoalWeightModal() {
            // Get current goal weight
            const currentGoal = document.querySelector('#goalWeight .weight-number').textContent;
            document.getElementById('goalWeightModal').style.display = 'flex';
            document.getElementById('goalWeightInput').value = currentGoal !== '--' ? currentGoal : '';
            setTimeout(() => document.getElementById('goalWeightInput').focus(), 100);
        }

        function closeGoalWeightModal() {
            document.getElementById('goalWeightModal').style.display = 'none';
        }

        async function saveGoalWeight() {
            const targetWeight = parseFloat(document.getElementById('goalWeightInput').value);
            if (targetWeight && !isNaN(targetWeight) && targetWeight > 0 && targetWeight < 300) {
                try {
                    const response = await fetch('../api/user/update-goal-weight.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ target_weight: targetWeight })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Update display immediately
                        document.querySelector('#goalWeight .weight-number').textContent = targetWeight.toFixed(1);
                        closeGoalWeightModal();
                        showToast('Goal weight updated successfully! 🎯');
                        
                        // Reload profile data
                        loadProgressData();
                    } else {
                        showToast(data.message || 'Failed to save goal weight', 'error');
                    }
                } catch (error) {
                    console.error('Error saving goal weight:', error);
                    showToast('Error saving goal weight', 'error');
                }
            } else {
                showToast('Please enter a valid goal weight', 'error');
            }
        }

        async function saveWeight() {
            const weight = parseFloat(document.getElementById('weightInput').value);
            if (weight && !isNaN(weight) && weight > 0 && weight < 300) {
                try {
                    const response = await fetch('../api/user/update-weight.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ weight })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Update display immediately
                        document.querySelector('#currentWeight .weight-number').textContent = weight.toFixed(1);
                        
                        // Update weight history
                        let weightData = JSON.parse(localStorage.getItem('weightData') || 'null');
                        if (weightData && weightData.history) {
                            weightData.history.push(weight);
                            if (weightData.history.length > 10) {
                                weightData.history.shift(); // Keep last 10 entries
                            }
                            weightData.current = weight;
                            localStorage.setItem('weightData', JSON.stringify(weightData));
                            
                            // Redraw chart with new data
                            if (weightData.history.length > 1) {
                                drawWeightChart(weightData.history);
                            }
                        }
                        
                        closeWeightModal();
                        showToast('Weight logged successfully! 💪');
                        
                        // Reload profile data
                        loadProgressData();
                    } else {
                        showToast(data.message || 'Failed to save weight', 'error');
                    }
                } catch (error) {
                    console.error('Error saving weight:', error);
                    showToast('Error saving weight', 'error');
                }
            } else {
                showToast('Please enter a valid weight', 'error');
            }
        }

        function showMeasurementsModal() {
            const measurements = JSON.parse(localStorage.getItem('measurements') || '{"arms": 38, "chest": 95, "waist": 82, "legs": 58}');
            document.getElementById('armsInput').value = measurements.arms;
            document.getElementById('chestInput').value = measurements.chest;
            document.getElementById('waistInput').value = measurements.waist;
            document.getElementById('legsInput').value = measurements.legs;
            document.getElementById('measurementsModal').style.display = 'flex';
        }

        function closeMeasurementsModal() {
            document.getElementById('measurementsModal').style.display = 'none';
        }

        function saveMeasurements() {
            const arms = parseFloat(document.getElementById('armsInput').value);
            const chest = parseFloat(document.getElementById('chestInput').value);
            const waist = parseFloat(document.getElementById('waistInput').value);
            const legs = parseFloat(document.getElementById('legsInput').value);
            
            if (arms && chest && waist && legs) {
                const measurements = {
                    arms: arms,
                    chest: chest,
                    waist: waist,
                    legs: legs
                };
                localStorage.setItem('measurements', JSON.stringify(measurements));
                loadProgressData();
                closeMeasurementsModal();
                showToast('Measurements updated! 📏');
            } else {
                showToast('Please fill in all measurements', 'error');
            }
        }

        function showToast(message, type = 'success') {
            const toast = document.getElementById('successToast');
            toast.textContent = message;
            toast.style.display = 'block';
            
            if (type === 'error') {
                toast.style.background = 'linear-gradient(135deg, #f44336 0%, #d32f2f 100%)';
            } else {
                toast.style.background = 'linear-gradient(135deg, #4CAF50 0%, #45a049 100%)';
            }
            
            setTimeout(() => {
                toast.style.display = 'none';
            }, 3000);
        }

        // Close modals on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeWeightModal();
                closeMeasurementsModal();
            }
        });

        // Close modals on overlay click
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal-overlay')) {
                closeWeightModal();
                closeMeasurementsModal();
            }
        });

        // Submit on Enter key in weight input
        document.addEventListener('DOMContentLoaded', function() {
            const weightInput = document.getElementById('weightInput');
            if (weightInput) {
                weightInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        saveWeight();
                    }
                });
            }
        });
    </script>
</body>
</html>
