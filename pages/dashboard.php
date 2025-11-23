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
    <title>Dashboard - NutriCoach AI</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
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
            <div class="card" style="margin-bottom: 1.5rem; background: linear-gradient(135deg, #4A9DB5 0%, #3D8BA3 100%); color: white;">
                <div class="card-body" style="padding: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <div>
                            <h2 style="margin: 0; font-size: 1.5rem; color: white;">Level <span id="userLevel">1</span> 💪</h2>
                            <p style="margin: 0.25rem 0 0 0; opacity: 0.9; font-size: 0.875rem;">Keep crushing your workouts!</p>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 2rem; font-weight: 700;" id="userXP">0</div>
                            <div style="font-size: 0.75rem; opacity: 0.9;">Total XP</div>
                        </div>
                    </div>
                    
                    <div style="background: rgba(255,255,255,0.2); border-radius: 10px; height: 12px; overflow: hidden; margin-bottom: 0.5rem;">
                        <div id="xpProgressBar" style="background: white; height: 100%; width: 0%; transition: width 0.5s ease; border-radius: 10px;"></div>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; font-size: 0.75rem; opacity: 0.9;">
                        <span><span id="xpProgress">0</span> XP</span>
                        <span><span id="xpNeeded">100</span> XP to Level <span id="nextLevel">2</span></span>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.2);">
                        <div style="text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: 700;" id="totalWorkouts">0</div>
                            <div style="font-size: 0.75rem; opacity: 0.9;">Workouts</div>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: 700;" id="totalExercises">0</div>
                            <div style="font-size: 0.75rem; opacity: 0.9;">Exercises</div>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: 700;" id="totalAchievements">0</div>
                            <div style="font-size: 0.75rem; opacity: 0.9;">Achievements</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Progress Tracking Widget -->
            <?php include __DIR__ . '/../includes/progress-widget.php'; ?>

            <!-- Main Content Grid -->
            <div class="dashboard-grid">
                <!-- AI Chatbot Section -->
                <div class="dashboard-section chatbot-section">
                    <div class="card">
                        <div class="card-header">
                            <h2>🤖 AI Fitness Coach</h2>
                        </div>
                        <div class="card-body">
                            <div id="chatMessages" class="chat-messages"></div>
                            <form id="chatForm" class="chat-form">
                                <input type="text" name="message" class="form-control" placeholder="Ask your AI coach anything..." required>
                                <button type="submit" class="btn btn-primary">Send</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="dashboard-section">
                    <div class="card">
                        <div class="card-header">
                            <h2>Quick Actions</h2>
                        </div>
                        <div class="card-body">
                            <div class="action-buttons">
                                <a href="workout-plan-improved.php" class="action-btn">
                                    <span class="action-icon">💪</span>
                                    <span>View Workout Plan</span>
                                </a>
                                <a href="meal-plan-new.php" class="action-btn">
                                    <span class="action-icon">🥗</span>
                                    <span>Today's Meals</span>
                                </a>
                                <a href="progress.php" class="action-btn">
                                    <span class="action-icon">📊</span>
                                    <span>Track Progress</span>
                                </a>
                                <a href="profile.php" class="action-btn">
                                    <span class="action-icon">👤</span>
                                    <span>Edit Profile</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Today's Workout Preview -->
                <div class="dashboard-section">
                    <div class="card">
                        <div class="card-header">
                            <h2>Today's Workout</h2>
                            <a href="workout-plan-improved.php" class="btn btn-outline">View Full Plan</a>
                        </div>
                        <div class="card-body" id="todayWorkout">
                            <div class="spinner"></div>
                        </div>
                    </div>
                </div>

                <!-- Meal Plan Preview -->
                <div class="dashboard-section">
                    <div class="card">
                        <div class="card-header">
                            <h2>Today's Nutrition</h2>
                            <a href="meal-plan-new.php" class="btn btn-outline">View Full Plan</a>
                        </div>
                        <div class="card-body" id="todayMeals">
                            <div class="spinner"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/dashboard.js"></script>
    <script>
        // Load XP and Level stats
        async function loadXPStats() {
            try {
                const response = await fetch('../api/workout/get-stats.php');
                const data = await response.json();
                
                if (data.success) {
                    const stats = data.data;
                    
                    // Update level and XP
                    document.getElementById('userLevel').textContent = stats.level;
                    document.getElementById('userXP').textContent = stats.xp;
                    document.getElementById('nextLevel').textContent = stats.level + 1;
                    
                    // Update progress bar
                    document.getElementById('xpProgress').textContent = stats.xp_progress;
                    document.getElementById('xpNeeded').textContent = stats.xp_needed;
                    document.getElementById('xpProgressBar').style.width = stats.progress_percent + '%';
                    
                    // Update stats
                    document.getElementById('totalWorkouts').textContent = stats.total_workouts;
                    document.getElementById('totalExercises').textContent = stats.total_exercises;
                    document.getElementById('totalAchievements').textContent = stats.total_achievements;
                }
            } catch (error) {
                console.error('Error loading XP stats:', error);
            }
        }
        
        // Load stats on page load
        loadXPStats();
        
        // Refresh stats every 30 seconds
        setInterval(loadXPStats, 30000);
    </script>
</body>
</html>
