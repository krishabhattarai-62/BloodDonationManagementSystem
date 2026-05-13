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

<div id="chatbox">
    <div id="messages"></div>
    <div id="inputArea">
        <input type="text" id="userInput" placeholder="Ask about blood...">
        <button onclick="sendMessage()">Send</button>
    </div>
</div>


<style>
    #chatbox {
        width: 300px;
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: #fff;
        border: 1px solid #ccc;
        border-radius: 10px;
        pointer-events: auto;
        z-index: 9999;
    }

    #messages {
        height: 250px;
        overflow-y: auto;
        padding: 10px;
    }

    .user {
        text-align: right;
        color: blue;
    }

    .bot {
        text-align: left;
        color: green;
    }

    .bot-error {
        text-align: left;
        color: #999;
        font-style: italic;
        font-size: 0.85em;
    }

    #inputArea {
        display: flex;
        border-top: 1px solid #ccc;
        pointer-events: auto;
    }

    #inputArea input {
        flex: 1;
        min-width: 0;
        padding: 10px;
        border: none;
        outline: none;
        pointer-events: auto;
        position: relative;
        z-index: 1;
    }

    #inputArea button {
        padding: 10px;
        background: red;
        color: white;
        border: none;
        cursor: pointer;
        pointer-events: auto;
    }
</style>

<script>
    // Cache the user's coordinates once on page load
    let userLat = null;
    let userLng = null;

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function (pos) {
                userLat = pos.coords.latitude;
                userLng = pos.coords.longitude;
            },
            function () {
                // Permission denied or unavailable — nearby search will be skipped server-side
                userLat = null;
                userLng = null;
            }
        );
    }

    function sendMessage() {
        let input = document.getElementById("userInput");
        let message = input.value.trim();
        if (message === "") return;

        let messages = document.getElementById("messages");
        messages.innerHTML += `<p class="user">${message}</p>`;
        input.value = "";

        // Show a typing indicator
        let typingId = "typing-" + Date.now();
        messages.innerHTML += `<p class="bot-error" id="${typingId}">Bot is typing…</p>`;
        messages.scrollTop = messages.scrollHeight;

        // Build form body — include coords only when available
        let body = "message=" + encodeURIComponent(message);
        if (userLat !== null && userLng !== null) {
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