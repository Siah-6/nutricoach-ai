/**
 * Authentication JavaScript
 */

// Modal Functions - Define globally on window object
window.openLoginModal = function() {
    document.getElementById('loginModal').classList.add('active');
}

window.closeLoginModal = function() {
    document.getElementById('loginModal').classList.remove('active');
}

window.openSignupModal = function() {
    document.getElementById('signupModal').classList.add('active');
}

window.closeSignupModal = function() {
    document.getElementById('signupModal').classList.remove('active');
}

window.openForgotPasswordModal = function() {
    document.getElementById('forgotPasswordModal').classList.add('active');
}

window.closeForgotPasswordModal = function() {
    document.getElementById('forgotPasswordModal').classList.remove('active');
}

// Create convenience references
const openLoginModal = window.openLoginModal;
const closeLoginModal = window.closeLoginModal;
const openSignupModal = window.openSignupModal;
const closeSignupModal = window.closeSignupModal;
const openForgotPasswordModal = window.openForgotPasswordModal;
const closeForgotPasswordModal = window.closeForgotPasswordModal;

// Close modals on outside click
window.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal')) {
        e.target.classList.remove('active');
    }
});

// Get NutriCoach utilities when DOM is ready
let Utils, Auth, FormValidator;

// Login Form Handler
document.addEventListener('DOMContentLoaded', () => {
    // Initialize NutriCoach utilities
    if (window.NutriCoach) {
        Utils = window.NutriCoach.Utils;
        Auth = window.NutriCoach.Auth;
        FormValidator = window.NutriCoach.FormValidator;
    }
    
    const loginForm = document.getElementById('loginForm');
    
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const validator = new FormValidator(loginForm);
            const isValid = validator.validate({
                email: { required: true, email: true },
                password: { required: true }
            });
            
            if (!isValid) return;
            
            const formData = new FormData(loginForm);
            const email = formData.get('email');
            const password = formData.get('password');
            
            const submitBtn = loginForm.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Logging in...';
            
            try {
                const response = await Auth.login(email, password);
                
                Utils.showAlert(response.message, 'success');
                
                // Redirect based on onboarding status
                setTimeout(() => {
                    if (response.onboarding_completed) {
                        window.location.href = '/pages/dashboard.php';
                    } else {
                        window.location.href = '/pages/onboarding.php';
                    }
                }, 1000);
                
            } catch (error) {
                Utils.showAlert(error.message, 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Login';
            }
        });
    }
});

// Signup Form Handler
document.addEventListener('DOMContentLoaded', () => {
    const signupForm = document.getElementById('signupForm');
    
    if (signupForm) {
        signupForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const validator = new FormValidator(signupForm);
            const isValid = validator.validate({
                name: { required: true, minLength: 2 },
                email: { required: true, email: true },
                password: { required: true, minLength: 8 },
                confirm_password: {
                    required: true,
                    match: 'password',
                    message: 'Passwords do not match'
                }
            });
            
            if (!isValid) return;
            
            const formData = new FormData(signupForm);
            const name = formData.get('name');
            const email = formData.get('email');
            const password = formData.get('password');
            const confirmPassword = formData.get('confirm_password');
            
            const submitBtn = signupForm.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Creating account...';
            
            try {
                const response = await Auth.signup(name, email, password, confirmPassword);
                
                Utils.showAlert(response.message, 'success');
                
                // Redirect to onboarding
                setTimeout(() => {
                    window.location.href = '/pages/onboarding.php';
                }, 1000);
                
            } catch (error) {
                Utils.showAlert(error.message, 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Sign Up';
            }
        });
    }
});

// Forgot Password Form Handler
document.addEventListener('DOMContentLoaded', () => {
    const forgotPasswordForm = document.getElementById('forgotPasswordForm');
    
    if (forgotPasswordForm) {
        forgotPasswordForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const validator = new FormValidator(forgotPasswordForm);
            const isValid = validator.validate({
                email: { required: true, email: true }
            });
            
            if (!isValid) return;
            
            const formData = new FormData(forgotPasswordForm);
            const email = formData.get('email');
            
            const submitBtn = forgotPasswordForm.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Sending...';
            
            try {
                const response = await Auth.forgotPassword(email);
                
                Utils.showAlert(response.message, 'success');
                
                // Close modal after success
                setTimeout(() => {
                    closeForgotPasswordModal();
                    forgotPasswordForm.reset();
                }, 2000);
                
            } catch (error) {
                Utils.showAlert(error.message, 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Send Reset Link';
            }
        });
    }
});

// Logout Function
async function logout() {
    if (!confirm('Are you sure you want to logout?')) return;
    
    try {
        await Auth.logout();
        Utils.showAlert('Logged out successfully', 'success');
        setTimeout(() => {
            window.location.href = '/';
        }, 1000);
    } catch (error) {
        Utils.showAlert('Logout failed', 'error');
    }
}

// Make logout function globally available
window.logout = logout;
