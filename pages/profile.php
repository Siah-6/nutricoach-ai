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
    <title>Profile - NutriCoach AI</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="container" style="padding: 3rem 0;">
        <h1 class="mb-4">👤 Your Profile</h1>

        <div class="row">
            <!-- Account Information -->
            <div class="col-12 col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h3>Account Information</h3>
                    </div>
                    <div class="card-body">
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
                            <button type="submit" class="btn btn-primary">Update Account</button>
                        </form>
                    </div>
                </div>

                <!-- Change Password -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3>Change Password</h3>
                    </div>
                    <div class="card-body">
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
                            <button type="submit" class="btn btn-primary">Change Password</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Fitness Profile -->
            <div class="col-12 col-md-6">
                <?php if ($profile): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h3>Fitness Profile</h3>
                    </div>
                    <div class="card-body">
                        <div class="profile-info">
                            <div class="info-item">
                                <strong>Gender:</strong> <?php echo ucfirst($profile['gender']); ?>
                            </div>
                            <div class="info-item">
                                <strong>Age:</strong> <?php echo $profile['age']; ?> years
                            </div>
                            <div class="info-item">
                                <strong>Height:</strong> <?php echo $profile['height'] . ' ' . $profile['height_unit']; ?>
                            </div>
                            <div class="info-item">
                                <strong>Weight:</strong> <?php echo $profile['weight'] . ' ' . $profile['weight_unit']; ?>
                            </div>
                            <div class="info-item">
                                <strong>BMI:</strong> <?php echo $profile['bmi']; ?>
                            </div>
                            <div class="info-item">
                                <strong>Fitness Goal:</strong> <?php echo ucwords(str_replace('_', ' ', $profile['fitness_goal'])); ?>
                            </div>
                            <div class="info-item">
                                <strong>Fitness Level:</strong> <?php echo ucfirst($profile['fitness_level']); ?>
                            </div>
                            <div class="info-item">
                                <strong>Activity Level:</strong> <?php echo ucwords(str_replace('_', ' ', $profile['activity_level'])); ?>
                            </div>
                            <div class="info-item">
                                <strong>Workout Frequency:</strong> <?php echo $profile['workout_frequency']; ?> days/week
                            </div>
                        </div>
                        <a href="onboarding.php" class="btn btn-outline mt-3">Update Fitness Profile</a>
                    </div>
                </div>

                <!-- Nutrition Goals -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3>Nutrition Goals</h3>
                    </div>
                    <div class="card-body">
                        <div class="profile-info">
                            <div class="info-item">
                                <strong>Daily Calories:</strong> <?php echo $profile['daily_calories']; ?> cal
                            </div>
                            <div class="info-item">
                                <strong>Protein:</strong> <?php echo $profile['protein_grams']; ?>g
                            </div>
                            <div class="info-item">
                                <strong>Carbohydrates:</strong> <?php echo $profile['carbs_grams']; ?>g
                            </div>
                            <div class="info-item">
                                <strong>Fats:</strong> <?php echo $profile['fats_grams']; ?>g
                            </div>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="card">
                    <div class="card-body text-center">
                        <p>Complete your onboarding to see your fitness profile.</p>
                        <a href="onboarding.php" class="btn btn-primary">Complete Onboarding</a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
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
    </style>
</body>
</html>
