/**
 * NutriCoach AI - Main JavaScript File
 */

// API Base URL - Dynamically determine based on current location
const API_BASE = (() => {
    const path = window.location.pathname;
    let base;
    
    // If we're in /pages/, go up one level; if we're at root, stay at root
    if (path.includes('/pages/')) {
        base = window.location.origin + path.substring(0, path.indexOf('/pages/'));
    } else if (path.includes('/assets/')) {
        base = window.location.origin + path.substring(0, path.indexOf('/assets/'));
    } else {
        // We're at the root (index.php)
        const dir = path.substring(0, path.lastIndexOf('/'));
        base = window.location.origin + (dir || '');
    }
    
    console.log('API_BASE initialized:', base);
    return base;
})();

// Utility Functions
const Utils = {
    // Make API request
    async request(url, options = {}) {
        try {
            const response = await fetch(url, {
                headers: {
                    'Content-Type': 'application/json',
                    ...options.headers
                },
                ...options
            });
            
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.error || 'Request failed');
            }
            
            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },
    
    // Show alert message
    showAlert(message, type = 'info') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.textContent = message;
        
        const container = document.querySelector('.container') || document.body;
        container.insertBefore(alertDiv, container.firstChild);
        
        setTimeout(() => {
            alertDiv.style.opacity = '0';
            setTimeout(() => alertDiv.remove(), 300);
        }, 5000);
    },
    
    // Show loading spinner
    showLoading(element) {
        const spinner = document.createElement('div');
        spinner.className = 'spinner';
        spinner.id = 'loading-spinner';
        element.appendChild(spinner);
    },
    
    // Hide loading spinner
    hideLoading() {
        const spinner = document.getElementById('loading-spinner');
        if (spinner) spinner.remove();
    },
    
    // Validate email
    isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    },
    
    // Format date
    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    },
    
    // Get query parameter
    getQueryParam(param) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(param);
    },
    
    // Store data in localStorage
    setStorage(key, value) {
        try {
            localStorage.setItem(key, JSON.stringify(value));
        } catch (error) {
            console.error('Storage error:', error);
        }
    },
    
    // Get data from localStorage
    getStorage(key) {
        try {
            const item = localStorage.getItem(key);
            return item ? JSON.parse(item) : null;
        } catch (error) {
            console.error('Storage error:', error);
            return null;
        }
    },
    
    // Remove data from localStorage
    removeStorage(key) {
        try {
            localStorage.removeItem(key);
        } catch (error) {
            console.error('Storage error:', error);
        }
    },
    
    // Debounce function
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
};

// Authentication Functions
const Auth = {
    // Sign up
    async signup(name, email, password, confirmPassword) {
        return await Utils.request(`${API_BASE}/api/auth/signup.php`, {
            method: 'POST',
            body: JSON.stringify({ name, email, password, confirm_password: confirmPassword })
        });
    },
    
    // Login
    async login(email, password) {
        return await Utils.request(`${API_BASE}/api/auth/login.php`, {
            method: 'POST',
            body: JSON.stringify({ email, password })
        });
    },
    
    // Logout
    async logout() {
        return await Utils.request(`${API_BASE}/api/auth/logout.php`, {
            method: 'POST'
        });
    },
    
    // Forgot password
    async forgotPassword(email) {
        return await Utils.request(`${API_BASE}/api/auth/forgot-password.php`, {
            method: 'POST',
            body: JSON.stringify({ email })
        });
    },
    
    // Reset password
    async resetPassword(token, password, confirmPassword) {
        return await Utils.request(`${API_BASE}/api/auth/reset-password.php`, {
            method: 'POST',
            body: JSON.stringify({ token, password, confirm_password: confirmPassword })
        });
    }
};

// User Functions
const User = {
    // Get profile
    async getProfile() {
        return await Utils.request(`${API_BASE}/api/user/profile.php`);
    },
    
    // Update profile
    async updateProfile(data) {
        // Use POST for broader hosting compatibility (some servers block PUT)
        return await Utils.request(`${API_BASE}/api/user/profile.php`, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    },
    
    // Submit onboarding
    async submitOnboarding(data) {
        return await Utils.request(`${API_BASE}/api/user/onboarding.php`, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }
};

// Chat Functions
const Chat = {
    // Send message
    async sendMessage(message) {
        return await Utils.request(`${API_BASE}/api/chat/message.php`, {
            method: 'POST',
            body: JSON.stringify({ message })
        });
    },
    
    // Get chat history
    async getHistory(limit = 20) {
        return await Utils.request(`${API_BASE}/api/chat/history.php?limit=${limit}`);
    }
};

// Fitness Functions
const Fitness = {
    // Get workout plan
    async getWorkoutPlan() {
        return await Utils.request(`${API_BASE}/api/fitness/workout-plan.php`);
    },
    
    // Get meal plan
    async getMealPlan(date = null) {
        const dateParam = date ? `?date=${date}` : '';
        return await Utils.request(`${API_BASE}/api/fitness/meal-plan.php${dateParam}`);
    },
    
    // Get progress
    async getProgress(days = 30) {
        return await Utils.request(`${API_BASE}/api/fitness/progress.php?days=${days}`);
    },
    
    // Log progress
    async logProgress(data) {
        return await Utils.request(`${API_BASE}/api/fitness/progress.php`, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }
};

// Support Functions
const Support = {
    // Submit ticket
    async submitTicket(name, email, subject, message) {
        return await Utils.request(`${API_BASE}/api/support/ticket.php`, {
            method: 'POST',
            body: JSON.stringify({ name, email, subject, message })
        });
    }
};

// Form Validation
class FormValidator {
    constructor(form) {
        this.form = form;
        this.errors = {};
    }
    
    validate(rules) {
        this.errors = {};
        
        for (const [field, fieldRules] of Object.entries(rules)) {
            const input = this.form.querySelector(`[name="${field}"]`);
            if (!input) continue;
            
            const value = input.value.trim();
            
            // Required validation
            if (fieldRules.required && !value) {
                this.errors[field] = fieldRules.message || `${field} is required`;
                continue;
            }
            
            // Email validation
            if (fieldRules.email && value && !Utils.isValidEmail(value)) {
                this.errors[field] = 'Please enter a valid email address';
                continue;
            }
            
            // Min length validation
            if (fieldRules.minLength && value.length < fieldRules.minLength) {
                this.errors[field] = `Minimum ${fieldRules.minLength} characters required`;
                continue;
            }
            
            // Max length validation
            if (fieldRules.maxLength && value.length > fieldRules.maxLength) {
                this.errors[field] = `Maximum ${fieldRules.maxLength} characters allowed`;
                continue;
            }
            
            // Match validation (for password confirmation)
            if (fieldRules.match) {
                const matchInput = this.form.querySelector(`[name="${fieldRules.match}"]`);
                if (matchInput && value !== matchInput.value) {
                    this.errors[field] = fieldRules.message || 'Fields do not match';
                }
            }
            
            // Custom validation
            if (fieldRules.custom && !fieldRules.custom(value)) {
                this.errors[field] = fieldRules.message || 'Invalid value';
            }
        }
        
        this.displayErrors();
        return Object.keys(this.errors).length === 0;
    }
    
    displayErrors() {
        // Clear previous errors
        this.form.querySelectorAll('.form-error').forEach(el => el.remove());
        this.form.querySelectorAll('.error').forEach(el => el.classList.remove('error'));
        
        // Display new errors
        for (const [field, message] of Object.entries(this.errors)) {
            const input = this.form.querySelector(`[name="${field}"]`);
            if (input) {
                input.classList.add('error');
                const errorDiv = document.createElement('span');
                errorDiv.className = 'form-error';
                errorDiv.textContent = message;
                input.parentElement.appendChild(errorDiv);
            }
        }
    }
    
    clearErrors() {
        this.errors = {};
        this.form.querySelectorAll('.form-error').forEach(el => el.remove());
        this.form.querySelectorAll('.error').forEach(el => el.classList.remove('error'));
    }
}

// Mobile Menu Toggle
document.addEventListener('DOMContentLoaded', () => {
    const navbarToggle = document.querySelector('.navbar-toggle');
    const navbarMenu = document.querySelector('.navbar-menu');
    
    if (navbarToggle && navbarMenu) {
        navbarToggle.addEventListener('click', () => {
            navbarMenu.classList.toggle('active');
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!navbarToggle.contains(e.target) && !navbarMenu.contains(e.target)) {
                navbarMenu.classList.remove('active');
            }
        });
    }
});

// Export for use in other files
window.NutriCoach = {
    Utils,
    Auth,
    User,
    Chat,
    Fitness,
    Support,
    FormValidator
};

// Global logout function
window.logout = async function() {
    if (!confirm('Are you sure you want to logout?')) {
        return;
    }
    
    try {
        await Auth.logout();
        Utils.showAlert('Logged out successfully', 'success');
        setTimeout(function() {
            // Redirect to root - determine if we're in pages/ or at root
            const path = window.location.pathname;
            if (path.includes('/pages/')) {
                window.location.href = '../index.php';
            } else {
                window.location.href = './';
            }
        }, 1000);
    } catch (error) {
        console.error('Logout error:', error);
        // Force logout even if API fails - redirect to logout endpoint
        window.location.href = API_BASE + '/api/auth/logout.php';
    }
};
