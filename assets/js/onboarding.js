/**
 * Onboarding JavaScript
 */

// Get utilities when needed
let Utils, User;

let currentStep = 1;
const totalSteps = 6;

// Update progress bar
function updateProgress() {
    const progress = (currentStep / totalSteps) * 100;
    document.getElementById('progressBar').style.width = progress + '%';
    document.getElementById('currentStep').textContent = currentStep;
}

// Make functions globally accessible
window.nextStep = function nextStep() {
    console.log('nextStep called, currentStep:', currentStep);
    const currentStepEl = document.querySelector(`.onboarding-step[data-step="${currentStep}"]`);
    console.log('currentStepEl:', currentStepEl);
    
    // Validate current step
    const inputs = currentStepEl.querySelectorAll('input[required], select[required]');
    console.log('Found inputs:', inputs.length);
    let isValid = true;
    
    inputs.forEach(input => {
        if (input.type === 'radio') {
            const radioGroup = currentStepEl.querySelectorAll(`input[name="${input.name}"]`);
            const isChecked = Array.from(radioGroup).some(radio => radio.checked);
            if (!isChecked) {
                isValid = false;
                if (Utils && Utils.showAlert) {
                    Utils.showAlert('Please select an option', 'warning');
                } else {
                    alert('Please select an option');
                }
            }
        } else if (input.type === 'checkbox') {
            // Checkboxes are optional for workout days
        } else if (!input.value) {
            isValid = false;
            input.classList.add('error');
            if (Utils && Utils.showAlert) {
                Utils.showAlert('Please fill in all required fields', 'warning');
            } else {
                alert('Please fill in all required fields');
            }
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
        document.querySelector(`.onboarding-step[data-step="${currentStep}"]`).classList.add('active');
        updateProgress();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

// Previous step
window.prevStep = function prevStep() {
    const currentStepEl = document.querySelector(`.onboarding-step[data-step="${currentStep}"]`);
    currentStepEl.classList.remove('active');
    
    currentStep--;
    
    if (currentStep >= 1) {
        document.querySelector(`.onboarding-step[data-step="${currentStep}"]`).classList.add('active');
        updateProgress();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

// Update frequency display
window.updateFrequency = function updateFrequency(value) {
    document.getElementById('frequencyValue').textContent = `${value} day${value > 1 ? 's' : ''} per week`;
}

// Form submission
document.addEventListener('DOMContentLoaded', () => {
    // Initialize utilities
    if (window.NutriCoach) {
        Utils = window.NutriCoach.Utils;
        User = window.NutriCoach.User;
    } else {
        console.error('NutriCoach not available');
    }
    
    const form = document.getElementById('onboardingForm');
    
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
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
            // Provide safe defaults for legacy fields that may be missing
            data.workout_frequency = 3;
            data.workout_days = [];
            
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Processing...';
            
            try {
                const response = await User.submitOnboarding(data);
                
                Utils.showAlert('Profile completed successfully!', 'success');
                
                // Show summary modal or redirect
                setTimeout(() => {
                    showSummary(response);
                }, 1000);
                
            } catch (error) {
                Utils.showAlert(error.message, 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Complete Setup';
            }
        });
    }
    
    // Initialize progress
    updateProgress();
});

// Show summary and redirect to dashboard
function showSummary(data) {
    const summaryHTML = `
        <div style="text-align: center; padding: 2rem;">
            <h2 style="color: var(--primary-color); margin-bottom: 1.5rem;">🎉 Your Fitness Profile is Ready!</h2>
            <div style="background-color: var(--bg-light); padding: 2rem; border-radius: 12px; margin-bottom: 2rem;">
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; text-align: left;">
                    <div>
                        <strong>BMI:</strong> ${data.bmi}
                    </div>
                    <div>
                        <strong>BMR:</strong> ${data.bmr} cal
                    </div>
                    <div>
                        <strong>Daily Calories:</strong> ${data.daily_calories} cal
                    </div>
                    <div>
                        <strong>Protein:</strong> ${data.macros.protein}g
                    </div>
                    <div>
                        <strong>Carbs:</strong> ${data.macros.carbs}g
                    </div>
                    <div>
                        <strong>Fats:</strong> ${data.macros.fats}g
                    </div>
                </div>
            </div>
            <p style="color: var(--text-light); margin-bottom: 2rem;">
                We've created a personalized fitness plan just for you!
            </p>
            <button onclick="goToDashboard()" class="btn btn-primary btn-lg">
                Go to Dashboard
            </button>
        </div>
    `;
    
    document.querySelector('.onboarding-container').innerHTML = summaryHTML;
}

// Redirect to dashboard
window.goToDashboard = function goToDashboard() {
    window.location.href = 'dashboard.php';
}
