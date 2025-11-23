<?php
/**
 * Support Page
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

initSession();
$user = isLoggedIn() ? getCurrentUser() : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support - NutriCoach AI</title>
    <link rel="stylesheet" href="/xampp/NutriCoachAI/assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="container" style="padding: 3rem 0;">
        <h1 class="text-center mb-3">Contact Support</h1>
        <p class="text-center mb-5">Have a question or need help? We're here for you!</p>

        <div class="row">
            <div class="col-12 col-md-8" style="margin: 0 auto;">
                <div class="card">
                    <form id="supportForm">
                        <div class="form-group">
                            <label class="form-label">Name *</label>
                            <input type="text" name="name" class="form-control" 
                                   value="<?php echo $user ? htmlspecialchars($user['name']) : ''; ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control" 
                                   value="<?php echo $user ? htmlspecialchars($user['email']) : ''; ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Subject *</label>
                            <input type="text" name="subject" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Message *</label>
                            <textarea name="message" class="form-control" rows="6" required></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">Submit Ticket</button>
                    </form>
                </div>

                <div class="card mt-4">
                    <h3>Other Ways to Reach Us</h3>
                    <p><strong>Email:</strong> support@nutricoach.ai</p>
                    <p><strong>Response Time:</strong> We typically respond within 24-48 hours</p>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script src="/xampp/NutriCoachAI/assets/js/main.js?v=<?php echo time(); ?>"></script>
    <?php if (!isLoggedIn()): ?>
    <script>
        // Redirect to homepage for login/signup since we don't have modals here
        window.openLoginModal = function() {
            window.location.href = '/xampp/NutriCoachAI/?action=login';
        };
        window.openSignupModal = function() {
            window.location.href = '/xampp/NutriCoachAI/?action=signup';
        };
    </script>
    <?php endif; ?>
    <script>
        const Utils = window.NutriCoach ? window.NutriCoach.Utils : null;
        const Support = window.NutriCoach ? window.NutriCoach.Support : null;
        const FormValidator = window.NutriCoach ? window.NutriCoach.FormValidator : null;

        document.getElementById('supportForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const validator = new FormValidator(e.target);
            const isValid = validator.validate({
                name: { required: true, minLength: 2 },
                email: { required: true, email: true },
                subject: { required: true, minLength: 5 },
                message: { required: true, minLength: 10 }
            });

            if (!isValid) return;

            const formData = new FormData(e.target);
            const submitBtn = e.target.querySelector('button[type="submit"]');
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';

            try {
                await Support.submitTicket(
                    formData.get('name'),
                    formData.get('email'),
                    formData.get('subject'),
                    formData.get('message')
                );

                Utils.showAlert('Support ticket submitted successfully! We\'ll get back to you soon.', 'success');
                e.target.reset();
            } catch (error) {
                Utils.showAlert(error.message, 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit Ticket';
            }
        });

        <?php if (isLoggedIn()): ?>
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.NutriCoach.Auth.logout().then(() => {
                    window.location.href = '/';
                });
            }
        }
        <?php endif; ?>
    </script>
</body>
</html>
