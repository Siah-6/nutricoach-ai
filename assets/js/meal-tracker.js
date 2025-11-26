/**
 * Meal Tracker JavaScript
 */

// User targets - will be loaded from page
let dailyCalories = 2000;
let dailyCarbs = 250;
let dailyProtein = 150;
let dailyFats = 65;

// Load targets from page
function loadTargets() {
    const caloriesText = document.getElementById('caloriesLeft').textContent;
    dailyCalories = parseInt(caloriesText) || 2000;
    
    // Extract targets from macro values text
    const carbsTarget = document.querySelector('#carbsValue').parentElement.textContent.match(/\/ (\d+) g/);
    const proteinTarget = document.querySelector('#proteinValue').parentElement.textContent.match(/\/ (\d+) g/);
    const fatsTarget = document.querySelector('#fatsValue').parentElement.textContent.match(/\/ (\d+) g/);
    
    if (carbsTarget) dailyCarbs = parseInt(carbsTarget[1]);
    if (proteinTarget) dailyProtein = parseInt(proteinTarget[1]);
    if (fatsTarget) dailyFats = parseInt(fatsTarget[1]);
}

// Current meal being logged
let currentMeal = '';
let selectedFoods = [];

// Food database
const foodDatabase = {
    vegetables: [
        { name: 'Broccoli', calories: 55, carbs: 11, protein: 4, fats: 0.5, serving: '1 cup' },
        { name: 'Spinach', calories: 23, carbs: 4, protein: 3, fats: 0.4, serving: '1 cup' },
        { name: 'Carrots', calories: 52, carbs: 12, protein: 1, fats: 0.3, serving: '1 cup' },
        { name: 'Bell Peppers', calories: 30, carbs: 7, protein: 1, fats: 0.2, serving: '1 cup' },
        { name: 'Tomatoes', calories: 32, carbs: 7, protein: 2, fats: 0.4, serving: '1 cup' },
    ],
    fruits: [
        { name: 'Banana', calories: 105, carbs: 27, protein: 1, fats: 0.4, serving: '1 medium' },
        { name: 'Apple', calories: 95, carbs: 25, protein: 0.5, fats: 0.3, serving: '1 medium' },
        { name: 'Orange', calories: 62, carbs: 15, protein: 1, fats: 0.2, serving: '1 medium' },
        { name: 'Strawberries', calories: 49, carbs: 12, protein: 1, fats: 0.5, serving: '1 cup' },
        { name: 'Grapes', calories: 104, carbs: 27, protein: 1, fats: 0.2, serving: '1 cup' },
    ],
    protein: [
        { name: 'Chicken Breast', calories: 165, carbs: 0, protein: 31, fats: 3.6, serving: '100g' },
        { name: 'Salmon', calories: 206, carbs: 0, protein: 22, fats: 13, serving: '100g' },
        { name: 'Eggs', calories: 155, carbs: 1, protein: 13, fats: 11, serving: '2 large' },
        { name: 'Tuna', calories: 132, carbs: 0, protein: 28, fats: 1, serving: '100g' },
        { name: 'Beef', calories: 250, carbs: 0, protein: 26, fats: 17, serving: '100g' },
        { name: 'Tofu', calories: 76, carbs: 2, protein: 8, fats: 4.8, serving: '100g' },
    ],
    grains: [
        { name: 'Brown Rice', calories: 216, carbs: 45, protein: 5, fats: 1.8, serving: '1 cup' },
        { name: 'Oatmeal', calories: 150, carbs: 27, protein: 5, fats: 3, serving: '1 cup' },
        { name: 'Whole Wheat Bread', calories: 80, carbs: 14, protein: 4, fats: 1, serving: '1 slice' },
        { name: 'Quinoa', calories: 222, carbs: 39, protein: 8, fats: 3.6, serving: '1 cup' },
        { name: 'Pasta', calories: 200, carbs: 42, protein: 7, fats: 1.2, serving: '1 cup' },
    ],
    dairy: [
        { name: 'Greek Yogurt', calories: 100, carbs: 6, protein: 17, fats: 0.7, serving: '1 cup' },
        { name: 'Milk', calories: 149, carbs: 12, protein: 8, fats: 8, serving: '1 cup' },
        { name: 'Cheese', calories: 113, carbs: 1, protein: 7, fats: 9, serving: '1 oz' },
        { name: 'Cottage Cheese', calories: 163, carbs: 6, protein: 28, fats: 2.3, serving: '1 cup' },
    ]
};

// Current selected date
let selectedDate = new Date();
let currentViewDate = new Date();

// Load today's meals on page load
document.addEventListener('DOMContentLoaded', () => {
    loadTargets();
    generateWeekCalendar();
    loadMealsForDate(formatDate(selectedDate));
    setupCategoryButtons();
});

function formatDate(date) {
    return date.toISOString().split('T')[0];
}

function generateWeekCalendar() {
    const weekDates = document.getElementById('weekDates');
    const currentMonth = document.getElementById('currentMonth');
    
    // Update month display based on selected date
    currentMonth.textContent = selectedDate.toLocaleDateString('en-US', { 
        month: 'long', 
        day: 'numeric', 
        year: 'numeric' 
    });
    
    // Always show the week that contains the selected date
    const startOfWeek = new Date(selectedDate);
    const day = startOfWeek.getDay();
    const diff = startOfWeek.getDate() - day + (day === 0 ? -6 : 1); // Adjust to Monday
    startOfWeek.setDate(diff);
    
    weekDates.innerHTML = '';
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    for (let i = 0; i < 7; i++) {
        const date = new Date(startOfWeek);
        date.setDate(startOfWeek.getDate() + i);
        
        const dateItem = document.createElement('div');
        dateItem.className = 'date-item';
        dateItem.textContent = date.getDate();
        
        // Check if it's today
        if (formatDate(date) === formatDate(today)) {
            dateItem.classList.add('today');
        }
        
        // Check if it's selected
        if (formatDate(date) === formatDate(selectedDate)) {
            dateItem.classList.add('selected');
        }
        
        // Disable future dates
        if (date > today) {
            dateItem.classList.add('disabled');
        } else {
            dateItem.onclick = () => selectDate(date);
        }
        
        weekDates.appendChild(dateItem);
    }
}

function selectDate(date) {
    selectedDate = new Date(date);
    // Don't change currentViewDate - keep the same week visible
    generateWeekCalendar();
    loadMealsForDate(formatDate(selectedDate));
}

function changeMonth(direction) {
    selectedDate.setDate(selectedDate.getDate() + (direction * 7));
    generateWeekCalendar();
    loadMealsForDate(formatDate(selectedDate));
}

function goToToday() {
    selectedDate = new Date();
    currentViewDate = new Date();
    generateWeekCalendar();
    loadMealsForDate(formatDate(selectedDate));
}

async function loadMealsForDate(date) {
    try {
        const response = await fetch(`../api/meal/get-today.php?date=${date}`);
        const data = await response.json();
        
        if (data.success) {
            updateMealSummary(data.meals);
        } else {
            console.error('Failed to load meals:', data.message);
            updateMealSummary([]);
        }
    } catch (error) {
        console.error('Error loading meals:', error);
        updateMealSummary([]);
    }
}

function updateMealSummary(meals) {
    let totalCalories = 0;
    let totalCarbs = 0;
    let totalProtein = 0;
    let totalFats = 0;
    
    // Calculate totals
    meals.forEach(meal => {
        totalCalories += parseInt(meal.calories) || 0;
        totalCarbs += parseInt(meal.carbs) || 0;
        totalProtein += parseInt(meal.protein) || 0;
        totalFats += parseInt(meal.fats) || 0;
        
        // Update meal card
        const mealCard = document.getElementById(`${meal.meal_type}Calories`);
        if (mealCard) {
            mealCard.textContent = `${meal.calories || 0} Kcal`;
        }
    });
    
    // Update calorie circle
    const caloriesLeft = dailyCalories - totalCalories;
    document.getElementById('caloriesLeft').textContent = Math.max(0, caloriesLeft);
    document.getElementById('caloriesEaten').textContent = totalCalories;
    
    // Update progress circle
    const progress = Math.min((totalCalories / dailyCalories) * 100, 100);
    const circumference = 2 * Math.PI * 90;
    const offset = circumference - (progress / 100) * circumference;
    document.getElementById('calorieProgress').style.strokeDashoffset = offset;
    
    // Update macros
    updateMacro('carbs', totalCarbs, dailyCarbs);
    updateMacro('protein', totalProtein, dailyProtein);
    updateMacro('fats', totalFats, dailyFats);
}

function updateMacro(type, current, target) {
    // Ensure values are numbers
    current = parseFloat(current) || 0;
    target = parseFloat(target) || 1; // Avoid division by zero
    
    const percent = Math.min((current / target) * 100, 100);
    const percentRounded = Math.round(percent);
    const currentRounded = Math.round(current);
    
    document.getElementById(`${type}Percent`).textContent = `${percentRounded}%`;
    document.getElementById(`${type}Bar`).style.width = `${percent}%`;
    document.getElementById(`${type}Value`).textContent = currentRounded;
}

function openMealLogger(mealType) {
    currentMeal = mealType;
    selectedFoods = [];
    
    // Update modal title
    const mealNames = {
        'breakfast': 'Breakfast',
        'morning-snack': 'Morning Snack',
        'lunch': 'Lunch',
        'afternoon-snack': 'Afternoon Snack',
        'dinner': 'Dinner'
    };
    document.getElementById('modalMealName').textContent = mealNames[mealType];
    
    // Show modal
    document.getElementById('mealModal').style.display = 'flex';
    
    // Load all foods
    displayFoods('all');
}

function closeMealLogger() {
    document.getElementById('mealModal').style.display = 'none';
    selectedFoods = [];
}

function setupCategoryButtons() {
    const buttons = document.querySelectorAll('.category-btn');
    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            buttons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const category = btn.dataset.category;
            displayFoods(category);
        });
    });
}

function displayFoods(category) {
    const foodList = document.getElementById('foodList');
    foodList.innerHTML = '';
    
    let foods = [];
    if (category === 'all') {
        Object.values(foodDatabase).forEach(cat => foods.push(...cat));
    } else {
        foods = foodDatabase[category] || [];
    }
    
    foods.forEach((food, index) => {
        const isSelected = selectedFoods.some(f => f.name === food.name);
        const foodItem = document.createElement('div');
        foodItem.className = `food-item ${isSelected ? 'selected' : ''}`;
        foodItem.innerHTML = `
            <div class="food-info">
                <div class="food-name">${food.name}</div>
                <div class="food-macros">${food.serving} | ${food.calories} kcal | C:${food.carbs}g P:${food.protein}g F:${food.fats}g</div>
            </div>
            <button class="food-add-btn" onclick="toggleFood(${index}, '${category}')">
                ${isSelected ? '✓' : '+'}
            </button>
        `;
        foodList.appendChild(foodItem);
    });
}

function toggleFood(index, category) {
    let foods = [];
    if (category === 'all') {
        Object.values(foodDatabase).forEach(cat => foods.push(...cat));
    } else {
        foods = foodDatabase[category] || [];
    }
    
    const food = foods[index];
    const existingIndex = selectedFoods.findIndex(f => f.name === food.name);
    
    if (existingIndex >= 0) {
        selectedFoods.splice(existingIndex, 1);
    } else {
        selectedFoods.push(food);
    }
    
    displayFoods(category);
    updateSelectedItems();
}

function updateSelectedItems() {
    const selectedSection = document.getElementById('selectedItems');
    const selectedList = document.getElementById('selectedList');
    const selectedCount = document.getElementById('selectedCount');
    
    if (selectedFoods.length === 0) {
        selectedSection.style.display = 'none';
        return;
    }
    
    selectedSection.style.display = 'block';
    selectedCount.textContent = selectedFoods.length;
    
    selectedList.innerHTML = selectedFoods.map((food, index) => `
        <div class="selected-item">
            <div class="selected-item-info">
                <div class="selected-item-name">${food.name}</div>
                <div class="selected-item-calories">${food.calories} kcal</div>
            </div>
            <button class="btn-remove" onclick="removeFood(${index})">✕</button>
        </div>
    `).join('');
}

function removeFood(index) {
    selectedFoods.splice(index, 1);
    updateSelectedItems();
    displayFoods(document.querySelector('.category-btn.active').dataset.category);
}

function clearMeal() {
    if (confirm('Clear all selected items?')) {
        selectedFoods = [];
        updateSelectedItems();
        displayFoods(document.querySelector('.category-btn.active').dataset.category);
    }
}

async function logMeal() {
    if (selectedFoods.length === 0) {
        alert('Please select at least one food item');
        return;
    }
    
    // Calculate totals
    const totals = selectedFoods.reduce((acc, food) => ({
        calories: acc.calories + food.calories,
        carbs: acc.carbs + food.carbs,
        protein: acc.protein + food.protein,
        fats: acc.fats + food.fats
    }), { calories: 0, carbs: 0, protein: 0, fats: 0 });
    
    try {
        const response = await fetch('../api/meal/log-meal.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                meal_type: currentMeal,
                foods: selectedFoods,
                date: formatDate(selectedDate),
                ...totals
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            closeMealLogger();
            loadMealsForDate(formatDate(selectedDate));
            const expMsg = data.exp_gained ? ` (+${data.exp_gained} XP)` : '';
            showNotification(`🍽️ Meal logged successfully!${expMsg}`);
        } else {
            alert('Failed to log meal: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error logging meal:', error);
        alert('Failed to log meal: ' + error.message);
    }
}

function showNotification(message) {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: #4CAF50;
        color: white;
        padding: 1rem 2rem;
        border-radius: 10px;
        z-index: 10001;
        animation: slideDown 0.3s ease;
    `;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
