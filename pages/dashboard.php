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
</head>
<body class="dark-theme">
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="dashboard-container">
        <div class="container">
            <!-- Welcome Section -->
            <div class="welcome-section">
                <h1>Welcome back, <?php echo htmlspecialchars($user['name']); ?>! 💪</h1>
                <p>Ready to crush your fitness goals today?</p>
            </div>

            <!-- Stats Overview -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">🎯</div>
                    <div class="stat-content">
                        <h3>Daily Calories</h3>
                        <p class="stat-value"><?php echo $profile['daily_calories']; ?> cal</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">💪</div>
                    <div class="stat-content">
                        <h3>Protein Goal</h3>
                        <p class="stat-value"><?php echo $profile['protein_grams']; ?>g</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">⚖️</div>
                    <div class="stat-content">
                        <h3>Current BMI</h3>
                        <p class="stat-value"><?php echo $profile['bmi']; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🔥</div>
                    <div class="stat-content">
                        <h3>Workout Days</h3>
                        <p class="stat-value"><?php echo $profile['workout_frequency']; ?>/week</p>
                    </div>
                </div>
            </div>

            <!-- XP and Level Widget -->
            <div class="xp-card">
                <div class="xp-header">
                    <div class="xp-level">
                        <h2>Level <span id="userLevel">1</span> 💪</h2>
                        <p>Keep crushing your workouts!</p>
                    </div>
                    <div class="xp-total">
                        <div class="xp-total-value" id="userXP">0</div>
                        <div class="xp-total-label">Total XP</div>
                    </div>
                </div>
                
                <div class="xp-progress-bar">
                    <div id="xpProgressBar" class="xp-progress-fill"></div>
                </div>
                
                <div class="xp-progress-text">
                    <span><span id="xpProgress">0</span> XP</span>
                    <span><span id="xpNeeded">100</span> XP to Level <span id="nextLevel">2</span></span>
                </div>
                
                <div class="xp-stats">
                    <div class="xp-stat">
                        <span class="xp-stat-value" id="totalWorkouts">0</span>
                        <span class="xp-stat-label">Workouts</span>
                    </div>
                    <div class="xp-stat">
                        <span class="xp-stat-value" id="totalExercises">0</span>
                        <span class="xp-stat-label">Exercises</span>
                    </div>
                    <div class="xp-stat">
                        <span class="xp-stat-value" id="totalAchievements">0</span>
                        <span class="xp-stat-label">Achievements</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <h2>Quick Actions</h2>
                <div class="action-grid">
                    <a href="workout-plan-improved.php" class="action-card">
                        <span class="action-icon">💪</span>
                        <h3>Workout Plan</h3>
                    </a>
                    <a href="meal-plan-new.php" class="action-card">
                        <span class="action-icon">🥗</span>
                        <h3>Meal Plan</h3>
                    </a>
                    <a href="chat.php" class="action-card">
                        <span class="action-icon">🤖</span>
                        <h3>AI Coach</h3>
                    </a>
                    <a href="profile.php" class="action-card">
                        <span class="action-icon">👤</span>
                        <h3>Profile</h3>
                    </a>
                </div>
            </div>

            <!-- Today's Activity -->
            <div class="activity-section">
                <h2>Today's Activity</h2>
                
                <!-- Today's Workout -->
                <div class="activity-card">
                    <div class="activity-header">
                        <h3>💪 Today's Workout</h3>
                        <span class="activity-badge">Ready</span>
                    </div>
                    <div class="activity-content" id="todayWorkout">
                        <p>Your personalized workout plan is ready. Start training to earn XP and level up!</p>
                        <a href="workout-plan-improved.php" class="activity-btn">Start Workout</a>
                    </div>
                </div>

                <!-- Today's Nutrition -->
                <div class="activity-card">
                    <div class="activity-header">
                        <h3>🥗 Today's Nutrition</h3>
                        <span class="activity-badge">Pending</span>
                    </div>
                    <div class="activity-content" id="todayMeals">
                        <p>Track your meals and stay on top of your nutrition goals.</p>
                        <a href="meal-plan-new.php" class="activity-btn">View Meal Plan</a>
                    </div>
                </div>

                <!-- AI Coach Chat Preview -->
                <div class="chat-preview">
                    <h3>🤖 Ask Your AI Coach</h3>
                    <div class="chat-messages" id="chatMessages">
                        <p style="color: var(--text-secondary); text-align: center; padding: 1rem;">
                            Start a conversation with your AI fitness coach
                        </p>
                    </div>
                    <a href="chat.php" class="activity-btn">Open Chat</a>
                </div>
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
                        document.getElementById('userLevel').textContent = data.level || 1;
                        document.getElementById('userXP').textContent = data.xp || 0;
                        document.getElementById('nextLevel').textContent = (data.level || 1) + 1;
                        
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
        });
    </script>
</body>
</html>
