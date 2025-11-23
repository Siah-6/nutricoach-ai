<?php
/**
 * FAQ Page
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

initSession();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - NutriCoach AI</title>
    <link rel="stylesheet" href="/xampp/NutriCoachAI/assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="container" style="padding: 3rem 0;">
        <h1 class="text-center mb-5">Frequently Asked Questions</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <h3>What is NutriCoach AI?</h3>
                    <p>NutriCoach AI is an AI-powered fitness coaching platform that provides personalized workout plans, nutrition guidance, and 24/7 access to an intelligent fitness coach powered by Google's Gemini AI.</p>
                </div>

                <div class="card">
                    <h3>How does the AI coach work?</h3>
                    <p>Our AI coach uses advanced machine learning to understand your fitness goals, current level, and preferences. It provides personalized advice, answers your questions, and helps you stay motivated throughout your fitness journey.</p>
                </div>

                <div class="card">
                    <h3>Is NutriCoach AI free to use?</h3>
                    <p>Yes! NutriCoach AI is completely free to use. Simply create an account and start your fitness journey today.</p>
                </div>

                <div class="card">
                    <h3>Can I change my fitness goals later?</h3>
                    <p>Absolutely! You can update your profile, fitness goals, and preferences anytime from your profile page. The AI will adapt to your new goals.</p>
                </div>

                <div class="card">
                    <h3>What fitness levels does NutriCoach AI support?</h3>
                    <p>NutriCoach AI supports all fitness levels - from complete beginners to advanced athletes. Our AI adapts workout plans and nutrition guidance based on your specific level.</p>
                </div>

                <div class="card">
                    <h3>How accurate are the calorie and macro calculations?</h3>
                    <p>Our calculations use scientifically proven formulas (Mifflin-St Jeor Equation for BMR) and are adjusted based on your activity level and fitness goals. However, individual results may vary.</p>
                </div>

                <div class="card">
                    <h3>Can I use NutriCoach AI on my mobile device?</h3>
                    <p>Yes! NutriCoach AI is fully responsive and works seamlessly on smartphones, tablets, and desktop computers.</p>
                </div>

                <div class="card">
                    <h3>Is my data secure?</h3>
                    <p>Yes, we take data security seriously. All passwords are encrypted, and we use secure connections to protect your personal information.</p>
                </div>

                <div class="card">
                    <h3>How do I reset my password?</h3>
                    <p>Click on "Forgot Password" on the login page, enter your email address, and you'll receive a password reset link.</p>
                </div>

                <div class="card">
                    <h3>Still have questions?</h3>
                    <p>Contact us through our <a href="/xampp/NutriCoachAI/pages/support.php">Support page</a> and we'll be happy to help!</p>
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
    <?php else: ?>
        <script>
            function logout() {
                if (confirm('Are you sure you want to logout?')) {
                    if (window.NutriCoach && window.NutriCoach.Auth) {
                        window.NutriCoach.Auth.logout().then(() => {
                            window.location.href = '/xampp/NutriCoachAI/';
                        });
                    }
                }
            }
        </script>
    <?php endif; ?>
</body>
</html>
