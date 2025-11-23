<?php
/**
 * Meal Plan Page
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meal Plan - NutriCoach AI</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="container" style="padding: 3rem 0;">
        <div class="flex-between mb-4">
            <h1>🥗 Your Meal Plan</h1>
            <input type="date" id="dateSelector" class="form-control" style="max-width: 200px;" value="<?php echo date('Y-m-d'); ?>">
        </div>

        <div id="mealPlanContainer">
            <div class="flex-center" style="padding: 4rem;">
                <div class="spinner"></div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script src="../assets/js/main.js"></script>
    <script>
        const { Utils, Fitness } = window.NutriCoach;

        async function loadMealPlan(date = null) {
            const container = document.getElementById('mealPlanContainer');

            try {
                const response = await Fitness.getMealPlan(date);
                const meals = response.meals;
                const totals = response.totals;
                const targets = response.targets;

                if (!meals || meals.length === 0) {
                    container.innerHTML = `
                        <div class="card text-center">
                            <div class="empty-state">
                                <div class="empty-state-icon">🥗</div>
                                <h3>No Meals Planned</h3>
                                <p>Your meal plan will be generated automatically.</p>
                            </div>
                        </div>
                    `;
                    return;
                }

                // Summary Card
                let html = `
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3>Daily Summary</h3>
                        </div>
                        <div class="card-body">
                            <div class="macro-grid">
                                <div class="macro-item">
                                    <div class="macro-label">Calories</div>
                                    <div class="macro-value">${totals.calories} / ${targets.calories}</div>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: ${Math.min((totals.calories / targets.calories) * 100, 100)}%"></div>
                                    </div>
                                </div>
                                <div class="macro-item">
                                    <div class="macro-label">Protein</div>
                                    <div class="macro-value">${totals.protein}g / ${targets.protein}g</div>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: ${Math.min((totals.protein / targets.protein) * 100, 100)}%"></div>
                                    </div>
                                </div>
                                <div class="macro-item">
                                    <div class="macro-label">Carbs</div>
                                    <div class="macro-value">${totals.carbs}g / ${targets.carbs}g</div>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: ${Math.min((totals.carbs / targets.carbs) * 100, 100)}%"></div>
                                    </div>
                                </div>
                                <div class="macro-item">
                                    <div class="macro-label">Fats</div>
                                    <div class="macro-value">${totals.fats}g / ${targets.fats}g</div>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: ${Math.min((totals.fats / targets.fats) * 100, 100)}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                // Meal Cards
                const mealIcons = {
                    breakfast: '🍳',
                    lunch: '🍽️',
                    dinner: '🍲',
                    snack: '🍎'
                };

                meals.forEach(meal => {
                    html += `
                        <div class="card mb-3">
                            <div class="card-header">
                                <h3>${mealIcons[meal.meal_type] || '🍴'} ${meal.meal_name}</h3>
                                <span class="meal-type-badge">${meal.meal_type.toUpperCase()}</span>
                            </div>
                            <div class="card-body">
                                <p>${meal.description}</p>
                                <div class="meal-macros">
                                    <span><strong>Calories:</strong> ${meal.calories}</span>
                                    <span><strong>Protein:</strong> ${meal.protein}g</span>
                                    <span><strong>Carbs:</strong> ${meal.carbs}g</span>
                                    <span><strong>Fats:</strong> ${meal.fats}g</span>
                                </div>
                            </div>
                        </div>
                    `;
                });

                container.innerHTML = html;

            } catch (error) {
                console.error('Error loading meal plan:', error);
                container.innerHTML = `
                    <div class="alert alert-error">
                        Failed to load meal plan. Please try again later.
                    </div>
                `;
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            loadMealPlan();

            document.getElementById('dateSelector').addEventListener('change', (e) => {
                loadMealPlan(e.target.value);
            });
        });

        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.NutriCoach.Auth.logout().then(() => {
                    window.location.href = '/';
                });
            }
        }
    </script>

    <style>
        .macro-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        .macro-item {
            text-align: center;
        }

        .macro-label {
            font-size: 0.875rem;
            color: var(--text-light);
            margin-bottom: 0.5rem;
        }

        .macro-value {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .meal-type-badge {
            padding: 0.25rem 0.75rem;
            background-color: var(--primary-color);
            color: white;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .meal-macros {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border-color);
        }

        .meal-macros span {
            font-size: 0.875rem;
        }

        @media (max-width: 768px) {
            .macro-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .meal-macros {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
    </style>
</body>
</html>
