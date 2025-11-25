<?php
/**
 * AI Chat Page - Full Screen Chat Experience
 */

require_once __DIR__ . '/../includes/functions.php';

initSession();

if (!isLoggedIn()) {
    redirect('/');
}

$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0A1628">
    <title>AI Coach Chat - NutriCoach AI</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../assets/css/dark-theme.css">
    <link rel="stylesheet" href="../assets/css/chat-dark.css">
</head>
<body class="dark-theme">
    <div class="chat-page-container">
        <!-- Chat Header -->
        <div class="chat-header">
            <button class="back-btn" onclick="window.history.back()">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
            </button>
            <div>
                <h1>🤖 AI Coach</h1>
            </div>
            <div class="ai-status">
                <span class="status-dot"></span>
                <span>Online</span>
            </div>
        </div>
        
        <!-- Chat Messages -->
        <div class="chat-messages-container" id="chatMessages">
            <div class="welcome-message">
                <div class="ai-avatar">🤖</div>
                <h2>Hi, I'm your AI Coach!</h2>
                <p>Ask me anything about fitness, nutrition, or your workout plan</p>
                
                <div class="suggested-questions">
                    <div class="suggested-question" onclick="sendSuggestedQuestion('What workout should I do today?')">
                        <span class="icon">💪</span>
                        <span class="text">What workout should I do today?</span>
                    </div>
                    <div class="suggested-question" onclick="sendSuggestedQuestion('How many calories should I eat?')">
                        <span class="icon">🥗</span>
                        <span class="text">How many calories should I eat?</span>
                    </div>
                    <div class="suggested-question" onclick="sendSuggestedQuestion('Tips for building muscle?')">
                        <span class="icon">🏋️</span>
                        <span class="text">Tips for building muscle?</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Chat Input -->
        <div class="chat-input-container">
            <form class="chat-input-form" id="chatForm">
                <textarea 
                    class="chat-input" 
                    id="messageInput" 
                    placeholder="Type your message..." 
                    rows="1"
                    required
                ></textarea>
                <button type="submit" class="send-btn" id="sendBtn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        // Initialize NutriCoach API
        window.NutriCoach = window.NutriCoach || {};
        window.NutriCoach.Chat = {
            async sendMessage(message) {
                console.log('Sending message:', message);
                const response = await fetch('../api/chat/message.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ message: message })
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                console.log('API response:', data);
                return data;
            },
            
            async getHistory() {
                console.log('Loading chat history...');
                const response = await fetch('../api/chat/history.php');
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                console.log('History response:', data);
                return data;
            }
        };

        const chatMessages = document.getElementById('chatMessages');
        const chatForm = document.getElementById('chatForm');
        const messageInput = document.getElementById('messageInput');
        const sendBtn = document.getElementById('sendBtn');
        let isFirstMessage = true;

        // Auto-resize textarea
        messageInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });

        // Send suggested question
        function sendSuggestedQuestion(question) {
            messageInput.value = question;
            chatForm.dispatchEvent(new Event('submit'));
        }

        // Handle form submit
        chatForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const message = messageInput.value.trim();
            if (!message) return;

            // Clear welcome message on first message
            if (isFirstMessage) {
                chatMessages.innerHTML = '';
                isFirstMessage = false;
            }

            // Add user message
            addMessage(message, 'user');
            messageInput.value = '';
            messageInput.style.height = 'auto';

            // Show typing indicator
            showTypingIndicator();

            try {
                const response = await window.NutriCoach.Chat.sendMessage(message);
                
                // Remove typing indicator
                removeTypingIndicator();

                if (response.success || response.response) {
                    const aiResponse = response.response || response.data?.response;
                    addMessage(aiResponse, 'ai');
                } else {
                    addMessage('Sorry, I encountered an error. Please try again.', 'ai');
                }
            } catch (error) {
                console.error('Chat error:', error);
                removeTypingIndicator();
                addMessage('Sorry, I encountered an error. Please try again.', 'ai');
            }
        });

        function addMessage(text, sender, timestamp = null) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `chat-message ${sender}`;
            
            const avatar = document.createElement('div');
            avatar.className = 'message-avatar';
            avatar.textContent = sender === 'user' ? '👤' : '🤖';
            
            const content = document.createElement('div');
            content.className = 'message-content';
            
            const bubble = document.createElement('div');
            bubble.className = 'message-bubble';
            bubble.textContent = text;
            
            const time = document.createElement('div');
            time.className = 'message-time';
            
            // Use provided timestamp or current time
            if (timestamp) {
                const date = new Date(timestamp);
                time.textContent = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            } else {
                time.textContent = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            }
            
            content.appendChild(bubble);
            content.appendChild(time);
            messageDiv.appendChild(avatar);
            messageDiv.appendChild(content);
            
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function showTypingIndicator() {
            const indicator = document.createElement('div');
            indicator.className = 'typing-indicator';
            indicator.id = 'typingIndicator';
            
            indicator.innerHTML = `
                <div class="message-avatar">🤖</div>
                <div class="typing-dots">
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                </div>
            `;
            
            chatMessages.appendChild(indicator);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function removeTypingIndicator() {
            const indicator = document.getElementById('typingIndicator');
            if (indicator) {
                indicator.remove();
            }
        }

        // Load chat history on page load
        async function loadChatHistory() {
            try {
                const response = await window.NutriCoach.Chat.getHistory();
                console.log('History loaded:', response);
                
                // API returns { success: true, data: { history: [...] } }
                const history = response.data?.history || response.history || [];
                
                if (history.length > 0) {
                    chatMessages.innerHTML = ''; // Clear welcome message
                    isFirstMessage = false;
                    
                    history.forEach(chat => {
                        // Pass the timestamp from database
                        addMessage(chat.message, 'user', chat.created_at);
                        addMessage(chat.response, 'ai', chat.created_at);
                    });
                    
                    console.log('Loaded', history.length, 'chat messages');
                } else {
                    console.log('No chat history found');
                }
            } catch (error) {
                console.error('Error loading chat history:', error);
                // Keep welcome message if history fails to load
            }
        }

        // Load history when page loads
        window.addEventListener('DOMContentLoaded', loadChatHistory);
    </script>
</body>
</html>