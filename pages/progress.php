<?php
/**
 * Progress Tracking Page
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
    <title>Progress Tracker - NutriCoach AI</title>
    <link rel="stylesheet" href="/xampp/NutriCoachAI/assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="container" style="padding: 3rem 0;">
        <h1 class="mb-4">📊 Track Your Progress</h1>

        <!-- Log Progress Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h3>Log Today's Progress</h3>
            </div>
            <div class="card-body">
                <form id="progressForm">
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label class="form-label">Weight (kg or lbs)</label>
                                <input type="number" name="weight" class="form-control" step="0.1">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label class="form-label">Body Fat %</label>
                                <input type="number" name="body_fat_percentage" class="form-control" step="0.1">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label class="form-label">Calories Consumed</label>
                                <input type="number" name="calories_consumed" class="form-control">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label class="form-label">Workout Duration (minutes)</label>
                                <input type="number" name="workout_duration" class="form-control">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-label">
                                    <input type="checkbox" name="workout_completed" value="1">
                                    I completed my workout today
                                </label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-label">Notes (optional)</label>
                                <textarea name="notes" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Log Progress</button>
                </form>
            </div>
        </div>

        <!-- Progress Charts -->
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h3>Weight Progress</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="weightChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h3>Workout Consistency</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="workoutChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress History -->
        <div class="card">
            <div class="card-header">
                <h3>Progress History</h3>
            </div>
            <div class="card-body">
                <div id="progressHistory">
                    <div class="flex-center" style="padding: 2rem;">
                        <div class="spinner"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script src="/xampp/NutriCoachAI/assets/js/main.js"></script>
    <script>
        const { Utils, Fitness } = window.NutriCoach;
        let weightChart, workoutChart;

        // Load progress data
        async function loadProgress() {
            try {
                const response = await Fitness.getProgress(30);
                const logs = response.data.logs;

                if (logs && logs.length > 0) {
                    renderCharts(logs);
                    renderHistory(logs);
                } else {
                    document.getElementById('progressHistory').innerHTML = `
                        <div class="empty-state">
                            <div class="empty-state-icon">📊</div>
                            <p>No progress data yet. Start logging your progress!</p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error loading progress:', error);
                Utils.showAlert('Failed to load progress data', 'error');
            }
        }

        // Render charts
        function renderCharts(logs) {
            const dates = logs.map(log => new Date(log.log_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
            const weights = logs.map(log => log.weight || null);
            const workouts = logs.map(log => log.workout_completed ? 1 : 0);

            // Weight Chart
            const weightCtx = document.getElementById('weightChart').getContext('2d');
            if (weightChart) weightChart.destroy();
            
            weightChart = new Chart(weightCtx, {
                type: 'line',
                data: {
                    labels: dates,
                    datasets: [{
                        label: 'Weight',
                        data: weights,
                        borderColor: '#4CAF50',
                        backgroundColor: 'rgba(76, 175, 80, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: false
                        }
                    }
                }
            });

            // Workout Chart
            const workoutCtx = document.getElementById('workoutChart').getContext('2d');
            if (workoutChart) workoutChart.destroy();
            
            workoutChart = new Chart(workoutCtx, {
                type: 'bar',
                data: {
                    labels: dates,
                    datasets: [{
                        label: 'Workouts Completed',
                        data: workouts,
                        backgroundColor: '#2196F3'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 1,
                            ticks: {
                                stepSize: 1,
                                callback: function(value) {
                                    return value === 1 ? 'Yes' : 'No';
                                }
                            }
                        }
                    }
                }
            });
        }

        // Render history table
        function renderHistory(logs) {
            let html = '<div class="table-responsive"><table style="width: 100%; border-collapse: collapse;">';
            html += '<thead><tr style="border-bottom: 2px solid var(--border-color);">';
            html += '<th style="padding: 1rem; text-align: left;">Date</th>';
            html += '<th style="padding: 1rem; text-align: left;">Weight</th>';
            html += '<th style="padding: 1rem; text-align: left;">Calories</th>';
            html += '<th style="padding: 1rem; text-align: left;">Workout</th>';
            html += '</tr></thead><tbody>';

            logs.reverse().forEach(log => {
                html += '<tr style="border-bottom: 1px solid var(--border-color);">';
                html += `<td style="padding: 1rem;">${new Date(log.log_date).toLocaleDateString()}</td>`;
                html += `<td style="padding: 1rem;">${log.weight || '-'}</td>`;
                html += `<td style="padding: 1rem;">${log.calories_consumed || '-'}</td>`;
                html += `<td style="padding: 1rem;">${log.workout_completed ? '✅' : '❌'}</td>`;
                html += '</tr>';
            });

            html += '</tbody></table></div>';
            document.getElementById('progressHistory').innerHTML = html;
        }

        // Form submission
        document.getElementById('progressForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(e.target);
            const data = {
                weight: formData.get('weight') ? parseFloat(formData.get('weight')) : null,
                body_fat_percentage: formData.get('body_fat_percentage') ? parseFloat(formData.get('body_fat_percentage')) : null,
                calories_consumed: formData.get('calories_consumed') ? parseInt(formData.get('calories_consumed')) : null,
                workout_duration: formData.get('workout_duration') ? parseInt(formData.get('workout_duration')) : null,
                workout_completed: formData.get('workout_completed') === '1',
                notes: formData.get('notes') || null
            };

            const submitBtn = e.target.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Logging...';

            try {
                await Fitness.logProgress(data);
                Utils.showAlert('Progress logged successfully!', 'success');
                e.target.reset();
                await loadProgress();
            } catch (error) {
                Utils.showAlert(error.message, 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Log Progress';
            }
        });

        document.addEventListener('DOMContentLoaded', loadProgress);

        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.NutriCoach.Auth.logout().then(() => {
                    window.location.href = '/';
                });
            }
        }
    </script>

    <style>
        .table-responsive {
            overflow-x: auto;
        }

        @media (max-width: 768px) {
            table {
                font-size: 0.875rem;
            }

            th, td {
                padding: 0.5rem !important;
            }
        }
    </style>
</body>
</html>
