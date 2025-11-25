<?php
/**
 * Improved AI-Powered Workout Plan Page
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
    <title>Workout Plan - NutriCoach AI</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dark-theme.css">
    <link rel="stylesheet" href="../assets/css/workout-dark.css">
</head>
<body class="dark-theme">
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="workout-container">
        <div class="workout-header">
            <h1>💪 Workout Plan</h1>
            <p>Choose your workout type or target specific muscle groups</p>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <button class="action-card" onclick="window.location.href='workout-ai.php'">
                <span class="icon">🎯</span>
                <div class="title">AI Suggested</div>
                <div class="subtitle">Based on your goals</div>
            </button>
        </div>

        <!-- Muscle Group Selection -->
        <div class="section-header">
            <h2>Target Specific Muscle</h2>
            <p>Choose which area to focus on</p>
        </div>
        
        <div class="muscle-groups">
            <button class="muscle-btn" onclick="window.location.href='workout-muscle.php?muscle=chest'">
                <span class="icon">💪</span>
                <div class="label">Chest</div>
            </button>
            <button class="muscle-btn" onclick="window.location.href='workout-muscle.php?muscle=back'">
                <span class="icon">🏋️</span>
                <div class="label">Back</div>
            </button>
            <button class="muscle-btn" onclick="window.location.href='workout-muscle.php?muscle=legs'">
                <span class="icon">🦵</span>
                <div class="label">Legs</div>
            </button>
            <button class="muscle-btn" onclick="window.location.href='workout-muscle.php?muscle=shoulders'">
                <span class="icon">💪</span>
                <div class="label">Shoulders</div>
            </button>
            <button class="muscle-btn" onclick="window.location.href='workout-muscle.php?muscle=arms'">
                <span class="icon">💪</span>
                <div class="label">Arms</div>
            </button>
            <button class="muscle-btn" onclick="window.location.href='workout-muscle.php?muscle=abs'">
                <span class="icon">🔥</span>
                <div class="label">Abs</div>
            </button>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script src="../assets/js/main.js"></script>
</body>
</html>
