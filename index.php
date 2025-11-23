<?php
/**
 * NutriCoach AI - Landing Page
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

initSession();

// Redirect to dashboard if already logged in and onboarding completed
if (isLoggedIn()) {
    if (isOnboardingCompleted(getCurrentUserId())) {
        redirect('/pages/dashboard.php');
    } else {
        redirect('/pages/onboarding.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="NutriCoach AI - Your Personal AI-Powered Fitness Coach">
    <title>NutriCoach AI - Transform Your Fitness Journey</title>
    <link rel="stylesheet" href="/xampp/NutriCoachAI/assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="navbar-content">
                <a href="/xampp/NutriCoachAI/" class="navbar-brand">🏋️ NutriCoach AI</a>
                <button class="navbar-toggle" aria-label="Toggle menu">☰</button>
                <ul class="navbar-menu">
                    <li><a href="#features">Features</a></li>
                    <li><a href="#how-it-works">How It Works</a></li>
                    <li><a href="/xampp/NutriCoachAI/pages/faq.php">FAQ</a></li>
                    <li><a href="/xampp/NutriCoachAI/pages/support.php">Support</a></li>
                    <li><a href="#" onclick="openLoginModal()">Login</a></li>
                    <li><a href="#" onclick="openSignupModal()" class="btn btn-primary">Get Started</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <img src="/xampp/NutriCoachAI/assets/images/gym-hero.jpg" alt="Fitness" class="hero-background" onerror="this.style.display='none'">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1 class="hero-title">Transform Your Body with AI-Powered Coaching</h1>
            <p class="hero-subtitle">Personalized workout plans, nutrition guidance, and 24/7 AI fitness coach at your fingertips</p>
            <button onclick="openSignupModal()" class="btn btn-primary btn-lg">Start Your Journey</button>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="p-5">
        <div class="container">
            <h2 class="text-center mb-5">Why Choose NutriCoach AI?</h2>
            <div class="row">
                <div class="col-12 col-md-4">
                    <div class="card text-center">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">🤖</div>
                        <h3>AI-Powered Coaching</h3>
                        <p>Get instant, personalized fitness advice from our advanced AI coach powered by Google Gemini</p>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card text-center">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">💪</div>
                        <h3>Custom Workout Plans</h3>
                        <p>Receive tailored workout routines based on your fitness level, goals, and preferences</p>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card text-center">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">🥗</div>
                        <h3>Nutrition Guidance</h3>
                        <p>Get personalized meal plans and macro tracking to fuel your fitness journey</p>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-12 col-md-4">
                    <div class="card text-center">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">📊</div>
                        <h3>Progress Tracking</h3>
                        <p>Monitor your progress with detailed charts and analytics to stay motivated</p>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card text-center">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">🎯</div>
                        <h3>Goal-Oriented</h3>
                        <p>Whether you want to build muscle, lose weight, or stay fit, we've got you covered</p>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card text-center">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">📱</div>
                        <h3>Mobile-Friendly</h3>
                        <p>Access your fitness coach anytime, anywhere on any device</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="p-5" style="background-color: var(--bg-white);">
        <div class="container">
            <h2 class="text-center mb-5">How It Works</h2>
            <div class="row">
                <div class="col-12 col-md-3">
                    <div class="card text-center">
                        <div style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 1rem;">1</div>
                        <h4>Sign Up</h4>
                        <p>Create your free account in seconds</p>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="card text-center">
                        <div style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 1rem;">2</div>
                        <h4>Complete Profile</h4>
                        <p>Tell us about your fitness goals and current level</p>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="card text-center">
                        <div style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 1rem;">3</div>
                        <h4>Get Your Plan</h4>
                        <p>Receive personalized workout and meal plans</p>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="card text-center">
                        <div style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 1rem;">4</div>
                        <h4>Start Training</h4>
                        <p>Begin your transformation with AI guidance</p>
                    </div>
                </div>
            </div>
            <div class="text-center mt-5">
                <button onclick="openSignupModal()" class="btn btn-primary btn-lg">Get Started Now</button>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="p-4" style="background-color: var(--text-dark); color: white; text-align: center;">
        <div class="container">
            <p>&copy; 2024 NutriCoach AI. All rights reserved.</p>
            <p>
                <a href="/xampp/NutriCoachAI/pages/faq.php" style="color: white; margin: 0 1rem;">FAQ</a>
                <a href="/xampp/NutriCoachAI/pages/support.php" style="color: white; margin: 0 1rem;">Support</a>
            </p>
        </div>
    </footer>

    <!-- Login Modal -->
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Login to Your Account</h3>
                <button class="modal-close" onclick="closeLoginModal()">&times;</button>
            </div>
            <form id="loginForm">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <a href="#" onclick="openForgotPasswordModal(); closeLoginModal();">Forgot Password?</a>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Login</button>
                <p class="text-center mt-3">
                    Don't have an account? <a href="#" onclick="openSignupModal(); closeLoginModal();">Sign Up</a>
                </p>
            </form>
        </div>
    </div>

    <!-- Signup Modal -->
    <div id="signupModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Create Your Account</h3>
                <button class="modal-close" onclick="closeSignupModal()">&times;</button>
            </div>
            <form id="signupForm">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Sign Up</button>
                <p class="text-center mt-3">
                    Already have an account? <a href="#" onclick="openLoginModal(); closeSignupModal();">Login</a>
                </p>
            </form>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div id="forgotPasswordModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Reset Password</h3>
                <button class="modal-close" onclick="closeForgotPasswordModal()">&times;</button>
            </div>
            <form id="forgotPasswordForm">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
                <p class="text-center mt-3">
                    <a href="#" onclick="openLoginModal(); closeForgotPasswordModal();">Back to Login</a>
                </p>
            </form>
        </div>
    </div>

    <script src="/xampp/NutriCoachAI/assets/js/main.js?v=<?php echo time(); ?>"></script>
    <script src="/xampp/NutriCoachAI/assets/js/auth-simple.js?v=<?php echo time(); ?>"></script>
</body>
</html>
