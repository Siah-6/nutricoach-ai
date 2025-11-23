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
    <title>AI Coach Chat - NutriCoach AI</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../assets/css/dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../assets/css/mobile-redesign.css">
    <style>
        /* Full screen chat layout */
        html, body {
            height: 100%;
            overflow: hidden;
            margin: 0;
            padding: 0;
        }
        
        .chat-page-container {
            display: flex;
            flex-direction: column;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--bg-light);
            overflow: hidden;
        }
        
        .chat-header {
            background: var(--primary-color);
            color: white;
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .chat-header .back-btn {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .chat-header .back-btn:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .chat-header h1 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
        }
        
        .chat-header .subtitle {
            font-size: 0.875rem;
            opacity: 0.9;
        }
        
        .chat-main {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 1rem;
            padding-bottom: 1rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            -webkit-overflow-scrolling: touch;
        }
        
        .chat-input-container {
            flex-shrink: 0;
            padding: 1rem;
            padding-bottom: 70px;
            background: white;
            border-top: 1px solid var(--border-color);
            box-shadow: 0 -2px 8px rgba(0,0,0,0.05);
        }
        
        .chat-input-wrapper {
            display: flex;
            gap: 0.5rem;
            align-items: flex-end;
        }
        
        .chat-input-wrapper textarea {
            flex: 1;
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            resize: none;
            max-height: 120px;
            font-family: inherit;
        }
        
        .chat-input-wrapper button {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            flex-shrink: 0;
        }
        
        .chat-input-wrapper button:hover:not(:disabled) {
            background: var(--primary-dark);
            transform: scale(1.05);
        }
        
        .chat-input-wrapper button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        /* Chat messages */
        .chat-message {
            display: flex;
            gap: 0.75rem;
            animation: slideIn 0.3s ease;
            margin-bottom: 1rem;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .chat-message.user {
            flex-direction: row-reverse;
        }
        
        .chat-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            flex-shrink: 0;
        }
        
        .chat-message.user .chat-avatar {
            background: var(--accent-color);
        }
        
        .chat-bubble {
            max-width: 80%;
            padding: 1rem 1.25rem;
            border-radius: 20px;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            line-height: 1.6;
            word-wrap: break-word;
        }
        
        .chat-message.user .chat-bubble {
            background: linear-gradient(135deg, #4A9DB5 0%, #3D8BA3 100%);
            color: white;
            border-radius: 20px 20px 4px 20px;
        }
        
        .chat-message.ai .chat-bubble {
            background: #F8FAFB;
            color: #2c3e50;
            border-radius: 20px 20px 20px 4px;
            border-left: 3px solid #4A9DB5;
        }
        
        .chat-time {
            font-size: 0.75rem;
            color: var(--text-light);
            margin-top: 0.25rem;
        }
        
        .chat-message.user .chat-time {
            color: rgba(255,255,255,0.8);
        }
        
        .typing-indicator {
            display: flex;
            gap: 0.25rem;
            padding: 0.5rem;
        }
        
        .typing-indicator span {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--text-light);
            animation: typing 1.4s infinite;
        }
        
        .typing-indicator span:nth-child(2) {
            animation-delay: 0.2s;
        }
        
        .typing-indicator span:nth-child(3) {
            animation-delay: 0.4s;
        }
        
        @keyframes typing {
            0%, 60%, 100% {
                transform: translateY(0);
                opacity: 0.5;
            }
            30% {
                transform: translateY(-10px);
                opacity: 1;
            }
        }
        
        .empty-chat {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2rem;
            color: var(--text-light);
        }
        
        .empty-chat svg {
            width: 80px;
            height: 80px;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        /* Mobile adjustments */
        @media (max-width: 768px) {
            .chat-page-container {
                padding-bottom: 70px; /* Space for bottom nav */
            }
            
            .chat-bubble {
                max-width: 85%;
                padding: 0.875rem 1rem;
                font-size: 0.95rem;
            }
            
            .chat-avatar {
                width: 36px;
                height: 36px;
                font-size: 0.875rem;
            }
            
            .chat-message {
                gap: 0.5rem;
                margin-bottom: 0.75rem;
            }
        }
    </style>
</head>
<body>
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
                <div class="subtitle">Your personal fitness assistant</div>
            </div>
        </div>
        
        <!-- Chat Messages -->
        <div class="chat-main" id="chatMessages">
            <div class="empty-chat">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
                <h3>Start a conversation</h3>
                <p>Ask me anything about fitness, nutrition, or your workout plan!</p>
            </div>
        </div>
        
        <!-- Chat Input -->
        <div class="chat-input-container">
            <form id="chatForm" class="chat-input-wrapper">
                <textarea 
                    id="chatInput" 
                    placeholder="Ask your AI coach..."
                    rows="1"
                    required
                ></textarea>
                <button type="submit" id="sendBtn">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                </button>
            </form>
        </div>
    </div>
    
    <!-- Mobile Bottom Navigation Only -->
    <?php if (isLoggedIn()): ?>
    <nav class="mobile-bottom-nav">
        <a href="dashboard.php" class="nav-item">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
            <span>Home</span>
        </a>
        <a href="workout-plan-new.php" class="nav-item">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M6.5 6.5h11v11h-11z"></path>
                <path d="M3 3v18h18"></path>
            </svg>
            <span>Workout</span>
        </a>
        <a href="chat.php" class="nav-item nav-item-center active">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
            <span>AI Chat</span>
        </a>
        <a href="meal-plan-new.php" class="nav-item">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"></path>
                <path d="M7 2v20"></path>
                <path d="M21 15V2v0a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"></path>
            </svg>
            <span>Meals</span>
        </a>
        <a href="profile.php" class="nav-item">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
            <span>Profile</span>
        </a>
    </nav>
    <?php endif; ?>
    
    <script src="../assets/js/main.js?v=<?php echo time(); ?>"></script>
    <script>
        (function() {
            console.log('Chat page script loading...');
            console.log('window.NutriCoach:', window.NutriCoach);
            
            // Wait for NutriCoach to be available
            if (!window.NutriCoach) {
                console.error('NutriCoach not loaded!');
                alert('Error: Chat system not loaded. Please refresh the page.');
                return;
            }
            
            // Use local variables inside function scope
            var Utils = window.NutriCoach.Utils;
            var Chat = window.NutriCoach.Chat;
            var chatMessages = document.getElementById('chatMessages');
            var chatForm = document.getElementById('chatForm');
            var chatInput = document.getElementById('chatInput');
            var sendBtn = document.getElementById('sendBtn');
            
            console.log('Utils:', Utils);
            console.log('Chat:', Chat);
        
        // Auto-resize textarea
        chatInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });
        
        // Load chat history
        async function loadChatHistory() {
            console.log('Loading chat history...');
            try {
                const response = await Chat.getHistory();
                console.log('History response:', response);
                
                // Check if response has data property (API wrapper)
                const history = response.data?.history || response.history || [];
                
                console.log('History array:', history);
                console.log('History length:', history.length);
                
                if (history && history.length > 0) {
                    chatMessages.innerHTML = '';
                    history.forEach(msg => {
                        console.log('Loading message:', msg);
                        addMessage(msg.message, 'user', msg.created_at);
                        addMessage(msg.response, 'ai', msg.created_at);
                    });
                    console.log('✅ Loaded', history.length, 'messages');
                } else {
                    console.log('No history found');
                }
            } catch (error) {
                console.error('❌ Error loading chat history:', error);
            }
        }
        
        // Add message to chat
        function addMessage(text, sender, time = null) {
            const isEmpty = chatMessages.querySelector('.empty-chat');
            if (isEmpty) {
                chatMessages.innerHTML = '';
            }
            
            const messageDiv = document.createElement('div');
            messageDiv.className = `chat-message ${sender}`;
            
            const avatar = document.createElement('div');
            avatar.className = 'chat-avatar';
            avatar.textContent = sender === 'user' ? '<?php echo substr($currentUser['name'], 0, 1); ?>' : '🤖';
            
            const bubble = document.createElement('div');
            bubble.className = 'chat-bubble';
            
            const messageText = document.createElement('div');
            messageText.textContent = text;
            
            const timeDiv = document.createElement('div');
            timeDiv.className = 'chat-time';
            const timeOptions = { hour: '2-digit', minute: '2-digit', hour12: true };
            timeDiv.textContent = time ? new Date(time).toLocaleTimeString('en-US', timeOptions) : new Date().toLocaleTimeString('en-US', timeOptions);
            
            bubble.appendChild(messageText);
            bubble.appendChild(timeDiv);
            
            messageDiv.appendChild(avatar);
            messageDiv.appendChild(bubble);
            
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        
        // Show typing indicator
        function showTyping() {
            const typingDiv = document.createElement('div');
            typingDiv.className = 'chat-message ai';
            typingDiv.id = 'typingIndicator';
            
            const avatar = document.createElement('div');
            avatar.className = 'chat-avatar';
            avatar.textContent = '🤖';
            
            const bubble = document.createElement('div');
            bubble.className = 'chat-bubble';
            
            const typing = document.createElement('div');
            typing.className = 'typing-indicator';
            typing.innerHTML = '<span></span><span></span><span></span>';
            
            bubble.appendChild(typing);
            typingDiv.appendChild(avatar);
            typingDiv.appendChild(bubble);
            
            chatMessages.appendChild(typingDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        
        function hideTyping() {
            const typing = document.getElementById('typingIndicator');
            if (typing) typing.remove();
        }
        
        // Handle form submission
        chatForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            console.log('Form submitted');
            
            if (!Chat) {
                alert('Chat system not available. Please refresh the page.');
                return;
            }
            
            const message = chatInput.value.trim();
            console.log('Message:', message);
            
            if (!message) {
                console.log('Empty message, returning');
                return;
            }
            
            // Add user message
            console.log('Adding user message to chat');
            addMessage(message, 'user');
            chatInput.value = '';
            chatInput.style.height = 'auto';
            
            // Disable input
            sendBtn.disabled = true;
            chatInput.disabled = true;
            
            // Show typing
            showTyping();
            
            try {
                console.log('Sending message to API...');
                const response = await Chat.sendMessage(message);
                console.log('API response:', response);
                hideTyping();
                
                // Check for response (new format has response at top level)
                if (response && response.response) {
                    addMessage(response.response, 'ai');
                } else if (response && response.data && response.data.response) {
                    // Fallback for old format
                    addMessage(response.data.response, 'ai');
                } else {
                    console.error('Invalid response format:', response);
                    addMessage('Sorry, I received an invalid response. Please try again.', 'ai');
                }
            } catch (error) {
                console.error('Chat error:', error);
                console.error('Error details:', error);
                hideTyping();
                
                // Show more detailed error message
                let errorMsg = 'Sorry, I encountered an error. ';
                if (error.message) {
                    errorMsg += error.message;
                } else {
                    errorMsg += 'Please check the console for details.';
                }
                addMessage(errorMsg, 'ai');
            } finally {
                sendBtn.disabled = false;
                chatInput.disabled = false;
                chatInput.focus();
            }
        });
        
        // Load history on page load
        loadChatHistory();
        })(); // End of IIFE
    </script>
</body>
</html>
