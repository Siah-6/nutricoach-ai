<?php
/**
 * User Profile Page
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

initSession();

if (!isLoggedIn()) {
    redirect('/');
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
    <title>Profile - NutriCoach AI</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dark-theme.css">
    <link rel="stylesheet" href="../assets/css/profile-dark.css">
</head>
<body class="dark-theme">
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="profile-container">
        <div class="profile-header">
            <h1>👤 Your Profile</h1>
            <p>Manage your account and fitness information</p>
        </div>

        <!-- Account Information -->
        <div class="profile-card">
            <h3>Account Information</h3>
                        <form id="accountForm">
                            <div class="form-group">
                                <label class="form-label">Name</label>
                                <input type="text" name="name" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            <button type="submit" class="btn-update">Update Account</button>
                        </form>
                        
                        <div class="logout-section">
                            <button onclick="logout()" class="logout-btn">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                    <polyline points="16 17 21 12 16 7"></polyline>
                                    <line x1="21" y1="12" x2="9" y2="12"></line>
                                </svg>
                                Logout
                            </button>
                        </div>
                    </div>

        <!-- Change Password -->
        <div class="profile-card">
            <h3>Change Password</h3>
            <form id="passwordForm">
                            <div class="form-group">
                                <label class="form-label">Current Password</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">New Password</label>
                                <input type="password" name="new_password" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn-update">Change Password</button>
                        </form>
        </div>

        <!-- Fitness Profile -->
        <?php if ($profile): ?>
        <div class="profile-card">
            <h3>Fitness Profile</h3>
            <div class="info-grid">
                <div class="info-item">
                    <strong>Gender</strong>
                    <span><?php echo ucfirst($profile['gender']); ?></span>
                </div>
                <div class="info-item">
                    <strong>Age</strong>
                    <span><?php echo $profile['age']; ?> years</span>
                </div>
                <div class="info-item">
                    <strong>Height</strong>
                    <span><?php echo $profile['height'] . ' ' . $profile['height_unit']; ?></span>
                </div>
                <div class="info-item">
                    <strong>Weight</strong>
                    <span><?php echo $profile['weight'] . ' ' . $profile['weight_unit']; ?></span>
                </div>
                <div class="info-item">
                    <strong>BMI</strong>
                    <span><?php echo $profile['bmi']; ?></span>
                </div>
                <div class="info-item">
                    <strong>Fitness Goal</strong>
                    <span><?php echo ucwords(str_replace('_', ' ', $profile['fitness_goal'])); ?></span>
                </div>
                <div class="info-item">
                    <strong>Fitness Level</strong>
                    <span><?php echo ucfirst($profile['fitness_level']); ?></span>
                </div>
                <div class="info-item">
                    <strong>Activity Level</strong>
                    <span><?php echo ucwords(str_replace('_', ' ', $profile['activity_level'])); ?></span>
                </div>
                <div class="info-item">
                    <strong>Workout Frequency</strong>
                    <span><?php echo $profile['workout_frequency']; ?> days/week</span>
                </div>
            </div>
            <a href="onboarding.php" class="btn-update" style="margin-top: 1.5rem; display: block; text-align: center; text-decoration: none;">Update Fitness Profile</a>
        </div>

        <!-- Nutrition Goals -->
        <div class="profile-card">
            <h3>Nutrition Goals</h3>
            <div class="stats-grid">
                <div class="stat-box">
                    <span class="stat-value"><?php echo $profile['daily_calories']; ?></span>
                    <span class="stat-label">Daily Calories</span>
                </div>
                <div class="stat-box">
                    <span class="stat-value"><?php echo $profile['protein_grams']; ?>g</span>
                    <span class="stat-label">Protein</span>
                </div>
                <div class="stat-box">
                    <span class="stat-value"><?php echo $profile['carbs_grams']; ?>g</span>
                    <span class="stat-label">Carbs</span>
                </div>
                <div class="stat-box">
                    <span class="stat-value"><?php echo $profile['fats_grams']; ?>g</span>
                    <span class="stat-label">Fats</span>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="profile-card" style="text-align: center;">
            <p style="margin-bottom: 1.5rem;">Complete your onboarding to see your fitness profile.</p>
            <a href="onboarding.php" class="btn-update" style="display: inline-block; text-decoration: none;">Complete Onboarding</a>
        </div>
        <?php endif; ?>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script src="../assets/js/main.js"></script>
    <script>
        const { Utils, User, FormValidator } = window.NutriCoach;

        // Account form
        document.getElementById('accountForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const validator = new FormValidator(e.target);
            const isValid = validator.validate({
                name: { required: true, minLength: 2 },
                email: { required: true, email: true }
            });

            if (!isValid) return;

            const formData = new FormData(e.target);
            const submitBtn = e.target.querySelector('button[type="submit"]');
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Updating...';

            try {
                await User.updateProfile({
                    name: formData.get('name'),
                    email: formData.get('email')
                });

                Utils.showAlert('Account updated successfully!', 'success');
            } catch (error) {
                Utils.showAlert(error.message, 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Update Account';
            }
        });

        // Password form
        document.getElementById('passwordForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const validator = new FormValidator(e.target);
            const isValid = validator.validate({
                current_password: { required: true },
                new_password: { required: true, minLength: 8 },
                confirm_password: {
                    required: true,
                    match: 'new_password',
                    message: 'Passwords do not match'
                }
            });

            if (!isValid) return;

            const formData = new FormData(e.target);
            const submitBtn = e.target.querySelector('button[type="submit"]');
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Changing...';

            try {
                await User.updateProfile({
                    current_password: formData.get('current_password'),
                    new_password: formData.get('new_password')
                });

                Utils.showAlert('Password changed successfully!', 'success');
                e.target.reset();
            } catch (error) {
                Utils.showAlert(error.message, 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Change Password';
            }
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
        .profile-info {
            display: grid;
            gap: 1rem;
        }

        .info-item {
            padding: 0.75rem;
            background-color: var(--bg-light);
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
        }

        .info-item strong {
            color: var(--text-dark);
        }
        
        .logout-btn {
            width: 100%;
            padding: 14px 24px;
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(238, 90, 111, 0.3);
        }
        
        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(238, 90, 111, 0.4);
        }
        
        .logout-btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(238, 90, 111, 0.3);
        }
    </style>
</body>
</html>
