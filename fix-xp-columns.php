<?php
/**
 * Fix XP Columns - Ensure all users have XP and Level initialized
 * Run this if XP is not working: http://localhost/NutriCoachAI/fix-xp-columns.php
 */

require_once 'config/database.php';

$db = getDB();

echo "<h1>🔧 Fixing XP System...</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
    .success { color: green; }
    .error { color: red; }
    .info { color: blue; }
</style>";

try {
    // 1. Ensure xp and level columns exist
    echo "<h2>Step 1: Checking/Adding XP columns...</h2>";
    
    try {
        $db->exec("ALTER TABLE users ADD COLUMN xp INT DEFAULT 0");
        echo "<p class='success'>✅ Added 'xp' column to users table</p>";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "<p class='info'>ℹ️ 'xp' column already exists</p>";
        } else {
            throw $e;
        }
    }
    
    try {
        $db->exec("ALTER TABLE users ADD COLUMN level INT DEFAULT 1");
        echo "<p class='success'>✅ Added 'level' column to users table</p>";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "<p class='info'>ℹ️ 'level' column already exists</p>";
        } else {
            throw $e;
        }
    }
    
    // 2. Initialize XP and Level for all users who have NULL values
    echo "<h2>Step 2: Initializing XP for all users...</h2>";
    
    $stmt = $db->exec("UPDATE users SET xp = 0 WHERE xp IS NULL");
    echo "<p class='success'>✅ Initialized XP for users with NULL values</p>";
    
    $stmt = $db->exec("UPDATE users SET level = 1 WHERE level IS NULL");
    echo "<p class='success'>✅ Initialized Level for users with NULL values</p>";
    
    // 3. Show current user stats
    echo "<h2>Step 3: Current User Stats</h2>";
    $stmt = $db->query("SELECT id, name, email, xp, level FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table style='border-collapse: collapse; width: 100%; background: white;'>";
    echo "<tr style='background: #4A9DB5; color: white;'>";
    echo "<th style='border: 1px solid #ddd; padding: 12px;'>ID</th>";
    echo "<th style='border: 1px solid #ddd; padding: 12px;'>Name</th>";
    echo "<th style='border: 1px solid #ddd; padding: 12px;'>Email</th>";
    echo "<th style='border: 1px solid #ddd; padding: 12px;'>XP</th>";
    echo "<th style='border: 1px solid #ddd; padding: 12px;'>Level</th>";
    echo "</tr>";
    
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td style='border: 1px solid #ddd; padding: 12px;'>{$user['id']}</td>";
        echo "<td style='border: 1px solid #ddd; padding: 12px;'>{$user['name']}</td>";
        echo "<td style='border: 1px solid #ddd; padding: 12px;'>{$user['email']}</td>";
        echo "<td style='border: 1px solid #ddd; padding: 12px;'>{$user['xp']}</td>";
        echo "<td style='border: 1px solid #ddd; padding: 12px;'>{$user['level']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2 class='success'>✅ XP System Fixed!</h2>";
    echo "<p>All users now have XP and Level initialized. You can now complete workouts and earn XP!</p>";
    echo "<br><a href='pages/dashboard.php' style='padding: 10px 20px; background: #4A9DB5; color: white; text-decoration: none; border-radius: 8px;'>Go to Dashboard</a>";
    echo " ";
    echo "<a href='check-xp-system.php' style='padding: 10px 20px; background: #3D8BA3; color: white; text-decoration: none; border-radius: 8px;'>Run Diagnostic</a>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Please make sure you've run setup-gamification.php first.</p>";
    echo "<a href='setup-gamification.php' style='padding: 10px 20px; background: #4A9DB5; color: white; text-decoration: none; border-radius: 8px;'>Run Setup</a>";
}
?>
