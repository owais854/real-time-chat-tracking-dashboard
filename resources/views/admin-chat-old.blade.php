<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Chat Dashboard</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        .chat-container {
            height: calc(100vh - 100px);
        }
        .messages-container {
            height: calc(100% - 80px);
        }
    </style>
</head>
<body class="bg-gray-100">
<div class="flex h-screen">
    <!-- Sidebar -->
    <div class="w-64 bg-gray-800 text-white">
        <div class="p-4 border-b border-gray-700">
            <h1 class="text-xl font-bold">Support Center</h1>
            <p class="text-sm text-gray-400">Active Chats</p>
        </div>
        <div id="active-chats" class="overflow-y-auto h-full">
            <div class="p-4 border-b border-gray-700 hover:bg-gray-700 cursor-pointer">
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                    <span>Visitor Chat</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Chat Area -->
    <div class="flex-1 flex flex-col">
        <!-- Chat Header -->
        <div class="bg-white shadow-sm p-4 border-b flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold">Visitor Chat</h2>
                <p class="text-sm text-gray-500">Active now</p>
            </div>
            <div class="flex space-x-2">
                <button class="p-2 rounded-full hover:bg-gray-100">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
            </div>
        </div>

        <!-- Messages -->
        <div id="messages" class="messages-container p-4 overflow-y-auto bg-gray-50">
            <div class="text-center text-gray-500 text-sm mt-4">No messages yet. Start the conversation!</div>
        </div>

        <!-- Message Input -->
        <div class="bg-white p-4 border-t">
            <div class="flex items-center space-x-2">
                <button class="p-2 text-gray-500 hover:text-gray-700">
                    <i class="far fa-smile"></i>
                </button>
                <input type="text" id="message-input"
                       placeholder="Type a message..."
                       class="flex-1 px-4 py-2 border rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500"
                       onkeypress="if(event.key === 'Enter') sendMessage()">
                <button onclick="sendMessage()"
                        class="bg-blue-600 text-white p-2 rounded-full hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- WebSocket Libraries -->
<script src="https://cdn.socket.io/4.3.2/socket.io.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>

<script>

    document.addEventListener('DOMContentLoaded', () => {

        Echo.channel(`chat.room`)
            .listen('ChatMessageSent', (e) => {
                console.log("Message Event", e);

            });
    });
    // 1. Setup Echo
    window.Echo = new Echo({
        broadcaster: 'reverb',
        host: window.location.hostname + ':8080',
        wsPath: '/reverb',
        transports: ['websocket'],
        forceTLS: false
    });

    function waitForEchoConnection(retries = 10) {
        if (window.Echo && window.Echo.connector && window.Echo.connector.socket) {
            console.log('Admin: Connected to WebSocket');

            window.Echo.connector.socket.on('connect', function () {
                console.log('Admin: Connected to WebSocket');

                // ✅ Only bind after socket connection
                window.Echo.channel('live-chat')
                    .listen('.chat.message', (e) => {
                        console.log('Admin received:', e);
                        if (!e.fromAdmin) {
                            addMessageToChat(e.message, false);
                        }
                    });
            });

            window.Echo.connector.socket.on('error', function (error) {
                console.error('WebSocket error:', error);
            });

            window.Echo.connector.socket.on('disconnect', function () {
                console.warn('Disconnected from WebSocket');
            });

        } else if (retries > 0) {
            console.log('Waiting for Echo connection...');
            setTimeout(() => waitForEchoConnection(retries - 1), 300);
        } else {
            console.error('Failed to connect to Echo after retries.');
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
                body: JSON.stringify({
                    message: message,
                    from_admin: true
                })
            });

            if (response.ok) {
                addMessageToChat(message, true);
                input.value = '';
                input.focus();
            }
        } catch (error) {
            console.error('Error sending message:', error);
        }
    }

    function addMessageToChat(message, isAdmin) {
        const messagesEl = document.getElementById('messages');
        const div = document.createElement('div');
        const initial = messagesEl.querySelector('.text-center');
        if (initial) initial.remove();

        div.className = `flex ${isAdmin ? 'justify-end' : 'justify-start'} mb-4`;
        div.innerHTML = `
            <div class="${isAdmin ? 'bg-green-100' : 'bg-blue-100'} rounded-lg py-2 px-4 max-w-xs">
                <div class="font-semibold">${isAdmin ? 'You' : 'Visitor'}</div>
                <div>${message}</div>
                <div class="text-xs text-gray-500 mt-1">${new Date().toLocaleTimeString()}</div>
            </div>`;
        messagesEl.appendChild(div);
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    document.addEventListener('DOMContentLoaded', async () => {
        const messagesEl = document.getElementById('messages');

        try {
            const res = await fetch('/messages');
            const messages = await res.json();
            messagesEl.innerHTML = '';
            messages.forEach(msg => {
                addMessageToChat(msg.message, msg.from_admin);
            });
        } catch (err) {
            console.error('Failed to load messages:', err);
        }
    });
</script>
</body>
</html>

{{--with IP--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Chat Dashboard</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .chat-container { height: calc(100vh - 100px); }
        .messages-container { height: calc(100% - 80px); }
    </style>
</head>
<body class="bg-gray-100">
<div class="flex h-screen">
    <!-- Sidebar -->
    <div class="w-64 bg-gray-800 text-white">
        <div class="p-4 border-b border-gray-700">
            <h1 class="text-xl font-bold">Support Center</h1>
            <p class="text-sm text-gray-400">Active Chats</p>
        </div>
        <div id="active-chats" class="overflow-y-auto h-full">
            <!-- Dynamic chat list here -->
            <div class="p-4 border-b border-gray-700 hover:bg-gray-700 cursor-pointer" onclick="switchVisitor('127.0.0.1')">
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                    <span>Visitor 127.0.0.1</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Chat Area -->
    <div class="flex-1 flex flex-col">
        <div class="bg-white shadow-sm p-4 border-b flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold" id="chat-title">Visitor Chat</h2>
                <p class="text-sm text-gray-500">Active now</p>
            </div>
        </div>

        <div id="messages" class="messages-container p-4 overflow-y-auto bg-gray-50">
            <div class="text-center text-gray-500 text-sm mt-4">No messages yet. Start the conversation!</div>
        </div>

        <div class="bg-white p-4 border-t">
            <div class="flex items-center space-x-2">
                <input type="text" id="message-input" placeholder="Type a message..."
                       class="flex-1 px-4 py-2 border rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500"
                       onkeypress="if(event.key === 'Enter') sendMessage()">
                <button onclick="sendMessage()"
                        class="bg-blue-600 text-white p-2 rounded-full hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let currentVisitorIp = null;
    let currentChannel = null;

    function switchVisitor(ip) {
        currentVisitorIp = ip;
        document.getElementById('chat-title').textContent = `Visitor ${ip}`;

        if (currentChannel) {
            Echo.leave(`private-chat.visitor.${currentChannel}`);
        }

        currentChannel = ip;

        Echo.private(`chat.visitor.${ip}`)
            .listen('ChatMessageSent', (e) => {
                addMessageToChat(e.message, e.fromAdmin, e.messageId, e.time);
            });

        loadMessages();
    }

    async function loadMessages() {
        const messagesEl = document.getElementById('messages');
        messagesEl.innerHTML = '';

        try {
            const res = await fetch('/messages');
            const messages = await res.json();
            messages.forEach(msg => {
                addMessageToChat(msg.message, msg.from_admin, msg.id, msg.timestamp);
            });
        } catch (err) {
            console.error('Failed to load messages:', err);
        }
    }

    async function sendMessage() {
        const input = document.getElementById('message-input');
        const message = input.value.trim();
        if (!message || !currentVisitorIp) return;

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
                    from_admin: true
                })
            });

            if (response.ok) {
                addMessageToChat(message, true);
                input.value = '';
                input.focus();
            }
        } catch (error) {
            console.error('Error sending message:', error);
        }
    }

    function addMessageToChat(message, isAdmin = false, messageId = null, timestamp = null) {
        const messagesContainer = document.getElementById('messages');
        if (messageId && document.querySelector(`[data-message-id="${messageId}"]`)) return;

        const messageElement = document.createElement('div');
        messageElement.className = `flex ${isAdmin ? 'justify-end' : 'justify-start'} mb-4`;
        if (messageId) messageElement.setAttribute('data-message-id', messageId);

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
</script>
</body>
</html>
