<?php
/**
 * Modern Meal Tracker Page - Daily Calorie & Macro Tracking
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

initSession();

if (!isLoggedIn()) {
    redirect('/');
}

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
    <title>Meal Tracker - NutriCoach AI</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dark-theme.css">
    <link rel="stylesheet" href="../assets/css/meal-tracker-dark.css">
</head>
<body class="dark-theme">
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="meal-tracker-container">
        <!-- Header with Date -->
        <div class="tracker-header">
            <h1>🍽️ Meal Tracker</h1>
            <div class="date-selector">
                <div class="date-display-header">
                    <button class="date-nav-btn" onclick="changeMonth(-1)">◀</button>
                    <div class="current-month" id="currentMonth"><?php echo date('F d, Y'); ?></div>
                    <button class="date-nav-btn" onclick="changeMonth(1)">▶</button>
                    <button class="btn-today-small" onclick="goToToday()">Today</button>
                </div>
                <div class="week-days">
                    <span>Mon</span>
                    <span>Tue</span>
                    <span>Today</span>
                    <span>Thu</span>
                    <span>Fri</span>
                    <span>Sat</span>
                    <span>Sun</span>
                </div>
                <div class="week-dates" id="weekDates"></div>
            </div>
        </div>

        <!-- Calorie & Macro Summary -->
        <div class="calorie-summary-card">
            <div class="calorie-circle">
                <svg viewBox="0 0 200 200">
                    <circle cx="100" cy="100" r="90" fill="none" stroke="#1a2332" stroke-width="20"/>
                    <circle id="calorieProgress" cx="100" cy="100" r="90" fill="none" stroke="#ff6b6b" stroke-width="20" 
                            stroke-dasharray="565" stroke-dashoffset="565" transform="rotate(-90 100 100)"/>
                </svg>
                <div class="calorie-text">
                    <div class="calorie-number" id="caloriesLeft"><?php echo $profile['daily_calories']; ?></div>
                    <div class="calorie-label">Kcal Left</div>
                </div>
            </div>

            <div class="macro-stats">
                <div class="macro-stat">
                    <div class="macro-value" id="caloriesEaten">0</div>
                    <div class="macro-label">Eaten</div>
                </div>
                <div class="macro-stat">
                    <div class="macro-value">0</div>
                    <div class="macro-label">Burned</div>
                </div>
            </div>

            <div class="macro-breakdown">
                <div class="macro-item">
                    <div class="macro-header">
                        <span>Carbs</span>
                        <span class="macro-badge carbs" id="carbsPercent">0%</span>
                    </div>
                    <div class="macro-progress">
                        <div class="macro-progress-bar carbs" id="carbsBar" style="width: 0%"></div>
                    </div>
                    <div class="macro-values">
                        <span id="carbsValue">0</span> / <?php echo $profile['carbs_grams']; ?> g
                    </div>
                </div>

                <div class="macro-item">
                    <div class="macro-header">
                        <span>Protein</span>
                        <span class="macro-badge protein" id="proteinPercent">0%</span>
                    </div>
                    <div class="macro-progress">
                        <div class="macro-progress-bar protein" id="proteinBar" style="width: 0%"></div>
                    </div>
                    <div class="macro-values">
                        <span id="proteinValue">0</span> / <?php echo $profile['protein_grams']; ?> g
                    </div>
                </div>

                <div class="macro-item">
                    <div class="macro-header">
                        <span>Fats</span>
                        <span class="macro-badge fats" id="fatsPercent">0%</span>
                    </div>
                    <div class="macro-progress">
                        <div class="macro-progress-bar fats" id="fatsBar" style="width: 0%"></div>
                    </div>
                    <div class="macro-values">
                        <span id="fatsValue">0</span> / <?php echo $profile['fats_grams']; ?> g
                    </div>
                </div>
            </div>
        </div>

        <!-- Log Your Meals Section -->
        <div class="section-header">
            <h2>Log your meals</h2>
        </div>

        <div class="meal-list">
            <div class="meal-card" onclick="openMealLogger('breakfast')">
                <div class="meal-icon">🥞</div>
                <div class="meal-info">
                    <div class="meal-name">Breakfast</div>
                    <div class="meal-calories" id="breakfastCalories">0 Kcal</div>
                </div>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M9 18l6-6-6-6"/>
                </svg>
            </div>

            <div class="meal-card" onclick="openMealLogger('morning-snack')">
                <div class="meal-icon">🍌</div>
                <div class="meal-info">
                    <div class="meal-name">Morning snack</div>
                    <div class="meal-calories" id="morningSnackCalories">0 Kcal</div>
                </div>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M9 18l6-6-6-6"/>
                </svg>
            </div>

            <div class="meal-card" onclick="openMealLogger('lunch')">
                <div class="meal-icon">🍖</div>
                <div class="meal-info">
                    <div class="meal-name">Lunch</div>
                    <div class="meal-calories" id="lunchCalories">0 Kcal</div>
                </div>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M9 18l6-6-6-6"/>
                </svg>
            </div>

            <div class="meal-card" onclick="openMealLogger('afternoon-snack')">
                <div class="meal-icon">🍌</div>
                <div class="meal-info">
                    <div class="meal-name">Afternoon snack</div>
                    <div class="meal-calories" id="afternoonSnackCalories">0 Kcal</div>
                </div>
                <svg width="24" height="24" fill="none" stroke="currentColor">
                    <path d="M9 18l6-6-6-6"/>
                </svg>
            </div>

            <div class="meal-card" onclick="openMealLogger('dinner')">
                <div class="meal-icon">🍽️</div>
                <div class="meal-info">
                    <div class="meal-name">Dinner</div>
                    <div class="meal-calories" id="dinnerCalories">0 Kcal</div>
                </div>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M9 18l6-6-6-6"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Meal Logger Modal -->
    <div class="modal-overlay" id="mealModal" style="display: none;">
        <div class="modal-content meal-modal">
            <div class="modal-header">
                <button class="btn-back" onclick="closeMealLogger()">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                </button>
                <h2 id="modalMealName">Breakfast</h2>
                <button class="btn-icon" onclick="clearMeal()">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                    </svg>
                </button>
            </div>

            <div class="food-categories">
                <button class="category-btn active" data-category="all">All</button>
                <button class="category-btn" data-category="vegetables">🥦 Vegetables</button>
                <button class="category-btn" data-category="fruits">🍎 Fruits</button>
                <button class="category-btn" data-category="protein">🥩 Protein</button>
                <button class="category-btn" data-category="grains">🍞 Grains</button>
                <button class="category-btn" data-category="dairy">🥛 Dairy</button>
            </div>

            <div class="food-list" id="foodList">
                <!-- Food items will be populated here -->
            </div>

            <div class="selected-items" id="selectedItems" style="display: none;">
                <h3>Selected Items <span id="selectedCount">0</span></h3>
                <div id="selectedList"></div>
            </div>

            <button class="btn-log-meal" onclick="logMeal()">Log Meal</button>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/meal-tracker.js"></script>
</body>
</html>
