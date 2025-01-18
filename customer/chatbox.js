function toggleChatbox() {
    const chatboxSection = document.getElementById('chatbox-section');
    if (chatboxSection.style.display === 'none') {
        chatboxSection.style.display = 'block';
        sendWelcomeMessage();
    } else {
        chatboxSection.style.display = 'none';
    }
}

function minimizeChat() {
    const chatboxSection = document.getElementById('chatbox-section');
    chatboxSection.style.display = 'none';
}

function sendWelcomeMessage() {
    fetch('../chatbox/chatbot.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            message: "hello"
        }), // Custom message for welcome intent
    })
        .then(response => response.json())
        .then(data => {
            const messages = document.getElementById('messages');
            messages.innerHTML += `
                <div class="message bot">
                    <div class="message-content"><strong>Kojek:</strong> ${data.reply}</div>
                </div>
            `;
            messages.scrollTop = messages.scrollHeight;
        })
        .catch(() => {
            const messages = document.getElementById('messages');
            messages.innerHTML += `
                <div class="message bot">
                    <div class="message-content"><strong>Kojek:</strong> Sorry, there was an error initializing the chat.</div>
                </div>
            `;
        });
}

function sendMessage() {
    const userInput = document.getElementById('userInput').value.trim();
    if (!userInput) return;

    document.getElementById('userInput').value = '';

    const messages = document.getElementById('messages');
    messages.innerHTML += `
        <div class="message user">
            <div class="message-content"><strong>You:</strong> ${userInput}</div>
        </div>
    `;

    // Add loading bubble
    const loadingBubble = document.createElement('div');
    loadingBubble.className = 'message bot';
    loadingBubble.innerHTML = `
        <div class="loading-bubble">. . .</div>
    `;
    messages.appendChild(loadingBubble);
    messages.scrollTop = messages.scrollHeight;

    setTimeout(() => {
        // Replace loading bubble with bot response
        loadingBubble.remove();
        fetch('../chatbox/chatbot.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                message: userInput
            }),
        })
            .then(response => response.json())
            .then(data => {
                messages.innerHTML += `
                    <div class="message bot">
                        <div class="message-content"><strong>Kojek:</strong> ${data.reply}</div>
                    </div>
                `;
                messages.scrollTop = messages.scrollHeight;
            })
            .catch(() => {
                messages.innerHTML += `
                    <div class="message bot">
                        <div class="message-content"><strong>Kojek:</strong> Sorry, there was an error processing your message.</div>
                    </div>
                `;
            });
    }, 1500); // delay for loading
}

window.onload = function() {
    const userInput = document.getElementById('userInput');
    if (userInput) {
        userInput.addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
//              console.log('Key pressed:', event.key);
                sendMessage();
                event.preventDefault(); // Prevent default form submission
            }
        });
    }
};
