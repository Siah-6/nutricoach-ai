<?php
/**
 * AI Meal Plan Page
 * Generates personalized daily meal plan with AI
 */

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

// Calculate daily calorie goal based on profile
$calorieGoal = $profile['daily_calorie_goal'] ?? 2000;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0A1628">
    <title>AI Meal Plan - NutriCoach AI</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dark-theme.css">
    <link rel="stylesheet" href="../assets/css/meal-plan-dark.css">
</head>
<body class="dark-theme">
    <div class="meal-plan-container">
        <!-- Header with Back Button -->
        <div class="meal-plan-header">
            <button class="back-btn" onclick="window.history.back()">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
            </button>
            <div class="header-title">
                <h1>🍽️ AI Meal Plan</h1>
                <p>Personalized for your goals</p>
            </div>
        </div>

        <!-- Loading State -->
        <div class="loading-state" id="loadingState">
            <div class="spinner"></div>
            <p>Generating your personalized meal plan...</p>
        </div>

        <!-- Completion Screen -->
        <div class="completion-screen" id="completionScreen" style="display: none;">
            <div class="completion-content">
                <div class="completion-icon">🎉</div>
                <h2>Meal Plan Already Completed!</h2>
                <p>You've already completed today's meal plan!</p>
                <div class="completion-stats">
                    <div class="stat-item">
                        <span class="stat-icon">✅</span>
                        <span class="stat-label">All Meals Eaten</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-icon">🔥</span>
                        <span class="stat-label">Come back tomorrow!</span>
                    </div>
                </div>
                <button class="btn-back" onclick="window.history.back()">← Back to Dashboard</button>
            </div>
        </div>

        <!-- Meal Plan Content -->
        <div class="meal-plan-content" id="mealPlanContent" style="display: none;">
            <!-- Daily Nutrition Summary -->
            <div class="nutrition-summary">
                <h3>📊 Today's Nutrition</h3>
                <div class="nutrition-stats">
                    <div class="stat-card">
                        <span class="stat-icon">🔥</span>
                        <div class="stat-info">
                            <span class="stat-value" id="totalCalories">0</span>
                            <span class="stat-label">/ <?php echo $calorieGoal; ?> cal</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <span class="stat-icon">💪</span>
                        <div class="stat-info">
                            <span class="stat-value" id="totalProtein">0g</span>
                            <span class="stat-label">Protein</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <span class="stat-icon">🍞</span>
                        <div class="stat-info">
                            <span class="stat-value" id="totalCarbs">0g</span>
                            <span class="stat-label">Carbs</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <span class="stat-icon">🥑</span>
                        <div class="stat-info">
                            <span class="stat-value" id="totalFats">0g</span>
                            <span class="stat-label">Fats</span>
                        </div>
                    </div>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill" style="width: 0%;"></div>
                </div>
                <p class="progress-text" id="progressText">0% of daily goal</p>
            </div>

            <!-- Meals List -->
            <div class="meals-list" id="mealsList"></div>

            <!-- Complete All Button -->
            <div class="complete-section" id="completeSection" style="display: none;">
                <button class="btn-complete-plan" onclick="completeMealPlan()">
                    ✅ Complete Meal Plan
                </button>
            </div>
        </div>
    </div>

    <script>
        let currentMeals = [];
        let completedMeals = new Set();
        const calorieGoal = <?php echo $calorieGoal; ?>;

        // Generate AI meal plan on page load
        window.addEventListener('DOMContentLoaded', async () => {
            // Initialize nutrition summary to 0
            updateNutritionSummary();
            
            const isCompleted = await checkCompletionStatus();
            
            if (!isCompleted) {
                const savedState = sessionStorage.getItem('aiMealPlanState');
                if (savedState) {
                    restoreMealPlanState(JSON.parse(savedState));
                } else {
                    generateAIMealPlan();
                }
            }
        });

        async function checkCompletionStatus() {
            try {
                const response = await fetch(`../api/meal/check-meal-plan-completion.php`);
                const data = await response.json();
                
                if (data.success && data.completed_today) {
                    document.getElementById('loadingState').style.display = 'none';
                    document.getElementById('mealPlanContent').style.display = 'none';
                    document.getElementById('completionScreen').style.display = 'block';
                    sessionStorage.removeItem('aiMealPlanState');
                    return true;
                }
                return false;
            } catch (error) {
                console.error('Error checking completion:', error);
                return false;
            }
        }

        async function generateAIMealPlan() {
            const loadingState = document.getElementById('loadingState');
            const mealPlanContent = document.getElementById('mealPlanContent');

            try {
                const prompt = `Create a daily meal plan with EXACTLY 4 meals for a person with ${calorieGoal} calorie goal.
                User profile: ${<?php echo json_encode($profile['fitness_level']); ?>} fitness level, goal: ${<?php echo json_encode($profile['fitness_goal']); ?>}.
                
                IMPORTANT: Format STRICTLY as:
                1. Breakfast: [Meal Name]
                   Ingredients: [list]
                   Calories: [number] | Protein: [number]g | Carbs: [number]g | Fats: [number]g
                
                2. Lunch: [Meal Name]
                   Ingredients: [list]
                   Calories: [number] | Protein: [number]g | Carbs: [number]g | Fats: [number]g
                
                3. Dinner: [Meal Name]
                   Ingredients: [list]
                   Calories: [number] | Protein: [number]g | Carbs: [number]g | Fats: [number]g
                
                4. Snack: [Meal Name]
                   Ingredients: [list]
                   Calories: [number] | Protein: [number]g | Carbs: [number]g | Fats: [number]g
                
                No greetings, no extra text, ONLY the meal plan.`;

                const response = await fetch('../api/fitness/generate-workout.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ prompt: prompt })
                });

                const data = await response.json();
                
                if (data.success || data.response) {
                    const aiResponse = data.response || data.data?.response;
                    parseAndDisplayMealPlan(aiResponse);
                    
                    loadingState.style.display = 'none';
                    mealPlanContent.style.display = 'block';
                } else {
                    throw new Error('Failed to generate meal plan');
                }
            } catch (error) {
                console.error('Error:', error);
                loadingState.innerHTML = '<p style="color: #ff6b6b;">Failed to generate meal plan. Please try again.</p>';
            }
        }

        function parseAndDisplayMealPlan(content) {
            const lines = content.split('\n').filter(line => line.trim());
            currentMeals = [];
            
            let currentMeal = null;
            
            lines.forEach(line => {
                line = line.trim();
                
                // Check for meal type (Breakfast, Lunch, Dinner, Snack)
                if (line.match(/^\d+\.\s*(Breakfast|Lunch|Dinner|Snack):/i)) {
                    if (currentMeal) currentMeals.push(currentMeal);
                    
                    const mealMatch = line.match(/^\d+\.\s*(Breakfast|Lunch|Dinner|Snack):\s*(.+)/i);
                    currentMeal = {
                        type: mealMatch[1],
                        name: mealMatch[2],
                        ingredients: '',
                        calories: 0,
                        protein: 0,
                        carbs: 0,
                        fats: 0
                    };
                }
                // Check for ingredients
                else if (line.match(/Ingredients:/i) && currentMeal) {
                    currentMeal.ingredients = line.replace(/Ingredients:/i, '').trim();
                }
                // Check for macros
                else if (line.match(/Calories:/i) && currentMeal) {
                    const caloriesMatch = line.match(/Calories:\s*(\d+)/i);
                    const proteinMatch = line.match(/Protein:\s*(\d+)g/i);
                    const carbsMatch = line.match(/Carbs:\s*(\d+)g/i);
                    const fatsMatch = line.match(/Fats:\s*(\d+)g/i);
                    
                    if (caloriesMatch) currentMeal.calories = parseInt(caloriesMatch[1]);
                    if (proteinMatch) currentMeal.protein = parseInt(proteinMatch[1]);
                    if (carbsMatch) currentMeal.carbs = parseInt(carbsMatch[1]);
                    if (fatsMatch) currentMeal.fats = parseInt(fatsMatch[1]);
                }
            });
            
            if (currentMeal) currentMeals.push(currentMeal);
            
            displayMeals();
            saveMealPlanState();
        }

        function displayMeals() {
            const mealsList = document.getElementById('mealsList');
            mealsList.innerHTML = '';
            
            const mealIcons = {
                'Breakfast': '🍳',
                'Lunch': '🥗',
                'Dinner': '🍽️',
                'Snack': '🍎'
            };
            
            currentMeals.forEach((meal, index) => {
                const isCompleted = completedMeals.has(index);
                const mealCard = document.createElement('div');
                mealCard.className = `meal-card ${isCompleted ? 'completed' : ''}`;
                mealCard.innerHTML = `
                    <div class="meal-header">
                        <div class="meal-type">
                            <span class="meal-icon">${mealIcons[meal.type] || '🍴'}</span>
                            <div>
                                <h4>${meal.type}</h4>
                                <p class="meal-name">${meal.name}</p>
                            </div>
                        </div>
                        <button class="check-btn ${isCompleted ? 'checked' : ''}" onclick="toggleMeal(${index})">
                            ${isCompleted ? '✓' : ''}
                        </button>
                    </div>
                    <div class="meal-details">
                        <p class="ingredients">${meal.ingredients}</p>
                        <div class="meal-macros">
                            <span>🔥 ${meal.calories} cal</span>
                            <span>💪 ${meal.protein}g</span>
                            <span>🍞 ${meal.carbs}g</span>
                            <span>🥑 ${meal.fats}g</span>
                        </div>
                    </div>
                `;
                mealsList.appendChild(mealCard);
            });
            
            updateNutritionSummary();
            
            if (completedMeals.size === currentMeals.length && currentMeals.length > 0) {
                document.getElementById('completeSection').style.display = 'block';
            } else {
                document.getElementById('completeSection').style.display = 'none';
            }
        }

        function toggleMeal(index) {
            if (completedMeals.has(index)) {
                completedMeals.delete(index);
            } else {
                completedMeals.add(index);
            }
            
            displayMeals();
            saveMealPlanState();
        }

        function updateNutritionSummary() {
            let totalCal = 0, totalPro = 0, totalCarb = 0, totalFat = 0;
            
            completedMeals.forEach(index => {
                const meal = currentMeals[index];
                if (meal) {
                    totalCal += parseInt(meal.calories) || 0;
                    totalPro += parseInt(meal.protein) || 0;
                    totalCarb += parseInt(meal.carbs) || 0;
                    totalFat += parseInt(meal.fats) || 0;
                }
            });
            
            document.getElementById('totalCalories').textContent = totalCal;
            document.getElementById('totalProtein').textContent = totalPro + 'g';
            document.getElementById('totalCarbs').textContent = totalCarb + 'g';
            document.getElementById('totalFats').textContent = totalFat + 'g';
            
            const progress = calorieGoal > 0 ? Math.min((totalCal / calorieGoal) * 100, 100) : 0;
            document.getElementById('progressFill').style.width = progress + '%';
            document.getElementById('progressText').textContent = Math.round(progress) + '% of daily goal';
        }

        async function completeMealPlan() {
            try {
                console.log('Completing meal plan with:', {
                    meals: currentMeals,
                    completed_meals: Array.from(completedMeals)
                });
                
                const response = await fetch('../api/meal/complete-meal-plan.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        meals: currentMeals,
                        completed_meals: Array.from(completedMeals)
                    })
                });

                const data = await response.json();
                console.log('API Response:', data);
                
                if (data.success) {
                    sessionStorage.removeItem('aiMealPlanState');
                    
                    // Show success message with EXP gained
                    let message = '🎉 Meal Plan Completed! Great job!';
                    if (data.exp_gained) {
                        message += ` (+${data.exp_gained} XP)`;
                    }
                    showNotification(message, 'success');
                    
                    // Wait a moment before reloading to show notification
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    // Show actual error message from API
                    showNotification('Failed to complete meal plan: ' + (data.message || 'Unknown error'), 'error');
                    console.error('API Error:', data);
                }
            } catch (error) {
                console.error('Error completing meal plan:', error);
                alert('Failed to complete meal plan. Error: ' + error.message);
            }
        }

        function saveMealPlanState() {
            const state = {
                meals: currentMeals,
                completed: Array.from(completedMeals)
            };
            sessionStorage.setItem('aiMealPlanState', JSON.stringify(state));
        }

        function restoreMealPlanState(state) {
            currentMeals = state.meals;
            completedMeals = new Set(state.completed);
            
            console.log('Restoring state:', state);
            console.log('Completed meals:', completedMeals);

            document.getElementById('loadingState').style.display = 'none';
            document.getElementById('mealPlanContent').style.display = 'block';

            displayMeals();
        }

        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            const bgColor = type === 'success' ? '#4CAF50' : '#f44336';
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                left: 50%;
                transform: translateX(-50%);
                background: ${bgColor};
                color: white;
                padding: 1rem 2rem;
                border-radius: 10px;
                z-index: 10001;
                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                font-weight: 500;
                max-width: 90%;
                text-align: center;
            `;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.transition = 'opacity 0.3s ease';
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
    </script>
</body>
</html>
