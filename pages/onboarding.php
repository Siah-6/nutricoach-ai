<?php
/**
 * User Onboarding Page
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

initSession();

// Redirect if not logged in
if (!isLoggedIn()) {
    redirect('/');
}

// Redirect if onboarding already completed
if (isOnboardingCompleted(getCurrentUserId())) {
    redirect('/pages/dashboard.php');
}

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0A1628">
    <title>Complete Your Profile - NutriCoach AI</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/onboarding-dark.css">
</head>
<body>
    <div class="onboarding-container">
        <div class="onboarding-header">
            <button type="button" class="back-button" id="backBtn" style="display: none;" onclick="prevStep()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
            </button>
            <div class="progress-bar">
                <div class="progress-fill" id="progressBar"></div>
            </div>
            <h1>Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h1>
            <p>Let's personalize your fitness journey</p>
            <p class="progress-text"><span id="currentStep">1</span> of <span id="totalSteps">8</span></p>
        </div>

        <form id="onboardingForm">
            <!-- Step 1: Gender -->
            <div class="onboarding-step active" data-step="1">
                <h2>Choose your gender</h2>
                <div class="option-group">
                    <label class="option-card">
                        <input type="radio" name="gender" value="male" required>
                        <div class="option-content">
                            <span class="option-label">Male</span>
                            <svg class="gender-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="10" cy="10" r="6"/>
                                <path d="M16 4l4 4M20 4l-4 4M20 8V4h-4"/>
                            </svg>
                        </div>
                    </label>
                    <label class="option-card">
                        <input type="radio" name="gender" value="female" required>
                        <div class="option-content">
                            <span class="option-label">Female</span>
                            <svg class="gender-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="8" r="6"/>
                                <path d="M12 14v6M9 19h6"/>
                            </svg>
                        </div>
                    </label>
                    <label class="option-card">
                        <input type="radio" name="gender" value="other" required>
                        <div class="option-content">
                            <span class="option-label">Other</span>
                            <svg class="gender-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="4"/>
                                <path d="M12 2v4M12 18v4M2 12h4M18 12h4"/>
                            </svg>
                        </div>
                    </label>
                </div>
                <div class="button-group">
                    <button type="button" class="btn btn-primary btn-lg" onclick="nextStep()">Continue</button>
                </div>
            </div>

            <!-- Step 2: Fitness Goal -->
            <div class="onboarding-step" data-step="2">
                <h2>Choose your goal</h2>
                <div class="option-group">
                    <label class="option-card">
                        <input type="radio" name="fitness_goal" value="build_muscle" required>
                        <div class="option-content">
                            <span class="option-label">Build Muscle</span>
                        </div>
                    </label>
                    <label class="option-card">
                        <input type="radio" name="fitness_goal" value="lose_weight" required>
                        <div class="option-content">
                            <span class="option-label">Lose Weight</span>
                        </div>
                    </label>
                    <label class="option-card">
                        <input type="radio" name="fitness_goal" value="look_better" required>
                        <div class="option-content">
                            <span class="option-label">Look Better</span>
                        </div>
                    </label>
                    <label class="option-card">
                        <input type="radio" name="fitness_goal" value="stay_in_shape" required>
                        <div class="option-content">
                            <span class="option-label">Stay in Shape</span>
                        </div>
                    </label>
                </div>
                <div class="button-group">
                    <button type="button" class="btn btn-primary btn-lg" onclick="nextStep()">Continue</button>
                </div>
            </div>

            <!-- Step 3: Fitness Level -->
            <div class="onboarding-step" data-step="3">
                <h2>Choose your fitness level</h2>
                <div class="option-group">
                    <label class="option-card">
                        <input type="radio" name="fitness_level" value="beginner" required>
                        <div class="option-content">
                            <div class="option-text">
                                <span class="option-label">Beginner</span>
                                <span class="option-desc">I'm new or have only tried it for a bit</span>
                            </div>
                            <svg class="level-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                            </svg>
                        </div>
                    </label>
                    <label class="option-card">
                        <input type="radio" name="fitness_level" value="intermediate" required>
                        <div class="option-content">
                            <div class="option-text">
                                <span class="option-label">Intermediate</span>
                                <span class="option-desc">I've lifted weights before</span>
                            </div>
                            <svg class="level-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M2 12h20M12 2l10 10-10 10L2 12l10-10z"/>
                            </svg>
                        </div>
                    </label>
                    <label class="option-card">
                        <input type="radio" name="fitness_level" value="advanced" required>
                        <div class="option-content">
                            <div class="option-text">
                                <span class="option-label">Advanced</span>
                                <span class="option-desc">I've been lifting weights for a while</span>
                            </div>
                            <svg class="level-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                            </svg>
                        </div>
                    </label>
                </div>
                <div class="button-group">
                    <button type="button" class="btn btn-primary btn-lg" onclick="nextStep()">Continue</button>
                </div>
            </div>

            <!-- Step 4: Activity Level -->
            <div class="onboarding-step" data-step="4">
                <h2>What's your activity level?</h2>
                <div class="option-group">
                    <label class="option-card">
                        <input type="radio" name="activity_level" value="sedentary" required>
                        <div class="option-content">
                            <div class="option-text">
                                <span class="option-label">Sedentary</span>
                                <span class="option-desc">Little to no exercise</span>
                            </div>
                        </div>
                    </label>
                    <label class="option-card">
                        <input type="radio" name="activity_level" value="lightly_active" required>
                        <div class="option-content">
                            <div class="option-text">
                                <span class="option-label">Lightly Active</span>
                                <span class="option-desc">Exercise 1-3 days/week</span>
                            </div>
                        </div>
                    </label>
                    <label class="option-card">
                        <input type="radio" name="activity_level" value="moderately_active" required>
                        <div class="option-content">
                            <div class="option-text">
                                <span class="option-label">Moderately Active</span>
                                <span class="option-desc">Exercise 3-5 days/week</span>
                            </div>
                        </div>
                    </label>
                    <label class="option-card">
                        <input type="radio" name="activity_level" value="very_active" required>
                        <div class="option-content">
                            <div class="option-text">
                                <span class="option-label">Very Active</span>
                                <span class="option-desc">Exercise 6-7 days/week</span>
                            </div>
                        </div>
                    </label>
                </div>
                <div class="button-group">
                    <button type="button" class="btn btn-primary btn-lg" onclick="nextStep()">Continue</button>
                </div>
            </div>

            <!-- Step 5: Age -->
            <div class="onboarding-step" data-step="5">
                <h2>How old are you?</h2>
                <div class="form-group">
                    <select name="age" class="form-control" required>
                        <option value="">Select your age</option>
                        <?php for ($i = 13; $i <= 100; $i++): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?> years</option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="button-group">
                    <button type="button" class="btn btn-primary btn-lg" onclick="nextStep()">Continue</button>
                </div>
            </div>

            <!-- Step 6: Height & Weight -->
            <div class="onboarding-step" data-step="6">
                <h2>Tell us your measurements</h2>
                <div class="form-group">
                    <label class="form-label">Height</label>
                    <div class="input-group">
                        <input type="number" name="height" class="form-control" step="0.1" required>
                        <select name="height_unit" class="form-control" style="max-width: 100px;">
                            <option value="cm">cm</option>
                            <option value="ft">ft</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Weight</label>
                    <div class="input-group">
                        <input type="number" name="weight" class="form-control" step="0.1" required>
                        <select name="weight_unit" class="form-control" style="max-width: 100px;">
                            <option value="kg">kg</option>
                            <option value="lbs">lbs</option>
                        </select>
                    </div>
                </div>
                <div class="button-group">
                    <button type="button" class="btn btn-primary btn-lg" onclick="nextStep()">Continue</button>
                </div>
            </div>

            <!-- Step 7: Workout Frequency -->
            <div class="onboarding-step" data-step="7">
                <h2>How many days per week can you workout?</h2>
                <div class="form-group">
                    <input type="range" name="workout_frequency" min="1" max="7" value="3" class="range-slider" oninput="updateFrequency(this.value)">
                    <div class="range-value" id="frequencyValue">3 days per week</div>
                </div>
                <div class="button-group">
                    <button type="button" class="btn btn-primary btn-lg" onclick="nextStep()">Continue</button>
                </div>
            </div>

            <!-- Step 8: Workout Days -->
            <div class="onboarding-step" data-step="8">
                <h2>Which days do you prefer to workout?</h2>
                <div class="days-grid">
                    <label class="day-card">
                        <input type="checkbox" name="workout_days[]" value="Monday">
                        <span>Mon</span>
                    </label>
                    <label class="day-card">
                        <input type="checkbox" name="workout_days[]" value="Tuesday">
                        <span>Tue</span>
                    </label>
                    <label class="day-card">
                        <input type="checkbox" name="workout_days[]" value="Wednesday">
                        <span>Wed</span>
                    </label>
                    <label class="day-card">
                        <input type="checkbox" name="workout_days[]" value="Thursday">
                        <span>Thu</span>
                    </label>
                    <label class="day-card">
                        <input type="checkbox" name="workout_days[]" value="Friday">
                        <span>Fri</span>
                    </label>
                    <label class="day-card">
                        <input type="checkbox" name="workout_days[]" value="Saturday">
                        <span>Sat</span>
                    </label>
                    <label class="day-card">
                        <input type="checkbox" name="workout_days[]" value="Sunday">
                        <span>Sun</span>
                    </label>
                </div>
                <div class="button-group">
                    <button type="submit" class="btn btn-primary btn-lg">Complete Setup</button>
                </div>
            </div>
        </form>
    </div>

    <script src="../assets/js/main.js?v=<?php echo time(); ?>"></script>
    <script src="../assets/js/onboarding-clean.js?v=<?php echo time(); ?>"></script>
</body>
</html>