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
        });
    </script>
</body>
</html>
