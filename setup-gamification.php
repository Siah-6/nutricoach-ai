<?php
/**
 * Setup Gamification System - XP, Levels, Workout Tracking
 * Run this once: http://localhost/xampp/NutriCoachAI/setup-gamification.php
 */

require_once 'config/database.php';

$db = getDB();

echo "<h1>🎮 Setting up Gamification System...</h1>";

try {
    // 1. Add XP and Level columns to users table
    echo "<p>Adding XP and Level columns to users...</p>";
    $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS xp INT DEFAULT 0");
    $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS level INT DEFAULT 1");
    echo "<p style='color: green;'>✅ XP and Level columns added!</p>";
    
    // 2. Create workout_sessions table
    echo "<p>Creating workout_sessions table...</p>";
    $db->exec("CREATE TABLE IF NOT EXISTS workout_sessions (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        workout_type VARCHAR(100),
        started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        completed_at TIMESTAMP NULL,
        total_exercises INT DEFAULT 0,
        completed_exercises INT DEFAULT 0,
        xp_earned INT DEFAULT 0,
        status ENUM('in_progress', 'completed', 'abandoned') DEFAULT 'in_progress',
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    echo "<p style='color: green;'>✅ workout_sessions table created!</p>";
    
    // 3. Create exercise_completions table
    echo "<p>Creating exercise_completions table...</p>";
    $db->exec("CREATE TABLE IF NOT EXISTS exercise_completions (
        id INT PRIMARY KEY AUTO_INCREMENT,
        session_id INT NOT NULL,
        user_id INT NOT NULL,
        exercise_name VARCHAR(255),
        sets_completed INT DEFAULT 0,
        reps_completed INT DEFAULT 0,
        xp_earned INT DEFAULT 10,
        completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (session_id) REFERENCES workout_sessions(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    echo "<p style='color: green;'>✅ exercise_completions table created!</p>";
    
    // 4. Create achievements table
    echo "<p>Creating achievements table...</p>";
    $db->exec("CREATE TABLE IF NOT EXISTS achievements (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        achievement_type VARCHAR(100),
        achievement_name VARCHAR(255),
        description TEXT,
        xp_reward INT DEFAULT 50,
        unlocked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    echo "<p style='color: green;'>✅ achievements table created!</p>";
    
    echo "<h2 style='color: green;'>🎉 Gamification System Setup Complete!</h2>";
    echo "<h3>Features Added:</h3>";
    echo "<ul>";
    echo "<li>✅ XP and Level system</li>";
    echo "<li>✅ Workout session tracking</li>";
    echo "<li>✅ Exercise completion tracking</li>";
    echo "<li>✅ Achievement system</li>";
    echo "</ul>";
    
    echo "<h3>XP System:</h3>";
    echo "<ul>";
    echo "<li>Complete exercise: +10 XP</li>";
    echo "<li>Complete workout: +50 XP bonus</li>";
    echo "<li>Level up: Every 100 XP</li>";
    echo "<li>Achievements: +50-200 XP</li>";
    echo "</ul>";
    
    echo "<br><a href='pages/dashboard.php' style='padding: 10px 20px; background: #4A9DB5; color: white; text-decoration: none; border-radius: 8px;'>Go to Dashboard</a>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
