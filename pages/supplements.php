<?php
/**
 * Supplements Page - AI Recommendations + Library
 */

require_once __DIR__ . '/../includes/functions.php';

initSession();

if (!isLoggedIn()) {
    redirect('/');
}

if (!isOnboardingCompleted(getCurrentUserId())) {
    redirect('/pages/onboarding.php');
}

$user = getCurrentUser();
$profile = getUserProfile(getCurrentUserId());
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0A1628">
    <title>Supplements - NutriCoach AI</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dark-theme.css">
    <link rel="stylesheet" href="../assets/css/supplements.css">
</head>
<body class="dark-theme">
    <div class="supplements-container">
        <!-- Header -->
        <div class="supplements-header">
            <button class="back-btn" onclick="window.history.back()">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
            </button>
            <div class="header-title">
                <h1>💊 Supplements</h1>
                <p>Personalized recommendations & guide</p>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs-container">
            <button class="tab-btn active" data-tab="ai-plan">My Plan</button>
            <button class="tab-btn" data-tab="library">Library</button>
        </div>

        <!-- AI Plan Tab -->
        <div class="tab-content active" id="ai-plan">
            <div class="ai-plan-section">
                <div class="plan-header">
                    <h2>🤖 AI Recommended For You</h2>
                    <p>Based on your goal: <strong><?php echo htmlspecialchars($profile['fitness_goal']); ?></strong></p>
                </div>

                <!-- Loading State -->
                <div class="loading-state" id="loadingPlan">
                    <div class="spinner"></div>
                    <p>Analyzing your profile...</p>
                </div>

                <!-- Recommendations List -->
                <div class="recommendations-list" id="recommendationsList" style="display: none;">
                    <!-- Will be populated by JavaScript -->
                </div>

                <button class="btn-regenerate" id="regenerateBtn" style="display: none;">
                    🔄 Regenerate Recommendations
                </button>
            </div>
        </div>

        <!-- Library Tab -->
        <div class="tab-content" id="library">
            <div class="library-section">
                <div class="search-box">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="M21 21l-4.35-4.35"/>
                    </svg>
                    <input type="text" id="searchInput" placeholder="Search supplements...">
                </div>

                <div class="categories">
                    <button class="category-btn active" data-category="all">All</button>
                    <button class="category-btn" data-category="protein">Protein</button>
                    <button class="category-btn" data-category="performance">Performance</button>
                    <button class="category-btn" data-category="recovery">Recovery</button>
                    <button class="category-btn" data-category="health">Health</button>
                </div>

                <div class="supplements-grid" id="supplementsGrid">
                    <!-- Will be populated by JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script>
        // Supplement Database
        const supplementsDB = [
            {
                id: 1,
                name: "Whey Protein",
                icon: "🥤",
                category: "protein",
                shortDesc: "Fast-absorbing protein for muscle growth",
                benefits: ["Muscle recovery", "Muscle growth", "Convenient protein source"],
                timing: "Post-workout or between meals",
                dosage: "25-30g per serving",
                bestFor: ["Muscle Gain", "Weight Loss"],
                safety: "Generally safe. Avoid if lactose intolerant.",
                science: "Highly researched and proven effective for muscle protein synthesis."
            },
            {
                id: 2,
                name: "Creatine Monohydrate",
                icon: "💪",
                category: "performance",
                shortDesc: "Increases strength and muscle mass",
                benefits: ["Increased strength", "Muscle growth", "Improved performance"],
                timing: "Daily, anytime (consistency matters)",
                dosage: "5g per day",
                bestFor: ["Muscle Gain", "Strength"],
                safety: "Very safe and well-researched.",
                science: "One of the most studied and effective supplements."
            },
            {
                id: 3,
                name: "Omega-3 Fish Oil",
                icon: "🐟",
                category: "health",
                shortDesc: "Supports heart, brain, and joint health",
                benefits: ["Reduced inflammation", "Joint health", "Heart health", "Brain function"],
                timing: "With meals",
                dosage: "1-2g EPA+DHA daily",
                bestFor: ["General Health", "Recovery"],
                safety: "Safe for most people.",
                science: "Strong evidence for cardiovascular and anti-inflammatory benefits."
            },
            {
                id: 4,
                name: "Caffeine",
                icon: "☕",
                category: "performance",
                shortDesc: "Boosts energy, focus, and performance",
                benefits: ["Increased energy", "Enhanced focus", "Improved endurance", "Fat burning"],
                timing: "30-60 min before workout",
                dosage: "200-400mg",
                bestFor: ["Performance", "Weight Loss"],
                safety: "Safe in moderation. Avoid if sensitive to caffeine.",
                science: "Well-researched ergogenic aid."
            },
            {
                id: 5,
                name: "Beta-Alanine",
                icon: "⚡",
                category: "performance",
                shortDesc: "Delays muscle fatigue during training",
                benefits: ["Increased endurance", "Delayed fatigue", "More reps"],
                timing: "Pre-workout",
                dosage: "2-5g per day",
                bestFor: ["Muscle Gain", "Endurance"],
                safety: "Safe. May cause tingling sensation.",
                science: "Proven to improve high-intensity exercise performance."
            },
            {
                id: 6,
                name: "BCAAs",
                icon: "🔗",
                category: "recovery",
                shortDesc: "Branched-chain amino acids for recovery",
                benefits: ["Muscle recovery", "Reduced soreness", "Prevents breakdown"],
                timing: "During or post-workout",
                dosage: "5-10g",
                bestFor: ["Recovery", "Muscle Gain"],
                safety: "Safe for most people.",
                science: "Beneficial for recovery, especially during fasted training."
            },
            {
                id: 7,
                name: "Vitamin D",
                icon: "☀️",
                category: "health",
                shortDesc: "Essential for bone health and immunity",
                benefits: ["Bone health", "Immune function", "Mood support", "Testosterone"],
                timing: "Morning with food",
                dosage: "1000-4000 IU daily",
                bestFor: ["General Health"],
                safety: "Very safe at recommended doses.",
                science: "Many people are deficient. Important for overall health."
            },
            {
                id: 8,
                name: "Multivitamin",
                icon: "💊",
                category: "health",
                shortDesc: "Covers nutritional gaps in your diet",
                benefits: ["Nutritional insurance", "Overall health", "Energy support"],
                timing: "Morning with breakfast",
                dosage: "As directed on label",
                bestFor: ["General Health"],
                safety: "Safe when used as directed.",
                science: "Helpful for covering micronutrient gaps."
            },
            {
                id: 9,
                name: "Pre-Workout",
                icon: "🚀",
                category: "performance",
                shortDesc: "Complete formula for energy and pumps",
                benefits: ["Energy boost", "Better pumps", "Enhanced focus", "Increased endurance"],
                timing: "20-30 min before workout",
                dosage: "As directed (usually 1 scoop)",
                bestFor: ["Performance", "Muscle Gain"],
                safety: "Check ingredients. Avoid if sensitive to stimulants.",
                science: "Combination of proven ingredients like caffeine, beta-alanine, citrulline."
            },
            {
                id: 10,
                name: "Casein Protein",
                icon: "🌙",
                category: "protein",
                shortDesc: "Slow-digesting protein for overnight recovery",
                benefits: ["Sustained protein release", "Muscle recovery", "Prevents breakdown"],
                timing: "Before bed",
                dosage: "25-40g",
                bestFor: ["Muscle Gain"],
                safety: "Safe. Avoid if lactose intolerant.",
                science: "Slow digestion makes it ideal for nighttime use."
            }
        ];

        let currentCategory = 'all';
        let currentTab = 'ai-plan';

        // Tab Switching
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const tab = btn.dataset.tab;
                
                // Update buttons
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                
                // Update content
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                document.getElementById(tab).classList.add('active');
                
                currentTab = tab;
                
                if (tab === 'ai-plan' && document.getElementById('recommendationsList').children.length === 0) {
                    generateAIRecommendations();
                } else if (tab === 'library' && document.getElementById('supplementsGrid').children.length === 0) {
                    displaySupplements();
                }
            });
        });

        // Generate AI Recommendations
        async function generateAIRecommendations() {
            const loadingState = document.getElementById('loadingPlan');
            const recommendationsList = document.getElementById('recommendationsList');
            const regenerateBtn = document.getElementById('regenerateBtn');
            
            loadingState.style.display = 'block';
            recommendationsList.style.display = 'none';
            
            try {
                const userGoal = '<?php echo $profile['fitness_goal']; ?>';
                const prompt = `Based on a user with fitness goal: "${userGoal}", recommend 3-4 supplements. For each supplement, provide: name, why it helps this goal, when to take it, and dosage. Format as: "Supplement Name: Brief explanation (1 sentence). Timing: X. Dosage: Y."`;
                
                const response = await fetch('../api/chat/message.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ message: prompt })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    displayAIRecommendations(data.response, userGoal);
                    loadingState.style.display = 'none';
                    recommendationsList.style.display = 'block';
                    regenerateBtn.style.display = 'block';
                }
            } catch (error) {
                console.error('Error:', error);
                loadingState.innerHTML = '<p style="color: #ff6b6b;">Failed to generate recommendations. Please try again.</p>';
            }
        }

        function displayAIRecommendations(aiResponse, userGoal) {
            const container = document.getElementById('recommendationsList');
            container.innerHTML = '';
            
            // Parse AI response and match with our database
            const lines = aiResponse.split('\n').filter(line => line.trim());
            const recommendations = [];
            
            lines.forEach(line => {
                supplementsDB.forEach(supp => {
                    if (line.toLowerCase().includes(supp.name.toLowerCase()) && !recommendations.find(r => r.id === supp.id)) {
                        recommendations.push(supp);
                    }
                });
            });
            
            // If AI didn't match well, use goal-based fallback
            if (recommendations.length < 3) {
                recommendations.length = 0;
                supplementsDB.forEach(supp => {
                    if (supp.bestFor.some(goal => userGoal.toLowerCase().includes(goal.toLowerCase()))) {
                        recommendations.push(supp);
                    }
                });
            }
            
            // Display recommendations
            recommendations.slice(0, 4).forEach(supp => {
                const card = createRecommendationCard(supp);
                container.appendChild(card);
            });
        }

        function createRecommendationCard(supp) {
            const card = document.createElement('div');
            card.className = 'recommendation-card';
            card.innerHTML = `
                <div class="rec-header">
                    <div class="rec-icon">${supp.icon}</div>
                    <div class="rec-title">
                        <h3>${supp.name}</h3>
                        <p>${supp.shortDesc}</p>
                    </div>
                </div>
                <div class="rec-benefits">
                    ${supp.benefits.slice(0, 3).map(b => `<span class="benefit-tag">✓ ${b}</span>`).join('')}
                </div>
                <div class="rec-details">
                    <div class="rec-detail">
                        <strong>⏰ When:</strong> ${supp.timing}
                    </div>
                    <div class="rec-detail">
                        <strong>💊 Dosage:</strong> ${supp.dosage}
                    </div>
                </div>
                <button class="btn-learn-more" onclick="showSupplementDetails(${supp.id})">
                    Learn More
                </button>
            `;
            return card;
        }

        // Display Library
        function displaySupplements(category = 'all', searchTerm = '') {
            const grid = document.getElementById('supplementsGrid');
            grid.innerHTML = '';
            
            let filtered = supplementsDB;
            
            if (category !== 'all') {
                filtered = filtered.filter(s => s.category === category);
            }
            
            if (searchTerm) {
                filtered = filtered.filter(s => 
                    s.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                    s.shortDesc.toLowerCase().includes(searchTerm.toLowerCase())
                );
            }
            
            filtered.forEach(supp => {
                const card = document.createElement('div');
                card.className = 'supplement-card';
                card.onclick = () => showSupplementDetails(supp.id);
                card.innerHTML = `
                    <div class="supp-icon">${supp.icon}</div>
                    <h3>${supp.name}</h3>
                    <p>${supp.shortDesc}</p>
                    <div class="supp-category">${supp.category}</div>
                `;
                grid.appendChild(card);
            });
            
            if (filtered.length === 0) {
                grid.innerHTML = '<p style="text-align: center; color: var(--text-secondary); padding: 2rem;">No supplements found</p>';
            }
        }

        // Category Filter
        document.querySelectorAll('.category-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                currentCategory = btn.dataset.category;
                displaySupplements(currentCategory, document.getElementById('searchInput').value);
            });
        });

        // Search
        document.getElementById('searchInput').addEventListener('input', (e) => {
            displaySupplements(currentCategory, e.target.value);
        });

        // Regenerate
        document.getElementById('regenerateBtn').addEventListener('click', generateAIRecommendations);

        // Show Details Modal
        function showSupplementDetails(id) {
            const supp = supplementsDB.find(s => s.id === id);
            if (!supp) return;
            
            const modal = document.createElement('div');
            modal.className = 'supplement-modal';
            modal.innerHTML = `
                <div class="modal-content">
                    <button class="modal-close" onclick="this.parentElement.parentElement.remove()">×</button>
                    <div class="modal-header">
                        <div class="modal-icon">${supp.icon}</div>
                        <h2>${supp.name}</h2>
                        <p>${supp.shortDesc}</p>
                    </div>
                    <div class="modal-body">
                        <div class="modal-section">
                            <h3>✨ Benefits</h3>
                            <ul>
                                ${supp.benefits.map(b => `<li>${b}</li>`).join('')}
                            </ul>
                        </div>
                        <div class="modal-section">
                            <h3>⏰ When to Take</h3>
                            <p>${supp.timing}</p>
                        </div>
                        <div class="modal-section">
                            <h3>💊 Recommended Dosage</h3>
                            <p>${supp.dosage}</p>
                        </div>
                        <div class="modal-section">
                            <h3>🎯 Best For</h3>
                            <p>${supp.bestFor.join(', ')}</p>
                        </div>
                        <div class="modal-section">
                            <h3>⚠️ Safety</h3>
                            <p>${supp.safety}</p>
                        </div>
                        <div class="modal-section">
                            <h3>🔬 Science</h3>
                            <p>${supp.science}</p>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }

        // Initialize
        window.addEventListener('DOMContentLoaded', () => {
            generateAIRecommendations();
        });
    </script>
</body>
</html>
