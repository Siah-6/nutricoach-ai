<?php
/**
 * Add workout_plan column to workout_sessions table
 * Run this once: http://localhost/xampp/NutriCoachAI/update-workout-sessions.php
 */

require_once 'config/database.php';

$db = getDB();

echo "<h1>🔧 Updating workout_sessions Table...</h1>";

try {
    echo "<p>Adding workout_plan column...</p>";
    $db->exec("ALTER TABLE workout_sessions ADD COLUMN IF NOT EXISTS workout_plan TEXT");
    echo "<p style='color: green;'>✅ workout_plan column added!</p>";
    
    echo "<h2 style='color: green;'>✅ Update Complete!</h2>";
    echo "<p>The workout_sessions table now has a workout_plan column to store exercises.</p>";
    echo "<br><a href='pages/workout-plan-improved.php' style='padding: 10px 20px; background: #4A9DB5; color: white; text-decoration: none; border-radius: 8px;'>Go to Workout Page</a>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
