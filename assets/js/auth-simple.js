/**
 * Authentication JavaScript - Simplified Version
 */

// Define modal functions directly on window - FIRST THING
window.openLoginModal = function() {
    console.log('openLoginModal called');
    const modal = document.getElementById('loginModal');
    if (modal) {
        modal.classList.add('active');
    } else {
        console.error('loginModal element not found');
    }
};

window.closeLoginModal = function() {
    const modal = document.getElementById('loginModal');
    if (modal) {
        modal.classList.remove('active');
    }
};

window.openSignupModal = function() {
    console.log('openSignupModal called');
    const modal = document.getElementById('signupModal');
    if (modal) {
        modal.classList.add('active');
    } else {
        console.error('signupModal element not found');
    }
};

window.closeSignupModal = function() {
    const modal = document.getElementById('signupModal');
    if (modal) {
        modal.classList.remove('active');
    }
};

window.openForgotPasswordModal = function() {
    const modal = document.getElementById('forgotPasswordModal');
    if (modal) {
        modal.classList.add('active');
    }
};

window.closeForgotPasswordModal = function() {
    const modal = document.getElementById('forgotPasswordModal');
    if (modal) {
        modal.classList.remove('active');
    }
};

console.log('Modal functions defined:', {
    openLoginModal: typeof window.openLoginModal,
    openSignupModal: typeof window.openSignupModal
});

// Close modals on outside click
window.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        e.target.classList.remove('active');
    }
});

// Wait for DOM and NutriCoach to be ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, setting up forms');
    
    // Get utilities
    const Utils = window.NutriCoach ? window.NutriCoach.Utils : null;
    const Auth = window.NutriCoach ? window.NutriCoach.Auth : null;
    const FormValidator = window.NutriCoach ? window.NutriCoach.FormValidator : null;
    
    if (!Utils || !Auth || !FormValidator) {
        console.error('NutriCoach utilities not available');
        return;
    }
    
    // Login Form
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const validator = new FormValidator(loginForm);
            const isValid = validator.validate({
                email: { required: true, email: true },
                password: { required: true }
            });
            
            if (!isValid) return;
            
            const formData = new FormData(loginForm);
            const submitBtn = loginForm.querySelector('button[type="submit"]');
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Logging in...';
            
            try {
                const response = await Auth.login(
                    formData.get('email'),
                    formData.get('password')
                );
                
                Utils.showAlert(response.message, 'success');
                
                setTimeout(function() {
                    if (response.data.onboarding_completed) {
                        window.location.href = '/xampp/NutriCoachAI/pages/dashboard.php';
                    } else {
                        window.location.href = '/xampp/NutriCoachAI/pages/onboarding.php';
                    }
                }, 1000);
                
            } catch (error) {
                Utils.showAlert(error.message, 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Login';
            }
        });
    }
    
    // Signup Form
    const signupForm = document.getElementById('signupForm');
    if (signupForm) {
        signupForm.addEventListener('submit', async function(e) {
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
            const submitBtn = signupForm.querySelector('button[type="submit"]');
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Creating account...';
            
            try {
                const response = await Auth.signup(
                    formData.get('name'),
                    formData.get('email'),
                    formData.get('password'),
                    formData.get('confirm_password')
                );
                
                Utils.showAlert(response.message, 'success');
                
                setTimeout(function() {
                    window.location.href = '/xampp/NutriCoachAI/pages/onboarding.php';
                }, 1000);
                
            } catch (error) {
                Utils.showAlert(error.message, 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Sign Up';
            }
        });
    }
    
    // Forgot Password Form
    const forgotPasswordForm = document.getElementById('forgotPasswordForm');
    if (forgotPasswordForm) {
        forgotPasswordForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const validator = new FormValidator(forgotPasswordForm);
            const isValid = validator.validate({
                email: { required: true, email: true }
            });
            
            if (!isValid) return;
            
            const formData = new FormData(forgotPasswordForm);
            const submitBtn = forgotPasswordForm.querySelector('button[type="submit"]');
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Sending...';
            
            try {
                const response = await Auth.forgotPassword(formData.get('email'));
                Utils.showAlert(response.message, 'success');
                
                setTimeout(function() {
                    window.closeForgotPasswordModal();
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

// Logout function
window.logout = async function() {
    if (!confirm('Are you sure you want to logout?')) return;
    
    try {
        if (window.NutriCoach && window.NutriCoach.Auth) {
            await window.NutriCoach.Auth.logout();
            window.NutriCoach.Utils.showAlert('Logged out successfully', 'success');
        }
        setTimeout(function() {
            window.location.href = '/xampp/NutriCoachAI/';
        }, 1000);
    } catch (error) {
        alert('Logout failed');
    }
};

console.log('auth-simple.js loaded successfully');
