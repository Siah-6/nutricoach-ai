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
    <meta name="theme-color" content="#0A1628">
    <title>NutriCoach AI - Transform Your Fitness Journey</title>
    
    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/dark-theme.css">
    <link rel="stylesheet" href="assets/css/landing-dark.css">
    <link rel="stylesheet" href="assets/css/modal-dark.css">
</head>
<body class="landing-page dark-theme">
    <!-- Hero Section -->
    <section class="hero-dark">
        <div class="hero-gradient"></div>
        <div class="container">
            <div class="hero-content">
                <div class="app-logo">
                    <img src="assets/images/NutriLogo.png" alt="NutriCoach AI">
                </div>
                <h1 class="hero-title">Transform Your Body<br>with AI-Powered Coaching</h1>
                <p class="hero-subtitle">Your personal fitness coach, workout plans, and nutrition guidance—all in your pocket</p>
                <button onclick="openSignupModal()" class="btn-primary btn-hero">Start Your Journey</button>
                <p class="hero-note">Join thousands achieving their fitness goals</p>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features-mobile">
        <h2>Why Choose NutriCoach AI?</h2>
        <p class="subtitle">Everything you need for your fitness journey</p>
        
        <div class="feature-grid">
            <div class="feature-card">
                <div class="icon">🤖</div>
                <h3>AI-Powered Coaching</h3>
                <p>Get instant, personalized fitness advice from our advanced AI coach powered by Google Gemini</p>
            </div>
            
            <div class="feature-card">
                <div class="icon">💪</div>
                <h3>Custom Workout Plans</h3>
                <p>Receive tailored workout routines based on your fitness level, goals, and preferences</p>
            </div>
            
            <div class="feature-card">
                <div class="icon">🥗</div>
                <h3>Nutrition Guidance</h3>
                <p>Get personalized meal plans and macro tracking to fuel your fitness journey</p>
            </div>
            
            <div class="feature-card">
                <div class="icon">📊</div>
                <h3>Progress Tracking</h3>
                <p>Monitor your progress with detailed charts and analytics to stay motivated</p>
            </div>
            
            <div class="feature-card">
                <div class="icon">🎯</div>
                <h3>Goal-Oriented</h3>
                <p>Whether you want to build muscle, lose weight, or stay fit, we've got you covered</p>
            </div>
            
            <div class="feature-card">
                <div class="icon">🏆</div>
                <h3>Gamification & XP</h3>
                <p>Earn XP, level up, and unlock achievements as you complete workouts and reach milestones</p>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="how-it-works-mobile">
        <h2>How It Works</h2>
        
        <div class="timeline">
            <div class="timeline-item" data-step="1">
                <h3>Sign Up</h3>
                <p>Create your free account in seconds and join thousands of users transforming their bodies</p>
            </div>
            
            <div class="timeline-item" data-step="2">
                <h3>Complete Profile</h3>
                <p>Tell us about your fitness goals, current level, and preferences for a personalized experience</p>
            </div>
            
            <div class="timeline-item" data-step="3">
                <h3>Get Your Plan</h3>
                <p>Receive AI-generated workout and meal plans tailored specifically to your goals</p>
            </div>
            
            <div class="timeline-item" data-step="4">
                <h3>Start Training</h3>
                <p>Begin your transformation with 24/7 AI guidance, track progress, and earn rewards</p>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <h2>Ready to Transform?</h2>
        <p>Join thousands of users achieving their fitness goals with AI-powered coaching</p>
        <button onclick="openSignupModal()" class="cta-btn">Get Started Now</button>
    </section>

    <!-- Footer -->
    <footer class="footer-mobile">
        <p>&copy; 2024 NutriCoach AI. All rights reserved.</p>
        <p>
            <a href="pages/faq.php">FAQ</a>
            <a href="pages/support.php">Support</a>
        </p>
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

    <script src="assets/js/main.js?v=<?php echo time(); ?>"></script>
    <script src="assets/js/auth-simple.js?v=<?php echo time(); ?>"></script>
</body>
</html>
