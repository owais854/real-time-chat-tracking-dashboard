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
{{--<script src="http://127.0.0.1:8000/js/embed.js"></script>--}}
<script>
    // Function to append messages in chat (global scope)
    let displayedMessageIds = new Set(); // Track displayed messages to prevent duplicates

    function addMessageToChat(message, fromAdmin, messageId = null) {
        // Prevent duplicate messages
        if (messageId && displayedMessageIds.has(messageId)) {
            console.log('Message already displayed, skipping:', messageId);
            return;
        }

        if (messageId) {
            displayedMessageIds.add(messageId);
        }

        const messagesContainer = document.getElementById('messages');
        const messageElement = document.createElement('div');

        if (fromAdmin) {
            messageElement.className = "text-left";
            messageElement.innerHTML = `
            <div class="inline-block bg-gray-200 text-gray-800 px-4 py-2 rounded-lg">
                <strong>Admin:</strong> ${message}
            </div>
        `;
        } else {
            messageElement.className = "text-right";
            messageElement.innerHTML = `
            <div class="inline-block bg-blue-500 text-white px-4 py-2 rounded-lg">
                ${message}
            </div>
        `;
        }

        messagesContainer.appendChild(messageElement);
        messagesContainer.scrollTop = messagesContainer.scrollHeight; // auto scroll
    }

    document.addEventListener('DOMContentLoaded', async () => {
        console.log('visitor: Connected to WebSocket: ', window.Echo);
        let visitorSessionId = "{{ $visitorId }}";

        // Listen for admin messages (only to this specific visitor)
        Echo.channel('chat.visitor.' + visitorSessionId)
            .listen('ChatMessageSent', (e) => {
                console.log('Visitor received message event:', e);
                console.log('Message details - fromAdmin:', e.fromAdmin, 'message:', e.message, 'sessionId:', e.sessionId);

                // Only show admin messages (fromAdmin = true) to this visitor
                if (e.fromAdmin) {
                    console.log('Adding admin message to chat:', e.message);
                    addMessageToChat(e.message, true, e.messageId); // Pass messageId for duplicate prevention
                } else {
                    console.log('Ignoring visitor message (not from admin)');
                }
            })
            .error((error) => {
                console.error('Visitor channel error:', error);
            });

        // Send message
        let isSending = false; // Prevent multiple simultaneous sends

        window.sendMessage = async function () {
            if (isSending) {
                console.log('Message already being sent, please wait...');
                return;
            }

            const input = document.getElementById('message-input');
            const message = input.value.trim();
            if (!message) return;

            isSending = true;

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
                        session_id: visitorSessionId
                    })
                });

                if (response.ok) {
                    const data = await response.json();
                    console.log('Message sent successfully:', data);
                    addMessageToChat(message, false, data.message_id);
                    input.value = '';
                    input.focus();
                } else {
                    console.error('Failed to send message:', response.status, response.statusText);
                }
            } catch (error) {
                console.error('Error sending message:', error);
            } finally {
                isSending = false;
            }
        };
    });
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    
    // Join when page loads
    axios.post('{{ route('visitor.join') }}', {}, {
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
        }
    }).catch(error => {
        console.error('Error joining chat:', error);
    });

    // Heartbeat ping every 20s (slightly less than 2 minutes to be safe)
    setInterval(() => {
        axios.post('{{ route('visitor.ping') }}', {}, {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(response => {
            console.log('Ping successful');
        }).catch(error => {
            console.error('Ping failed:', error);
        });
    }, 20000);

    // Best-effort leave on unload
    window.addEventListener('beforeunload', function () {
        const data = new FormData();
        data.append('_token', csrfToken);
        navigator.sendBeacon('{{ route('visitor.leave') }}', data);
    });
});
</script>

</body>
</html>
