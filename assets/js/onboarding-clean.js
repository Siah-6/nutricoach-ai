/**
 * Onboarding JavaScript - Clean Version
 */

console.log('onboarding-clean.js loading...');

let currentStep = 1;
const totalSteps = 6;

// Update progress bar and back button
function updateProgress() {
    const progress = (currentStep / totalSteps) * 100;
    const progressBar = document.getElementById('progressBar');
    const currentStepEl = document.getElementById('currentStep');
    const backBtn = document.getElementById('backBtn');
    
    if (progressBar) progressBar.style.width = progress + '%';
    if (currentStepEl) currentStepEl.textContent = currentStep;
    
    // Show/hide back button
    if (backBtn) {
        backBtn.style.display = currentStep > 1 ? 'flex' : 'none';
    }
}

// Next step function - globally accessible
window.nextStep = function() {
    console.log('nextStep called, currentStep:', currentStep);
    
    const currentStepEl = document.querySelector(`.onboarding-step[data-step="${currentStep}"]`);
    console.log('currentStepEl:', currentStepEl);
    
    if (!currentStepEl) {
        console.error('Current step element not found!');
        return;
    }
    
    // Validate current step
    const inputs = currentStepEl.querySelectorAll('input[required], select[required]');
    console.log('Found inputs:', inputs.length);
    let isValid = true;
    
    inputs.forEach(function(input) {
        if (input.type === 'radio') {
            const radioGroup = currentStepEl.querySelectorAll(`input[name="${input.name}"]`);
            const isChecked = Array.from(radioGroup).some(function(radio) { return radio.checked; });
            if (!isChecked) {
                isValid = false;
                console.log('Radio not checked:', input.name);
                alert('Please select an option');
            }
        } else if (input.type === 'checkbox') {
            // Checkboxes are optional for workout days
        } else if (!input.value) {
            isValid = false;
            input.classList.add('error');
            console.log('Empty input:', input.name);
            alert('Please fill in all required fields');
        } else {
            input.classList.remove('error');
        }
    });
    
    if (!isValid) {
        console.log('Validation failed');
        return;
    }
    
    console.log('Validation passed, moving to next step');
    
    // Move to next step
    currentStepEl.classList.remove('active');
    currentStep++;
    
    if (currentStep <= totalSteps) {
        const nextStepEl = document.querySelector(`.onboarding-step[data-step="${currentStep}"]`);
        if (nextStepEl) {
            nextStepEl.classList.add('active');
            updateProgress();
            window.scrollTo({ top: 0, behavior: 'smooth' });
            console.log('Moved to step:', currentStep);
        } else {
            console.error('Next step element not found!');
        }
    }
};

// Previous step function
window.prevStep = function() {
    console.log('prevStep called');
    
    const currentStepEl = document.querySelector(`.onboarding-step[data-step="${currentStep}"]`);
    if (currentStepEl) {
        currentStepEl.classList.remove('active');
    }
    
    currentStep--;
    
    if (currentStep >= 1) {
        const prevStepEl = document.querySelector(`.onboarding-step[data-step="${currentStep}"]`);
        if (prevStepEl) {
            prevStepEl.classList.add('active');
            updateProgress();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }
};

// Go to dashboard
window.goToDashboard = function() {
    window.location.href = 'dashboard.php';
};

// Form submission
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing onboarding');
    
    // Get utilities safely
    const Utils = window.NutriCoach ? window.NutriCoach.Utils : null;
    const User = window.NutriCoach ? window.NutriCoach.User : null;
    
    if (!Utils || !User) {
        console.error('NutriCoach utilities not available');
    }
    
    const form = document.getElementById('onboardingForm');
    
    if (form) {
        console.log('Onboarding form found');
        
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            console.log('Form submitted');
            
            if (!User) {
                alert('System error: User utilities not loaded');
                return;
            }
            
            // Collect form data
            const formData = new FormData(form);
            const data = {};
            
            // Get single values
            data.gender = formData.get('gender');
            data.fitness_goal = formData.get('fitness_goal');
            data.fitness_level = formData.get('fitness_level');
            data.activity_level = formData.get('activity_level');
            data.age = parseInt(formData.get('age'));
            data.height = parseFloat(formData.get('height'));
            data.height_unit = formData.get('height_unit');
            data.weight = parseFloat(formData.get('weight'));
            data.weight_unit = formData.get('weight_unit');
            // Fallback defaults to satisfy older APIs
            data.workout_frequency = 3;
            data.workout_days = [];
            
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Processing...';
            
            try {
                const response = await User.submitOnboarding(data);
                console.log('Onboarding response:', response);
                
                if (Utils) {
                    Utils.showAlert('Profile completed successfully!', 'success');
                }
                
                // Show summary
                setTimeout(function() {
                    showSummary(response);
                }, 1000);
                
            } catch (error) {
                console.error('Onboarding error:', error);
                if (Utils) {
                    Utils.showAlert(error.message, 'error');
                } else {
                    alert('Error: ' + error.message);
                }
                submitBtn.disabled = false;
                submitBtn.textContent = 'Complete Setup';
            }
        });
    }
    
    // Initialize progress
    updateProgress();
    console.log('Onboarding initialized successfully');
});

// Show summary
function showSummary(data) {
    const summaryHTML = `
        <div style="text-align: center; padding: 2rem; max-width: 480px; margin: 0 auto;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">🎉</div>
            <h2 style="color: #4a9eff; margin-bottom: 1rem; font-size: 1.5rem; font-weight: 700;">Your Fitness Profile is Ready!</h2>
            <div style="background: rgba(255, 255, 255, 0.05); border: 2px solid rgba(255, 255, 255, 0.1); padding: 2rem; border-radius: 16px; margin-bottom: 2rem;">
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; text-align: left;">
                    <div style="color: white;"><strong style="color: rgba(255, 255, 255, 0.6);">BMI:</strong> ${data.bmi}</div>
                    <div style="color: white;"><strong style="color: rgba(255, 255, 255, 0.6);">BMR:</strong> ${data.bmr} cal</div>
                    <div style="color: white;"><strong style="color: rgba(255, 255, 255, 0.6);">Daily Calories:</strong> ${data.daily_calories} cal</div>
                    <div style="color: white;"><strong style="color: rgba(255, 255, 255, 0.6);">Protein:</strong> ${data.macros.protein}g</div>
                    <div style="color: white;"><strong style="color: rgba(255, 255, 255, 0.6);">Carbs:</strong> ${data.macros.carbs}g</div>
                    <div style="color: white;"><strong style="color: rgba(255, 255, 255, 0.6);">Fats:</strong> ${data.macros.fats}g</div>
                </div>
            </div>
            <p style="color: rgba(255, 255, 255, 0.7); margin-bottom: 2rem; font-size: 0.9375rem;">
                We've created a personalized fitness plan just for you!
            </p>
            <button onclick="goToDashboard()" style="width: 100%; padding: 1rem; border-radius: 16px; font-size: 1.0625rem; font-weight: 700; background: rgba(255, 255, 255, 0.9); color: #0A1628; border: none; cursor: pointer;">
                Go to Dashboard
            </button>
        </div>
    `;
    
    const container = document.querySelector('.onboarding-container');
    if (container) {
        container.innerHTML = summaryHTML;
    }
}

console.log('onboarding-clean.js loaded successfully');
console.log('nextStep function:', typeof window.nextStep);
console.log('prevStep function:', typeof window.prevStep);
