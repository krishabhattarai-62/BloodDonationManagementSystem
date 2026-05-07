<?php include 'includes/header.php'; ?>

<div class="index-wrapper">

    <div class="blood-icon">
        <img src="assets/img/droplet-solid.png" alt="Blood Drop" />
    </div>

    <h1>Blood Donation Management System</h1>

    <p>Save lives by donating blood. Join our community of heroes — login or create an account to get started.</p>

    <div class="index-btn-group">
        <a href="public/login.php"><button>Login</button></a>
        <a href="public/signup.php"><button>Register</button></a>
    </div>

</div>

<!-- Floating toggle button -->
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

<!-- Chatbox (hidden by default) -->
<div id="chatbox" style="display:none;">
    <div id="chatHeader">
        <span>&#128172; Blood Assistant</span>
        <button onclick="toggleChat()">&#10005;</button>
    </div>
    <div id="messages">
        <p class="bot">&#128075; Hi! I&#39;m your blood donation assistant. Try asking:</p>
        <div id="suggestions">
            <button class="suggestion-btn" onclick="sendSuggestion('Is O+ blood available?')">&#129656; Is O+
                available?</button>
            <button class="suggestion-btn" onclick="sendSuggestion('Find A+ donors nearby')">&#128205; Find A+ donors
                nearby</button>
            <button class="suggestion-btn" onclick="sendSuggestion('Who has B+ blood?')">&#128100; Who has B+
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
    let userLat = null, userLng = null;

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            pos => { userLat = pos.coords.latitude; userLng = pos.coords.longitude; },
            () => { userLat = null; userLng = null; }
        );
    }

    function toggleChat() {
        let box = document.getElementById("chatbox");
        let opening = box.style.display === "none";
        box.style.display = opening ? "block" : "none";
        if (opening) document.getElementById("userInput").focus();
    }

    document.getElementById("userInput").addEventListener("keydown", e => {
        if (e.key === "Enter" && !e.shiftKey) { e.preventDefault(); sendMessage(); }
    });

    function sendSuggestion(text) {
        document.getElementById("suggestions")?.remove();
        document.getElementById("userInput").value = text;
        sendMessage();
    }

    function sendMessage() {
        let input = document.getElementById("userInput");
        let message = input.value.trim();
        if (!message) return;

        document.getElementById("suggestions")?.remove();

        let messages = document.getElementById("messages");
        messages.innerHTML += `<p class="user">${message}</p>`;
        input.value = "";

        let typingId = "typing-" + Date.now();
        messages.innerHTML += `<p class="bot-error" id="${typingId}">Bot is typing&#8230;</p>`;
        messages.scrollTop = messages.scrollHeight;

        let body = "message=" + encodeURIComponent(message);
        if (userLat && userLng) {
            body += "&latitude=" + encodeURIComponent(userLat);
            body += "&longitude=" + encodeURIComponent(userLng);
        }

        fetch("public/chatbot.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: body
        })
            .then(res => res.text())
            .then(reply => {
                document.getElementById(typingId)?.remove();
                messages.innerHTML += `<p class="bot">${reply}</p>`;
                messages.scrollTop = messages.scrollHeight;
            })
            .catch(() => {
                document.getElementById(typingId)?.remove();
                messages.innerHTML += `<p class="bot-error">Could not reach the server. Please try again.</p>`;
                messages.scrollTop = messages.scrollHeight;
            });
    }
</script>

<?php include 'includes/footer.php'; ?>