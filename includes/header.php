<?php
/**
 * Header Include
 */

if (!function_exists('isLoggedIn')) {
    require_once __DIR__ . '/functions.php';
}

initSession();
$isLoggedIn = isLoggedIn();
$currentUser = $isLoggedIn ? getCurrentUser() : null;
?>
<!-- Mobile Redesign CSS - Blue Theme -->
<link rel="stylesheet" href="/xampp/NutriCoachAI/assets/css/mobile-redesign.css">
<link rel="stylesheet" href="/xampp/NutriCoachAI/assets/css/splash.css">

<!-- Splash Screen - Only on Dashboard -->
<?php if ($isLoggedIn && basename($_SERVER['PHP_SELF']) == 'dashboard.php'): ?>
<div class="splash-screen">
    <img src="/xampp/NutriCoachAI/assets/images/NutriLogo.png" alt="NutriCoach" class="splash-logo">
    <div class="splash-text">NutriCoach AI</div>
    <div class="splash-tagline">Your Personal Fitness Coach</div>
    <div class="splash-loader"></div>
</div>
<script>
    // Only show splash on first visit
    if (!sessionStorage.getItem('splashShown')) {
        sessionStorage.setItem('splashShown', 'true');
        window.addEventListener('load', function() {
            setTimeout(function() {
                document.body.classList.add('loaded');
            }, 1500);
        });
    } else {
        document.body.classList.add('loaded');
    }
</script>
<?php endif; ?>

<nav class="navbar">
    <div class="container">
        <div class="navbar-content">
            <a href="<?php echo $isLoggedIn ? '/xampp/NutriCoachAI/pages/dashboard.php' : '/xampp/NutriCoachAI/'; ?>" class="navbar-brand">
                <img src="/xampp/NutriCoachAI/assets/images/NutriLogo.png" alt="NutriCoach" class="navbar-logo">
                <span>NutriCoach AI</span>
            </a>
            <button class="navbar-toggle" aria-label="Toggle menu">☰</button>
            <ul class="navbar-menu">
                <?php if ($isLoggedIn): ?>
                    <li><a href="/xampp/NutriCoachAI/pages/dashboard.php">Dashboard</a></li>
                    <li><a href="/xampp/NutriCoachAI/pages/workout-plan-improved.php">Workouts</a></li>
                    <li><a href="/xampp/NutriCoachAI/pages/meal-plan-new.php">Meals</a></li>
                    <li><a href="/xampp/NutriCoachAI/pages/progress.php">Progress</a></li>
                    <li><a href="/xampp/NutriCoachAI/pages/profile.php">Profile</a></li>
                    <li><a href="#" onclick="logout(); return false;">Logout</a></li>
                <?php else: ?>
                    <li><a href="/xampp/NutriCoachAI/">Home</a></li>
                    <li><a href="/xampp/NutriCoachAI/pages/faq.php">FAQ</a></li>
                    <li><a href="/xampp/NutriCoachAI/pages/support.php">Support</a></li>
                    <li><a href="#" onclick="openLoginModal(); return false;">Login</a></li>
                    <li><a href="#" onclick="openSignupModal(); return false;" class="btn btn-primary">Get Started</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<?php if ($isLoggedIn): ?>
<!-- Mobile Bottom Navigation -->
<nav class="mobile-bottom-nav">
    <a href="/xampp/NutriCoachAI/pages/dashboard.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
            <polyline points="9 22 9 12 15 12 15 22"></polyline>
        </svg>
        <span>Home</span>
    </a>
    <a href="/xampp/NutriCoachAI/pages/workout-plan-improved.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'workout-plan-improved.php' ? 'active' : ''; ?>">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M6.5 6.5h11v11h-11z"></path>
            <path d="M3 3v18h18"></path>
        </svg>
        <span>Workout</span>
    </a>
    <a href="/xampp/NutriCoachAI/pages/chat.php" class="nav-item nav-item-center <?php echo basename($_SERVER['PHP_SELF']) == 'chat.php' ? 'active' : ''; ?>">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
        </svg>
        <span>AI Chat</span>
    </a>
    <a href="/xampp/NutriCoachAI/pages/meal-plan-new.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'meal-plan-new.php' ? 'active' : ''; ?>">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"></path>
            <path d="M7 2v20"></path>
            <path d="M21 15V2v0a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"></path>
        </svg>
        <span>Meals</span>
    </a>
    <a href="/xampp/NutriCoachAI/pages/profile.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
        </svg>
        <span>Profile</span>
    </a>
</nav>
<?php endif; ?>
