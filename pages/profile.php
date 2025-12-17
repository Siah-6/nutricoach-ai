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
                    <label class="form-label">NAME</label>
                    <input type="text" name="name" class="form-control" 
                           value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">EMAIL</label>
                    <input type="email" name="email" class="form-control" 
                           value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">Update Account</button>
                    <a href="/logout" class="btn btn-danger">
                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                    </a>
                </div>
            </form>
        </div>

        <!-- Change Password -->
        <div class="profile-card">
            <h3>Change Password</h3>
            <form id="passwordForm">
                <div class="form-group">
                    <label class="form-label">CURRENT PASSWORD</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">NEW PASSWORD</label>
                    <input type="password" name="new_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">Change Password</button>
                </div>
            </form>
        </div>

        <!-- Fitness Profile -->
        <?php if ($profile): ?>
        <div class="profile-card">
            <h3>Fitness Profile</h3>
            <div class="info-grid">
                <div class="info-item">
                    <strong>Gender</strong>
                    <span><?php echo !empty($profile['gender']) ? htmlspecialchars(ucfirst($profile['gender'])) : 'Not set'; ?></span>
                </div>
                <div class="info-item">
                    <strong>Age</strong>
                    <span><?php echo !empty($profile['age']) ? htmlspecialchars($profile['age']) : 'Not set'; ?></span>
                </div>
                <div class="info-item">
                    <strong>Height</strong>
                    <span>
                        <?php 
                        if (!empty($profile['height'])) {
                            echo htmlspecialchars($profile['height']);
                            echo isset($profile['height_unit']) ? ' ' . htmlspecialchars($profile['height_unit']) : '';
                        } else {
                            echo 'Not set';
                        }
                        ?>
                    </span>
                </div>
                <div class="info-item">
                    <strong>Weight</strong>
                    <span><?php echo !empty($profile['weight']) ? htmlspecialchars($profile['weight'] . ' kg') : 'Not set'; ?></span>
                </div>
                <div class="info-item">
                    <strong>BMI</strong>
                    <span><?php echo !empty($profile['bmi']) ? number_format($profile['bmi'], 1) : 'Not calculated'; ?></span>
                </div>  
            </div>
            <form id="fitnessForm" class="profile-form">
                <div class="form-group">
                    <label class="form-label">Fitness Goal</label>
                    <select name="fitness_goal" class="form-control" required>
                        <?php
                            $goals = [
                                'build_muscle' => 'Build Muscle',
                                'lose_weight' => 'Lose Weight',
                                'look_better' => 'Look Better',
                                'stay_in_shape' => 'Stay in Shape'
                            ];
                            foreach ($goals as $value => $label):
                        ?>
                        <option value="<?php echo $value; ?>" <?php echo $profile['fitness_goal'] === $value ? 'selected' : ''; ?>><?php echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Fitness Level</label>
                    <select name="fitness_level" class="form-control" required>
                        <?php
                            $levels = [
                                'beginner' => 'Beginner',
                                'intermediate' => 'Intermediate',
                                'advanced' => 'Advanced'
                            ];
                            foreach ($levels as $value => $label):
                        ?>
                        <option value="<?php echo $value; ?>" <?php echo $profile['fitness_level'] === $value ? 'selected' : ''; ?>><?php echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Activity Level</label>
                    <select name="activity_level" class="form-control" required>
                        <?php
                            $activities = [
                                'sedentary' => 'Sedentary',
                                'lightly_active' => 'Lightly Active',
                                'moderately_active' => 'Moderately Active',
                                'very_active' => 'Very Active'
                            ];
                            foreach ($activities as $value => $label):
                        ?>
                        <option value="<?php echo $value; ?>" <?php echo $profile['activity_level'] === $value ? 'selected' : ''; ?>><?php echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-update">Update Fitness Profile</button>
                </div>
            </form>
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
        // Global Utils, User, and FormValidator are already available from main.js
        
        // Account form
        document.getElementById('accountForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const form = this;
            const formData = new FormData(form);
            const submitBtn = form.querySelector('button[type="submit"]');
            
            // Simple client-side validation
            const name = formData.get('name');
            const email = formData.get('email');
            
            if (!name || name.length < 2) {
                Utils.showAlert('Please enter a valid name (at least 2 characters)', 'error');
                return;
            }
            
            if (!email || !email.includes('@')) {
                Utils.showAlert('Please enter a valid email address', 'error');
                return;
            }
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Updating...';

            try {
                console.log('Sending request with:', { name, email });
                const response = await fetch('/api/user/profile.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        name: name,
                        email: email
                    })
                });
                
                console.log('Response status:', response.status);

                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.error || 'Failed to update profile');
                }

                Utils.showAlert('Profile updated successfully!', 'success');
                
                // Optional: Update the UI to reflect changes
                document.querySelector('input[name="name"]').value = name;
                document.querySelector('input[name="email"]').value = email;
                
            } catch (error) {
                console.error('Error:', error);
                Utils.showAlert(error.message || 'Failed to update profile', 'error');
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
        // Fitness form
        const fitnessForm = document.getElementById('fitnessForm');
        if (fitnessForm) {
            fitnessForm.addEventListener('submit', async (e) => {
                e.preventDefault();

                const validator = new FormValidator(e.target);
                const isValid = validator.validate({
                    fitness_goal: { required: true },
                    fitness_level: { required: true },
                    activity_level: { required: true },
                    height: { required: true, minLength: 1 }
                });

                if (!isValid) return;

                const formData = new FormData(e.target);
                const submitBtn = e.target.querySelector('button[type="submit"]');
                
                submitBtn.disabled = true;
                submitBtn.textContent = 'Updating...';

                try {
                    await User.updateProfile({
                        fitness_goal: formData.get('fitness_goal'),
                        fitness_level: formData.get('fitness_level'),
                        activity_level: formData.get('activity_level'),
                        height: formData.get('height'),
                        height_unit: formData.get('height_unit')
                    });

                    Utils.showAlert('Fitness profile updated!', 'success');
                } catch (error) {
                    Utils.showAlert(error.message, 'error');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Update Fitness Profile';
                }
            });
        }
    </script>

</body>
</html>
