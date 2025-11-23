<?php
/**
 * Workout Plan Page
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
    <title>Workout Plan - NutriCoach AI</title>
    <link rel="stylesheet" href="/xampp/NutriCoachAI/assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="container" style="padding: 3rem 0;">
        <h1 class="mb-4">💪 Your Personalized Workout Plan</h1>

        <div id="workoutPlanContainer">
            <div class="flex-center" style="padding: 4rem;">
                <div class="spinner"></div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script src="/xampp/NutriCoachAI/assets/js/main.js"></script>
    <script>
        const { Utils, Fitness } = window.NutriCoach;

        async function loadWorkoutPlan() {
            const container = document.getElementById('workoutPlanContainer');

            try {
                const response = await Fitness.getWorkoutPlan();
                const plan = response.data.plan;

                if (!plan || !plan.exercises) {
                    container.innerHTML = `
                        <div class="card text-center">
                            <div class="empty-state">
                                <div class="empty-state-icon">💪</div>
                                <h3>No Workout Plan Available</h3>
                                <p>Please contact support to generate your personalized plan.</p>
                            </div>
                        </div>
                    `;
                    return;
                }

                let html = `
                    <div class="card mb-4">
                        <div class="card-header">
                            <h2>${plan.plan_name}</h2>
                            <p>${plan.description}</p>
                        </div>
                    </div>
                `;

                plan.exercises.forEach((workout, index) => {
                    html += `
                        <div class="card mb-4">
                            <div class="card-header">
                                <h3>${workout.day} - ${workout.focus}</h3>
                            </div>
                            <div class="card-body">
                                <div class="workout-exercises">
                    `;

                    workout.exercises.forEach(exercise => {
                        html += `
                            <div class="exercise-item">
                                <div class="exercise-name">${exercise.name}</div>
                                <div class="exercise-details">
                                    <span class="badge">${exercise.sets} sets</span>
                                    <span class="badge">${exercise.reps} reps</span>
                                </div>
                            </div>
                        `;
                    });

                    html += `
                                </div>
                            </div>
                        </div>
                    `;
                });

                container.innerHTML = html;

            } catch (error) {
                console.error('Error loading workout plan:', error);
                container.innerHTML = `
                    <div class="alert alert-error">
                        Failed to load workout plan. Please try again later.
                    </div>
                `;
            }
        }

        document.addEventListener('DOMContentLoaded', loadWorkoutPlan);

        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.NutriCoach.Auth.logout().then(() => {
                    window.location.href = '/';
                });
            }
        }
    </script>

    <style>
        .workout-exercises {
            display: grid;
            gap: 1rem;
        }

        .exercise-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background-color: var(--bg-light);
            border-radius: 8px;
            border-left: 4px solid var(--primary-color);
        }

        .exercise-name {
            font-weight: 600;
            font-size: 1.125rem;
        }

        .exercise-details {
            display: flex;
            gap: 0.5rem;
        }

        .badge {
            padding: 0.25rem 0.75rem;
            background-color: var(--primary-color);
            color: white;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .exercise-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
        }
    </style>
</body>
</html>
