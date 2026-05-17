<div
    style="text-align:center; padding:16px; background:var(--red-deep); color:white; font-size:13px; letter-spacing:0.3px;">
    &copy; <?php echo date("Y"); ?> Blood Donation System. All rights reserved.
</div>

<?php include __DIR__ . '/toast.php'; ?>

<div id="chatGreeting" role="status">
    <button type="button" onclick="closeChatGreeting()" aria-label="Dismiss greeting">
        <i class="fa-solid fa-xmark"></i>
    </button>
    <strong>Hello!</strong>
    <span>Need help with blood donation? I am here to assist you.</span>
</div>

<div id="chatToggle" onclick="toggleChat()" title="Chat with us">
    <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#2c3e50"
        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="11" width="18" height="10" rx="2" />
        <circle cx="12" cy="5" r="2" />
        <line x1="12" y1="7" x2="12" y2="11" />
        <circle cx="8" cy="16" r="1" fill="#2c3e50" />
        <circle cx="16" cy="16" r="1" fill="#2c3e50" />
    </svg>
</div>

<div id="chatbox" style="display:none;">
    <div id="chatHeader">
        <span><i class="fa-solid fa-comments"></i> Blood Assistant</span>
        <button onclick="toggleChat()" aria-label="Close chat"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div id="messages">
        <p class="bot"><i class="fa-solid fa-hand"></i> Hi! I&#39;m your blood donation assistant. Try asking:</p>
        <div id="suggestions">
            <button class="suggestion-btn" onclick="sendSuggestion('Is O+ blood available?')"><i class="fa-solid fa-earth-americas"></i> Is O+
                available?</button>
            <button class="suggestion-btn" onclick="sendSuggestion('Find A+ donors nearby')"><i class="fa-solid fa-location-dot"></i> Find A+ donors
                nearby</button>
            <button class="suggestion-btn" onclick="sendSuggestion('Who has B+ blood?')"><i class="fa-solid fa-user"></i> Who has B+
                blood?</button>
        </div>
    </div>
    <div id="inputArea">
        <input type="text" id="userInput" placeholder="Type your message...">
        <button onclick="sendMessage()">Send</button>
    </div>
</div>

<style>
    #chatToggle {
        position: fixed;
        bottom: 24px;
        right: 24px;
        width: 52px;
        height: 52px;
        background: #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 10000;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    #chatGreeting {
        position: fixed;
        right: 88px;
        bottom: 28px;
        width: 260px;
        background: #fff;
        color: #2c3e50;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 12px 38px 12px 14px;
        z-index: 10000;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.18);
        font-size: 0.88em;
        line-height: 1.45;
    }

    #chatGreeting::after {
        content: "";
        position: absolute;
        right: -7px;
        bottom: 18px;
        width: 12px;
        height: 12px;
        background: #fff;
        border-right: 1px solid #e5e7eb;
        border-bottom: 1px solid #e5e7eb;
        transform: rotate(-45deg);
    }

    #chatGreeting strong,
    #chatGreeting span {
        display: block;
    }

    #chatGreeting button {
        position: absolute;
        top: 8px;
        right: 8px;
        width: 22px;
        height: 22px;
        border: none;
        border-radius: 50%;
        background: #f3f4f6;
        color: #4b5563;
        cursor: pointer;
        font-size: 12px;
        line-height: 22px;
    }

    #chatGreeting button:hover {
        background: #e5e7eb;
    }

    #chatbox {
        width: 300px;
        position: fixed;
        bottom: 90px;
        right: 20px;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 10px;
        z-index: 9999;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
        overflow: hidden;
    }

    #chatHeader {
        background: #2c3e50;
        color: white;
        padding: 10px 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.9em;
        font-weight: 600;
    }

    #chatHeader button {
        background: none;
        border: none;
        color: white;
        cursor: pointer;
        font-size: 1em;
        line-height: 1;
        padding: 2px 4px;
    }

    #chatHeader span {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    #messages {
        height: 240px;
        overflow-y: auto;
        padding: 10px;
        font-size: 0.88em;
    }

    .user {
        text-align: right;
        color: #2c3e50;
        font-weight: 500;
    }

    .bot {
        text-align: left;
        color: #333;
    }

    .bot-error {
        color: #aaa;
        font-style: italic;
        font-size: 0.82em;
    }

    #inputArea {
        display: flex;
        border-top: 1px solid #eee;
    }

    #inputArea input {
        flex: 1;
        padding: 9px 12px;
        border: none;
        outline: none;
        font-size: 0.85em;
    }

    #inputArea button {
        padding: 9px 14px;
        background: #2c3e50;
        color: white;
        border: none;
        cursor: pointer;
        font-size: 0.85em;
    }

    #suggestions {
        display: flex;
        flex-direction: column;
        gap: 6px;
        margin-top: 8px;
    }

    .suggestion-btn {
        display: flex;
        align-items: center;
        gap: 6px;
        background: #f4f6f8;
        color: #2c3e50;
        border: 1px solid #d0d8e0;
        border-radius: 20px;
        padding: 6px 12px;
        font-size: 0.82em;
        cursor: pointer;
        text-align: left;
    }

    .suggestion-btn:hover {
        background: #e2e8f0;
    }
</style>

<script>
    let userLat = null;
    let userLng = null;

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            pos => { userLat = pos.coords.latitude; userLng = pos.coords.longitude; },
            () => { userLat = null; userLng = null; }
        );
    }

    function toggleChat() {
        const box = document.getElementById("chatbox");
        const opening = box.style.display === "none";
        box.style.display = opening ? "block" : "none";
        if (opening) {
            closeChatGreeting();
        }
        if (opening) {
            document.getElementById("userInput").focus();
        }
    }

    function closeChatGreeting() {
        const greeting = document.getElementById("chatGreeting");
        if (greeting) {
            greeting.style.display = "none";
        }
    }

    document.getElementById("userInput").addEventListener("keydown", e => {
        if (e.key === "Enter" && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    function sendSuggestion(text) {
        document.getElementById("suggestions")?.remove();
        document.getElementById("userInput").value = text;
        sendMessage();
    }

    function sendMessage() {
        const input = document.getElementById("userInput");
        const message = input.value.trim();
        if (!message) {
            return;
        }

        document.getElementById("suggestions")?.remove();

        const messages = document.getElementById("messages");
        const userMessage = document.createElement("p");
        userMessage.className = "user";
        userMessage.textContent = message;
        messages.appendChild(userMessage);
        input.value = "";

        const typing = document.createElement("p");
        typing.className = "bot-error";
        typing.id = "typing-" + Date.now();
        typing.textContent = "Bot is typing...";
        messages.appendChild(typing);
        messages.scrollTop = messages.scrollHeight;

        let body = "message=" + encodeURIComponent(message);
        if (userLat && userLng) {
            body += "&latitude=" + encodeURIComponent(userLat);
            body += "&longitude=" + encodeURIComponent(userLng);
        }

        fetch("/BloodDonationManagementSystem/public/chatbot.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: body
        })
            .then(res => res.text())
            .then(reply => {
                typing.remove();
                const botMessage = document.createElement("p");
                botMessage.className = "bot";
                botMessage.innerHTML = reply;
                messages.appendChild(botMessage);
                messages.scrollTop = messages.scrollHeight;
            })
            .catch(() => {
                typing.remove();
                const errorMessage = document.createElement("p");
                errorMessage.className = "bot-error";
                errorMessage.textContent = "Could not reach the server. Please try again.";
                messages.appendChild(errorMessage);
                messages.scrollTop = messages.scrollHeight;
            });
    }
</script>
