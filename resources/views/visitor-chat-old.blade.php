<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Chat Support</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .chat-container {
            height: 80vh;
            max-height: 600px;
        }
        .messages {
            height: calc(100% - 60px);
        }
    </style>
</head>
<body class="bg-gray-100">
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="bg-blue-600 text-white p-4 flex items-center">
            <i class="fas fa-headset text-xl mr-2"></i>
            <h1 class="text-xl font-semibold">Customer Support</h1>
            <div class="ml-auto flex items-center">
                <span class="text-sm mr-2">Live</span>
                <div class="w-3 h-3 bg-red-500 rounded-full animate-pulse"></div>
            </div>
        </div>

        <div id="messages" class="messages p-4 space-y-4 overflow-y-auto">
            <div class="text-center text-gray-500 text-sm mt-4">A support agent will be with you shortly...</div>
        </div>

        <div class="border-t p-4 bg-gray-50">
            <div class="flex space-x-2">
                <input type="text" id="message-input" placeholder="Type your message..."
                       class="flex-1 px-4 py-2 border rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500"
                       onkeypress="if(event.key === 'Enter') sendMessage()">
                <button onclick="sendMessage()"
                        class="bg-blue-600 text-white px-6 py-2 rounded-full hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.socket.io/4.3.2/socket.io.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.11.3/dist/echo.iife.js"></script>

<script>
    window.Echo = new Echo({
        broadcaster: 'reverb',
        host: window.location.hostname + ':8080',
        wsPath: '/reverb',
        transports: ['websocket'],
        forceTLS: false,
    });

    // Initialize Echo
    // window.Echo = new Echo({
    //     broadcaster: 'socket.io',
    //     host: window.location.hostname + ':8080',
    //     client: io,
    //     withCredentials: true,
    //     transports: ['websocket'],
    // });

    function waitForEchoConnection(retries = 10) {
        if (window.Echo && window.Echo.connector && window.Echo.connector.socket) {
            console.log('Visitor: Connected to Reverb WebSocket server');

            window.Echo.connector.socket.on('connect', function () {
                console.log('Visitor: Successfully connected to WebSocket server');

                // ✅ SET UP LISTENER HERE AFTER CONNECTION SUCCESS
                window.Echo.channel('live-chat')
                    .listen('.chat.message', (e) => {
                        console.log('Received event:', e);
                        if (e.fromAdmin) addMessageToChat(e.message, true);
                    });
            });

            window.Echo.connector.socket.on('error', function (error) {
                console.error('WebSocket error:', error);
            });

            window.Echo.connector.socket.on('disconnect', function () {
                console.log('Visitor: Disconnected from WebSocket server');
            });

        } else if (retries > 0) {
            console.log('Waiting for Echo connector to be ready...');
            setTimeout(() => waitForEchoConnection(retries - 1), 300);
        } else {
            console.error('Failed to connect to Echo after multiple attempts.');
        }
    }

    waitForEchoConnection();

    async function sendMessage() {
        const input = document.getElementById('message-input');
        const message = input.value.trim();
        if (!message) return;

        try {
            const response = await fetch('/messages', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ message: message, from_admin: false })
            });

            if (response.ok) {
                addMessageToChat(message, false);
                input.value = '';
                input.focus();
            }
        } catch (err) {
            console.error('Send failed', err);
        }
    }

    function addMessageToChat(message, isAdmin) {
        const messagesEl = document.getElementById('messages');
        const messageEl = document.createElement('div');

        const initial = messagesEl.querySelector('.text-center');
        if (initial) initial.remove();

        messageEl.className = `flex ${isAdmin ? 'justify-start' : 'justify-end'} mb-4`;
        messageEl.innerHTML = `
            <div class="${isAdmin ? 'bg-blue-100' : 'bg-green-100'} rounded-lg py-2 px-4 max-w-xs">
                <div class="font-semibold">${isAdmin ? 'Support Agent' : 'You'}</div>
                <div>${message}</div>
                <div class="text-xs text-gray-500 mt-1">${new Date().toLocaleTimeString()}</div>
            </div>
        `;

        messagesEl.appendChild(messageEl);
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    document.addEventListener('DOMContentLoaded', async () => {
        try {
            const response = await fetch('/messages');
            const messages = await response.json();
            const messagesEl = document.getElementById('messages');

            if (messages.length > 0) messagesEl.innerHTML = '';

            messages.forEach(msg => {
                addMessageToChat(msg.message, msg.from_admin);
            });
        } catch (error) {
            console.error('Failed to load old messages:', error);
        }
    });
</script>
</body>
</html>

{{--with ip--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Chat Support</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .chat-container {
            height: 80vh;
            max-height: 600px;
        }
        .messages {
            height: calc(100% - 60px);
        }
    </style>
</head>
<body class="bg-gray-100">
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="bg-blue-600 text-white p-4 flex items-center">
            <i class="fas fa-headset text-xl mr-2"></i>
            <h1 class="text-xl font-semibold">Customer Support</h1>
            <div class="ml-auto flex items-center">
                <span class="text-sm mr-2">Live</span>
                <div class="w-3 h-3 bg-red-500 rounded-full animate-pulse"></div>
            </div>
        </div>

        <div id="messages" class="messages p-4 space-y-4 overflow-y-auto">
            <div class="text-center text-gray-500 text-sm mt-4">A support agent will be with you shortly...</div>
        </div>

        <div class="border-t p-4 bg-gray-50">
            <div class="flex space-x-2">
                <input type="text" id="message-input" placeholder="Type your message..."
                       class="flex-1 px-4 py-2 border rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500"
                       onkeypress="if(event.key === 'Enter') sendMessage()">
                <button onclick="sendMessage()"
                        class="bg-blue-600 text-white px-6 py-2 rounded-full hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    window.visitorIp = "{{ request()->ip() }}";
</script>

<script>
    document.addEventListener('DOMContentLoaded', async () => {

        // ✅ Make sendMessage globally accessible
        window.sendMessage = async function () {

            const input = document.getElementById('message-input');
            const message = input.value.trim();
            if (!message) return;

            try {
                const response = await fetch('/messages', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        message: message,
                        from_admin: false,
                        client_ip: window.visitorIp
                    })
                });

                if (response.ok) {
                    addMessageToChat(message, false);
                    input.value = '';
                    input.focus();
                }
            } catch (error) {
                console.error('Error sending message:', error);
            }
        };

        function addMessageToChat(message, isAdmin = false, messageId = null, timestamp = null) {
            const messagesContainer = document.getElementById('messages');

            if (messageId && document.querySelector(`[data-message-id="${messageId}"]`)) {
                return;
            }

            const messageElement = document.createElement('div');
            messageElement.className = `flex ${isAdmin ? 'justify-start' : 'justify-end'} mb-4`;

            if (messageId) {
                messageElement.setAttribute('data-message-id', messageId);
            }

            const time = timestamp ? new Date(timestamp).toLocaleTimeString() : new Date().toLocaleTimeString();

            messageElement.innerHTML = `
                <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg ${isAdmin ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800'}">
                    ${message}
                    <div class="text-xs mt-1 ${isAdmin ? 'text-blue-100' : 'text-gray-500'}">${time}</div>
                </div>
            `;

            messagesContainer.appendChild(messageElement);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        console.log('Visitor: WebSocket connection established', window.Echo);

        document.addEventListener('DOMContentLoaded', async () => {
            // Get client IP (you may need to pass this from your backend)
            let clientIp = await fetch('/get-ip').then(res => res.json()).then(data => data.ip);

            window.Echo = new Echo({
                broadcaster: 'reverb',
                key: process.env.MIX_REVERB_APP_KEY,
                wsHost: window.location.hostname,
                wsPort: 8080,
                forceTLS: false,
                enabledTransports: ['ws', 'wss'],
                auth: {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Client-IP': clientIp
                    },
                },
            });

            Echo.private('chat.room')
                .listen('ChatMessageSent', (e) => {
                    console.log('Message received:', e);
                    addMessageToChat(e.message, e.fromAdmin, e.id, e.timestamp);
                })
                .error((error) => {
                    console.error('Subscription error:', error);
                });
        });

        // ✅ Load old messages on page load
        try {
            const res = await fetch('/messages');
            const messages = await res.json();
            const messagesEl = document.getElementById('messages');
            messagesEl.innerHTML = '';

            messages.forEach(msg => {
                addMessageToChat(msg.message, msg.from_admin, msg.id, msg.timestamp);
            });
        } catch (err) {
            console.error('Failed to load messages:', err);
        }
    });
</script>

</body>
</html>
