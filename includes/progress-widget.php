<?php
/**
 * Progress Tracking Widget
 * Shows today's progress for calories, macros, water, etc.
 */

// Get user's daily goals
$userId = getCurrentUserId();
$db = getDB();

$stmt = $db->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
$stmt->execute([$userId]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

// Get today's logged data (you'll need to create these tables)
// For now, using sample data
$today = date('Y-m-d');

// Sample progress data (replace with actual database queries later)
$progress = [
    'calories_consumed' => 1700,
    'calories_goal' => $profile['daily_calories'],
    'protein_consumed' => 120,
    'protein_goal' => $profile['protein_grams'],
    'carbs_consumed' => 180,
    'carbs_goal' => $profile['carbs_grams'],
    'fats_consumed' => 50,
    'fats_goal' => $profile['fats_grams'],
    'water_glasses' => 6,
    'water_goal' => 8,
    'workout_done' => false,
    'meals_logged' => 2,
    'meals_goal' => 3
];

// Calculate percentages
$caloriesPercent = min(100, ($progress['calories_consumed'] / $progress['calories_goal']) * 100);
$proteinPercent = min(100, ($progress['protein_consumed'] / $progress['protein_goal']) * 100);
$carbsPercent = min(100, ($progress['carbs_consumed'] / $progress['carbs_goal']) * 100);
$fatsPercent = min(100, ($progress['fats_consumed'] / $progress['fats_goal']) * 100);
$waterPercent = ($progress['water_glasses'] / $progress['water_goal']) * 100;
?>

<div class="progress-widget card">
    <div class="card-header">
        <h2>📊 Today's Progress</h2>
        <p><?php echo date('l, F j'); ?></p>
    </div>
    <div class="card-body">
        
        <!-- Calories Progress -->
        <div class="progress-item">
            <div class="progress-header">
                <span class="progress-label">🔥 Calories</span>
                <span class="progress-value"><?php echo $progress['calories_consumed']; ?> / <?php echo $progress['calories_goal']; ?></span>
            </div>
            <div class="progress-bar-container">
                <div class="progress-bar-fill" style="width: <?php echo $caloriesPercent; ?>%; background: linear-gradient(90deg, #FF6B6B, #FF8E53);"></div>
            </div>
            <div class="progress-percent"><?php echo round($caloriesPercent); ?>%</div>
        </div>

        <!-- Protein Progress -->
        <div class="progress-item">
            <div class="progress-header">
                <span class="progress-label">💪 Protein</span>
                <span class="progress-value"><?php echo $progress['protein_consumed']; ?>g / <?php echo $progress['protein_goal']; ?>g</span>
            </div>
            <div class="progress-bar-container">
                <div class="progress-bar-fill" style="width: <?php echo $proteinPercent; ?>%; background: linear-gradient(90deg, #4A9DB5, #5BB4CC);"></div>
            </div>
            <div class="progress-percent"><?php echo round($proteinPercent); ?>%</div>
        </div>

        <!-- Carbs Progress -->
        <div class="progress-item">
            <div class="progress-header">
                <span class="progress-label">🍚 Carbs</span>
                <span class="progress-value"><?php echo $progress['carbs_consumed']; ?>g / <?php echo $progress['carbs_goal']; ?>g</span>
            </div>
            <div class="progress-bar-container">
                <div class="progress-bar-fill" style="width: <?php echo $carbsPercent; ?>%; background: linear-gradient(90deg, #FFD93D, #FF9A3D);"></div>
            </div>
            <div class="progress-percent"><?php echo round($carbsPercent); ?>%</div>
        </div>

        <!-- Fats Progress -->
        <div class="progress-item">
            <div class="progress-header">
                <span class="progress-label">🥑 Fats</span>
                <span class="progress-value"><?php echo $progress['fats_consumed']; ?>g / <?php echo $progress['fats_goal']; ?>g</span>
            </div>
            <div class="progress-bar-container">
                <div class="progress-bar-fill" style="width: <?php echo $fatsPercent; ?>%; background: linear-gradient(90deg, #A8E6CF, #3DDC84);"></div>
            </div>
            <div class="progress-percent"><?php echo round($fatsPercent); ?>%</div>
        </div>

        <!-- Water Progress -->
        <div class="progress-item">
            <div class="progress-header">
                <span class="progress-label">💧 Water</span>
                <span class="progress-value"><?php echo $progress['water_glasses']; ?> / <?php echo $progress['water_goal']; ?> glasses</span>
            </div>
            <div class="progress-bar-container">
                <div class="progress-bar-fill" style="width: <?php echo $waterPercent; ?>%; background: linear-gradient(90deg, #4ECDC4, #44A08D);"></div>
            </div>
            <div class="progress-percent"><?php echo round($waterPercent); ?>%</div>
        </div>

        <!-- Quick Summary -->
        <div class="progress-summary">
            <div class="summary-item">
                <span class="summary-icon">🍽️</span>
                <span class="summary-text">Meals: <?php echo $progress['meals_logged']; ?>/<?php echo $progress['meals_goal']; ?></span>
            </div>
            <div class="summary-item">
                <span class="summary-icon">💪</span>
                <span class="summary-text">Workout: <?php echo $progress['workout_done'] ? 'Done ✅' : 'Pending'; ?></span>
            </div>
        </div>

    </div>
</div>

<style>
.progress-widget {
    margin-bottom: 1.5rem;
}

.progress-item {
    margin-bottom: 1.5rem;
}

.progress-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.progress-label {
    font-weight: 600;
    font-size: 0.95rem;
    color: #2c3e50;
}

.progress-value {
    font-size: 0.875rem;
    color: #7f8c8d;
    font-weight: 600;
}

.progress-bar-container {
    height: 12px;
    background: #E8EEF2;
    border-radius: 10px;
    overflow: hidden;
    position: relative;
    margin-bottom: 0.25rem;
}

.progress-bar-fill {
    height: 100%;
    border-radius: 10px;
    transition: width 0.6s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.progress-percent {
    text-align: right;
    font-size: 0.75rem;
    color: #7f8c8d;
    font-weight: 600;
}

.progress-summary {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid #E8EEF2;
}

.summary-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem;
    background: #F8FAFB;
    border-radius: 12px;
}

.summary-icon {
    font-size: 1.5rem;
}

.summary-text {
    font-size: 0.875rem;
    font-weight: 600;
    color: #2c3e50;
}

@media (max-width: 768px) {
    .progress-item {
        margin-bottom: 1.25rem;
    }
    
    .progress-bar-container {
        height: 10px;
    }
    
    .progress-summary {
        grid-template-columns: 1fr;
    }
}
</style>
