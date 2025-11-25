<?php
/**
 * AI-Powered Meal Plan Page (Filipino Food Focus)
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
$db = getDB();

// Get user profile for personalized recommendations
$stmt = $db->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
$stmt->execute([getCurrentUserId()]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0A1628">
    <title>Meal Plan - NutriCoach AI</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dark-theme.css">
    <link rel="stylesheet" href="../assets/css/meal-dark.css">
</head>
<body class="dark-theme">
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="container" style="padding: 2rem 0;">
        <h1 class="mb-4">🍽️ Filipino Meal Plan</h1>

        <!-- User's Nutrition Goals -->
        <?php if ($profile): ?>
        <div class="card mb-4" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white;">
            <div class="card-body">
                <h3 style="margin-bottom: 1rem;">Your Daily Nutrition Goals</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
                    <div>
                        <div style="font-size: 2rem; font-weight: bold;"><?php echo $profile['daily_calories']; ?></div>
                        <div style="opacity: 0.9;">Calories</div>
                    </div>
                    <div>
                        <div style="font-size: 2rem; font-weight: bold;"><?php echo $profile['protein_grams']; ?>g</div>
                        <div style="opacity: 0.9;">Protein</div>
                    </div>
                    <div>
                        <div style="font-size: 2rem; font-weight: bold;"><?php echo $profile['carbs_grams']; ?>g</div>
                        <div style="opacity: 0.9;">Carbs</div>
                    </div>
                    <div>
                        <div style="font-size: 2rem; font-weight: bold;"><?php echo $profile['fats_grams']; ?>g</div>
                        <div style="opacity: 0.9;">Fats</div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Budget Selection -->
        <div class="card mb-4">
            <div class="card-header">
                <h2>Select Your Budget</h2>
                <p>We'll recommend affordable Filipino meals that fit your budget</p>
            </div>
            <div class="card-body">
                <div class="budget-grid">
                    <button class="budget-btn" data-budget="low" onclick="generateMealPlan('low')">
                        <span class="budget-icon">💰</span>
                        <span class="budget-name">Budget-Friendly</span>
                        <span class="budget-desc">₱150-250/day</span>
                    </button>
                    <button class="budget-btn" data-budget="medium" onclick="generateMealPlan('medium')">
                        <span class="budget-icon">💵</span>
                        <span class="budget-name">Moderate</span>
                        <span class="budget-desc">₱250-400/day</span>
                    </button>
                    <button class="budget-btn" data-budget="high" onclick="generateMealPlan('high')">
                        <span class="budget-icon">💸</span>
                        <span class="budget-name">Premium</span>
                        <span class="budget-desc">₱400+/day</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Meal Plan Display -->
        <div id="mealPlanContainer"></div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script src="../assets/js/main.js"></script>
    <script>
        const { Utils, Chat } = window.NutriCoach;
        const userCalories = <?php echo $profile['daily_calories'] ?? 2000; ?>;
        const userProtein = <?php echo $profile['protein_grams'] ?? 150; ?>;
        const userCarbs = <?php echo $profile['carbs_grams'] ?? 200; ?>;
        const userFats = <?php echo $profile['fats_grams'] ?? 60; ?>;
        const userGoal = '<?php echo $profile['fitness_goal'] ?? 'maintain'; ?>';

        async function generateMealPlan(budget) {
            const container = document.getElementById('mealPlanContainer');
            
            // Highlight selected button
            document.querySelectorAll('.budget-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelector(`[data-budget="${budget}"]`).classList.add('active');
            
            // Show loading
            container.innerHTML = `
                <div class="card">
                    <div class="card-body text-center" style="padding: 3rem;">
                        <div class="spinner"></div>
                        <p style="margin-top: 1rem;">AI is creating your personalized Filipino meal plan...</p>
                    </div>
                </div>
            `;
            
            try {
                const budgetText = {
                    low: 'budget-friendly (₱150-250/day)',
                    medium: 'moderate budget (₱250-400/day)',
                    high: 'premium budget (₱400+/day)'
                };
                
                const prompt = `Create a one-day Filipino meal plan for someone with these goals:
- Daily calories: ${userCalories}
- Protein: ${userProtein}g
- Carbs: ${userCarbs}g
- Fats: ${userFats}g
- Fitness goal: ${userGoal}
- Budget: ${budgetText[budget]}

Provide 3 meals (breakfast, lunch, dinner) and 2 snacks using ONLY common Filipino foods that are affordable and easy to find in local markets or carinderias.

For each meal, include:
1. Meal name (in Filipino if applicable)
2. Main ingredients
3. Estimated calories
4. Why it fits the nutrition goals

Focus on realistic, affordable Filipino dishes like:
- Breakfast: Tapsilog, Longsilog, Champorado, Pandesal with filling
- Lunch/Dinner: Adobo, Sinigang, Tinola, Giniling, Paksiw, with rice
- Snacks: Banana, Boiled egg, Peanuts, Turon, Banana cue

Make it practical for Filipinos on a budget!`;
                
                const response = await Chat.sendMessage(prompt);
                
                if (response && response.response) {
                    displayMealPlan(budget, response.response);
                } else if (response && response.data && response.data.response) {
                    displayMealPlan(budget, response.data.response);
                } else {
                    throw new Error('Invalid response from AI');
                }
                
            } catch (error) {
                console.error('Error generating meal plan:', error);
                container.innerHTML = `
                    <div class="alert alert-error">
                        <strong>Error:</strong> Failed to generate meal plan. Please try again.
                    </div>
                `;
            }
        }
        
        function displayMealPlan(budget, aiResponse) {
            const container = document.getElementById('mealPlanContainer');
            
            const budgetEmojis = {
                low: '💰',
                medium: '💵',
                high: '💸'
            };
            
            const budgetNames = {
                low: 'Budget-Friendly',
                medium: 'Moderate',
                high: 'Premium'
            };
            
            container.innerHTML = `
                <div class="card">
                    <div class="card-header">
                        <h2>${budgetEmojis[budget]} ${budgetNames[budget]} Filipino Meal Plan</h2>
                        <p>AI-Generated personalized meal plan with local Filipino food</p>
                    </div>
                    <div class="card-body">
                        <div class="ai-meal-response">
                            ${aiResponse.replace(/\n/g, '<br>')}
                        </div>
                        
                        <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
                            <h3 style="margin-bottom: 1rem;">🛒 Shopping Tips:</h3>
                            <ul style="list-style: none; padding: 0;">
                                <li style="padding: 0.5rem 0;">✅ Buy from local palengke for cheaper prices</li>
                                <li style="padding: 0.5rem 0;">✅ Cook in batches to save time and money</li>
                                <li style="padding: 0.5rem 0;">✅ Choose seasonal vegetables for better prices</li>
                                <li style="padding: 0.5rem 0;">✅ Eggs and canned fish are affordable protein sources</li>
                                <li style="padding: 0.5rem 0;">✅ Rice is your friend - it's filling and cheap!</li>
                            </ul>
                        </div>
                        
                        <div style="margin-top: 2rem;">
                            <button onclick="generateMealPlan('${budget}')" class="btn btn-secondary" style="margin-right: 1rem;">
                                🔄 Generate New Plan
                            </button>
                            <button onclick="customizeMeal()" class="btn btn-primary">
                                ✏️ Customize Meals
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }
        
        async function customizeMeal() {
            const userInput = prompt('What food would you like to replace or add? (e.g., "Replace adobo with sinigang" or "Add more vegetables")');
            
            if (!userInput) return;
            
            const container = document.getElementById('mealPlanContainer');
            container.innerHTML = `
                <div class="card">
                    <div class="card-body text-center" style="padding: 3rem;">
                        <div class="spinner"></div>
                        <p style="margin-top: 1rem;">Customizing your meal plan...</p>
                    </div>
                </div>
            `;
            
            try {
                const response = await Chat.sendMessage(`Based on my previous meal plan, ${userInput}. Keep it Filipino, affordable, and within my nutrition goals (${userCalories} calories, ${userProtein}g protein). Provide the updated meal suggestion.`);
                
                const aiResponse = response.response || response.data?.response;
                if (aiResponse) {
                    container.innerHTML = `
                        <div class="card">
                            <div class="card-header">
                                <h3>🤖 AI Suggestion</h3>
                            </div>
                            <div class="card-body">
                                <div class="ai-meal-response">
                                    ${aiResponse.replace(/\n/g, '<br>')}
                                </div>
                                <div style="margin-top: 2rem;">
                                    <button onclick="customizeMeal()" class="btn btn-primary">
                                        ✏️ Customize Again
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error customizing meal:', error);
                alert('Failed to customize meal. Please try again.');
            }
        }
    </script>

    <style>
        .budget-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        
        .budget-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            padding: 1.5rem 1rem;
            background: var(--bg-light);
            border: 2px solid var(--border-color);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .budget-btn:hover {
            transform: translateY(-4px);
            border-color: var(--primary-color);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .budget-btn.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .budget-icon {
            font-size: 2.5rem;
        }
        
        .budget-name {
            font-weight: 600;
            font-size: 1.125rem;
        }
        
        .budget-desc {
            font-size: 0.875rem;
            opacity: 0.8;
        }
        
        .ai-meal-response {
            background: var(--bg-light);
            padding: 1.5rem;
            border-radius: 8px;
            border-left: 4px solid var(--secondary-color);
            line-height: 1.8;
        }
        
        @media (max-width: 768px) {
            .budget-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>
