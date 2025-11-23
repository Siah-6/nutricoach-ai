/**
 * Dashboard JavaScript
 */

const { Utils, Chat, Fitness } = window.NutriCoach;

// Load dashboard data
document.addEventListener('DOMContentLoaded', async () => {
    await loadTodayWorkout();
    await loadTodayMeals();
    await loadChatHistory();
    setupChatForm();
});

// Load today's workout
async function loadTodayWorkout() {
    const container = document.getElementById('todayWorkout');
    
    try {
        const response = await Fitness.getWorkoutPlan();
        const plan = response.plan;
        
        if (!plan || !plan.exercises || plan.exercises.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">💪</div>
                    <p>No workout scheduled for today</p>
                </div>
            `;
            return;
        }
        
        // Get today's day name
        const today = new Date().toLocaleDateString('en-US', { weekday: 'long' });
        const todayWorkout = plan.exercises.find(ex => ex.day === today);
        
        if (!todayWorkout) {
            container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">😌</div>
                    <p>Rest day - Your muscles need recovery!</p>
                </div>
            `;
            return;
        }
        
        let html = `
            <div class="workout-item">
                <h4>${todayWorkout.focus}</h4>
                <p><strong>Exercises:</strong></p>
                <ul style="margin-left: 1.5rem;">
        `;
        
        todayWorkout.exercises.forEach(exercise => {
            html += `<li>${exercise.name} - ${exercise.sets} sets × ${exercise.reps} reps</li>`;
        });
        
        html += `</ul></div>`;
        container.innerHTML = html;
        
    } catch (error) {
        console.error('Error loading workout:', error);
        container.innerHTML = `
            <div class="empty-state">
                <p>Unable to load workout plan</p>
            </div>
        `;
    }
}

// Load today's meals
async function loadTodayMeals() {
    const container = document.getElementById('todayMeals');
    
    try {
        const response = await Fitness.getMealPlan();
        const meals = response.meals;
        const totals = response.totals;
        const targets = response.targets;
        
        if (!meals || meals.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">🥗</div>
                    <p>No meals planned for today</p>
                </div>
            `;
            return;
        }
        
        let html = '';
        
        meals.forEach(meal => {
            html += `
                <div class="meal-item">
                    <h4>${meal.meal_name}</h4>
                    <p><strong>${meal.meal_type.charAt(0).toUpperCase() + meal.meal_type.slice(1)}</strong></p>
                    <p>${meal.description}</p>
                    <p><strong>Macros:</strong> ${meal.calories} cal | ${meal.protein}g protein | ${meal.carbs}g carbs | ${meal.fats}g fat</p>
                </div>
            `;
        });
        
        html += `
            <div style="margin-top: 1rem; padding: 1rem; background-color: var(--bg-light); border-radius: 8px;">
                <p><strong>Daily Progress:</strong></p>
                <p>${totals.calories} / ${targets.calories} calories</p>
                <p>${totals.protein}g / ${targets.protein}g protein</p>
            </div>
        `;
        
        container.innerHTML = html;
        
    } catch (error) {
        console.error('Error loading meals:', error);
        container.innerHTML = `
            <div class="empty-state">
                <p>Unable to load meal plan</p>
            </div>
        `;
    }
}

// Load chat history
async function loadChatHistory() {
    const container = document.getElementById('chatMessages');
    
    try {
        const response = await Chat.getHistory(10);
        const history = response.history;
        
        if (!history || history.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <p>Start a conversation with your AI coach!</p>
                </div>
            `;
            return;
        }
        
        let html = '';
        
        history.forEach(chat => {
            html += `
                <div class="chat-message user">
                    <div class="chat-bubble">${chat.message}</div>
                </div>
                <div class="chat-message ai">
                    <div class="chat-bubble">${chat.response}</div>
                </div>
            `;
        });
        
        container.innerHTML = html;
        container.scrollTop = container.scrollHeight;
        
    } catch (error) {
        console.error('Error loading chat history:', error);
    }
}

// Setup chat form
function setupChatForm() {
    const form = document.getElementById('chatForm');
    const messagesContainer = document.getElementById('chatMessages');
    
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(form);
            const message = formData.get('message').trim();
            
            if (!message) return;
            
            // Add user message to chat
            const userMessageHTML = `
                <div class="chat-message user">
                    <div class="chat-bubble">${message}</div>
                </div>
            `;
            messagesContainer.insertAdjacentHTML('beforeend', userMessageHTML);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
            
            // Clear input
            form.reset();
            
            // Show typing indicator
            const typingHTML = `
                <div class="chat-message ai typing-indicator">
                    <div class="chat-bubble">
                        <div class="spinner spinner-sm"></div>
                        Thinking...
                    </div>
                </div>
            `;
            messagesContainer.insertAdjacentHTML('beforeend', typingHTML);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
            
            try {
                const response = await Chat.sendMessage(message);
                
                // Remove typing indicator
                messagesContainer.querySelector('.typing-indicator').remove();
                
                // Add AI response
                const aiMessageHTML = `
                    <div class="chat-message ai">
                        <div class="chat-bubble">${response.response}</div>
                    </div>
                `;
                messagesContainer.insertAdjacentHTML('beforeend', aiMessageHTML);
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
                
            } catch (error) {
                messagesContainer.querySelector('.typing-indicator').remove();
                Utils.showAlert('Failed to get response from AI coach', 'error');
            }
        });
    }
}
