<?php
/**
 * Check XP System Setup
 * Run this to verify XP system is properly configured
 */

require_once 'config/database.php';

$db = getDB();

echo "<h1>🔍 XP System Diagnostic</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
    .success { color: green; }
    .error { color: red; }
    .warning { color: orange; }
    table { border-collapse: collapse; width: 100%; margin: 20px 0; background: white; }
    th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
    th { background: #4A9DB5; color: white; }
</style>";

try {
    // 1. Check users table for xp and level columns
    echo "<h2>1. Checking users table...</h2>";
    $stmt = $db->query("SHOW COLUMNS FROM users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $hasXp = false;
    $hasLevel = false;
    
    echo "<table><tr><th>Column</th><th>Type</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Default']}</td></tr>";
        if ($col['Field'] === 'xp') $hasXp = true;
        if ($col['Field'] === 'level') $hasLevel = true;
    }
    echo "</table>";
    
    if ($hasXp && $hasLevel) {
        echo "<p class='success'>✅ Users table has XP and Level columns</p>";
    } else {
        echo "<p class='error'>❌ Missing columns! Need to run setup-gamification.php</p>";
        echo "<a href='setup-gamification.php' style='padding: 10px 20px; background: #4A9DB5; color: white; text-decoration: none; border-radius: 8px;'>Run Setup Now</a>";
    }
    
    // 2. Check workout_sessions table
    echo "<h2>2. Checking workout_sessions table...</h2>";
    $stmt = $db->query("SHOW TABLES LIKE 'workout_sessions'");
    if ($stmt->rowCount() > 0) {
        echo "<p class='success'>✅ workout_sessions table exists</p>";
        
        $stmt = $db->query("SHOW COLUMNS FROM workout_sessions");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<table><tr><th>Column</th><th>Type</th></tr>";
        foreach ($columns as $col) {
            echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='error'>❌ workout_sessions table missing!</p>";
    }
    
    // 3. Check exercise_completions table
    echo "<h2>3. Checking exercise_completions table...</h2>";
    $stmt = $db->query("SHOW TABLES LIKE 'exercise_completions'");
    if ($stmt->rowCount() > 0) {
        echo "<p class='success'>✅ exercise_completions table exists</p>";
    } else {
        echo "<p class='error'>❌ exercise_completions table missing!</p>";
    }
    
    // 4. Check achievements table
    echo "<h2>4. Checking achievements table...</h2>";
    $stmt = $db->query("SHOW TABLES LIKE 'achievements'");
    if ($stmt->rowCount() > 0) {
        echo "<p class='success'>✅ achievements table exists</p>";
    } else {
        echo "<p class='error'>❌ achievements table missing!</p>";
    }
    
    // 5. Check sample user data
    echo "<h2>5. Sample User XP Data</h2>";
    $stmt = $db->query("SELECT id, name, email, xp, level FROM users LIMIT 5");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($users) > 0) {
        echo "<table><tr><th>ID</th><th>Name</th><th>Email</th><th>XP</th><th>Level</th></tr>";
        foreach ($users as $user) {
            $xp = $user['xp'] ?? 'NULL';
            $level = $user['level'] ?? 'NULL';
            echo "<tr><td>{$user['id']}</td><td>{$user['name']}</td><td>{$user['email']}</td><td>$xp</td><td>$level</td></tr>";
        }
        echo "</table>";
    }
    
    // 6. Check recent workout sessions
    echo "<h2>6. Recent Workout Sessions</h2>";
    $stmt = $db->query("SELECT * FROM workout_sessions ORDER BY started_at DESC LIMIT 5");
    $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($sessions) > 0) {
        echo "<table><tr><th>ID</th><th>User ID</th><th>Type</th><th>Status</th><th>Exercises</th><th>XP Earned</th><th>Started</th></tr>";
        foreach ($sessions as $session) {
            echo "<tr>";
            echo "<td>{$session['id']}</td>";
            echo "<td>{$session['user_id']}</td>";
            echo "<td>{$session['workout_type']}</td>";
            echo "<td>{$session['status']}</td>";
            echo "<td>{$session['completed_exercises']}/{$session['total_exercises']}</td>";
            echo "<td>{$session['xp_earned']}</td>";
            echo "<td>{$session['started_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='warning'>⚠️ No workout sessions found</p>";
    }
    
    // 7. Check recent exercise completions
    echo "<h2>7. Recent Exercise Completions</h2>";
    $stmt = $db->query("SELECT * FROM exercise_completions ORDER BY completed_at DESC LIMIT 10");
    $exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($exercises) > 0) {
        echo "<table><tr><th>ID</th><th>User ID</th><th>Session ID</th><th>Exercise</th><th>XP</th><th>Completed</th></tr>";
        foreach ($exercises as $ex) {
            echo "<tr>";
            echo "<td>{$ex['id']}</td>";
            echo "<td>{$ex['user_id']}</td>";
            echo "<td>{$ex['session_id']}</td>";
            echo "<td>{$ex['exercise_name']}</td>";
            echo "<td>{$ex['xp_earned']}</td>";
            echo "<td>{$ex['completed_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='warning'>⚠️ No exercise completions found</p>";
    }
    
    echo "<h2>✅ Diagnostic Complete</h2>";
    echo "<p><a href='pages/dashboard.php'>Go to Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
