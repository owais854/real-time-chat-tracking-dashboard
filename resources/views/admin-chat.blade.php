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
            <p class="text-sm text-gray-400">Active Chats: <span id="visitor-count">0</span></p>
        </div>
        <div id="active-chats" class="overflow-y-auto h-full">
            <!-- Active visitors will be loaded here -->
        </div>
    </div>

    <!-- Main Chat Area -->
    <div class="flex-1 flex flex-col">
        <!-- Chat Header -->
        <div id="chat-header" class="bg-white shadow-sm p-4 border-b flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-800 transition-colors duration-200" title="Back to Dashboard">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <h2 class="text-lg font-semibold">Visitor Chat</h2>
                    <p class="text-sm text-gray-500">Active now</p>
                </div>
            </div>
{{--            <div class="flex space-x-2">--}}
{{--                <button onclick="loadActiveVisitors()" class="p-2 rounded-full hover:bg-gray-100" title="Refresh visitors">--}}
{{--                    <i class="fas fa-sync-alt"></i>--}}
{{--                </button>--}}
{{--                <button onclick="refreshCurrentVisitorMessages()" class="p-2 rounded-full hover:bg-gray-100" title="Refresh current visitor messages">--}}
{{--                    <i class="fas fa-comments"></i>--}}
{{--                </button>--}}
{{--                <button onclick="testMessageDisplay()" class="p-2 rounded-full hover:bg-gray-100" title="Test message display">--}}
{{--                    <i class="fas fa-bug"></i>--}}
{{--                </button>--}}
{{--                <button onclick="testRealTimeMessage()" class="p-2 rounded-full hover:bg-gray-100" title="Test real-time message">--}}
{{--                    <i class="fas fa-bolt"></i>--}}
{{--                </button>--}}
{{--                <button onclick="checkChatState()" class="p-2 rounded-full hover:bg-gray-100" title="Check chat state">--}}
{{--                    <i class="fas fa-info-circle"></i>--}}
{{--                </button>--}}
{{--                <button class="p-2 rounded-full hover:bg-gray-100">--}}
{{--                    <i class="fas fa-ellipsis-v"></i>--}}
{{--                </button>--}}
{{--            </div>--}}
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


<script>
    let selectedVisitorId = null;
    let knownVisitors = new Set();
    let unreadMessages = new Map(); // Track unread messages per visitor
    let displayedMessageIds = new Set(); // Track displayed messages to prevent duplicates
    let visitorMessages = new Map(); // Store messages for each visitor separately
    let currentChatMessages = []; // Current chat's messages only

    document.addEventListener('DOMContentLoaded', () => {
        console.log('Admin: Connected to WebSocket: ', window.Echo);
        
        // Check for visitor ID in URL
        const urlParams = new URLSearchParams(window.location.search);
        const visitorId = urlParams.get('visitor');
        
        if (visitorId) {
            // If visitor ID is in URL, select that visitor and load their messages
            selectVisitor(visitorId);
        } else {
            // Otherwise load the active visitors list
            loadActiveVisitors();
        }

        // Listen for admin messages
        Echo.private('chat.admin')
            .listen('ChatMessageSent', (e) => {
                console.log('Admin received visitor message:', e);
                console.log('Message details:', {
                    message: e.message,
                    fromAdmin: e.fromAdmin,
                    messageId: e.messageId,
                    sessionId: e.sessionId,
                    selectedVisitorId: selectedVisitorId,
                    isMatch: selectedVisitorId === e.sessionId
                });

                // This is a visitor message, add it to the appropriate visitor's chat
                if (!e.fromAdmin && e.messageId) {
                    console.log('Processing visitor message. Message ID:', e.messageId, 'Message:', e.message);

                    // If sessionId is missing, try to find the visitor from the database
                    let targetVisitorId = e.sessionId;
                    if (!targetVisitorId) {
                        console.log('sessionId is missing, will try to find visitor from database');
                        // For now, assume it's from the currently selected visitor
                        targetVisitorId = selectedVisitorId;
                    }

                    if (targetVisitorId) {
                        console.log('Target visitor ID:', targetVisitorId);

                        // Store the message for this visitor
                        async function loadVisitorMessages(visitorId) {
                            try {
                                const response = await fetch(`/messages?session_id=${visitorId}`);
                                const messages = await response.json();
                                
                                // Store messages for this visitor
                                visitorMessages.set(visitorId, messages);
                                currentChatMessages = messages;
                                
                                // Display messages
                                displayMessages(messages);
                                
                                // Scroll to bottom of messages
                                const messagesContainer = document.getElementById('messages');
                                if (messagesContainer) {
                                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                                }
                            } catch (error) {
                                console.error('Error loading messages:', error);
                            }
                        }

                        const newMessage = {
                            id: e.messageId,
                            message: e.message,
                            from_admin: false,
                            timestamp: e.time,
                            visitor_ip: targetVisitorId
                        };

                        // Add message to visitor's message list
                        if (!visitorMessages.has(targetVisitorId)) {
                            visitorMessages.set(targetVisitorId, []);
                        }

                        const visitorMsgList = visitorMessages.get(targetVisitorId);
                        visitorMsgList.push(newMessage);
                        console.log(`Message stored for visitor ${targetVisitorId}. Total messages: ${visitorMsgList.length}`);

                        // Only display the message if this visitor's chat is currently selected
                        if (selectedVisitorId === targetVisitorId) {
                            console.log('Visitor chat is selected! Displaying message immediately');
                            displayMessageInCurrentChat(newMessage);
                        } else {
                            console.log('Visitor chat not selected. Message stored but not displayed');
                            // Mark as unread for other visitors
                            const currentUnread = unreadMessages.get(targetVisitorId) || 0;
                            unreadMessages.set(targetVisitorId, currentUnread + 1);
                            updateUnreadIndicator(targetVisitorId);
                            console.log('Marked message as unread for visitor:', targetVisitorId);
                        }

                        // If no visitor is currently selected, auto-select this one
                        if (!selectedVisitorId) {
                            console.log('No visitor selected, auto-selecting message sender:', targetVisitorId);
                            selectVisitor(targetVisitorId);
                        }
                    } else {
                        console.log('Cannot determine target visitor. sessionId:', e.sessionId, 'selectedVisitorId:', selectedVisitorId);
                    }

                    // Always refresh visitor list to show new activity
                    loadActiveVisitors();
                } else {
                    console.log('Message not processed. fromAdmin:', e.fromAdmin, 'messageId:', e.messageId);
                }
            })
            .error((error) => {
                console.error('Subscription error:', error);
            });

        // Remove the duplicate channel listener since we're handling everything in the private channel
        // Auto-refresh visitor list every 10 seconds as backup
        setInterval(() => {
            loadActiveVisitors();
        }, 10000);
    });

    async function loadActiveVisitors() {
        try {
            const response = await fetch('/visitors');
            const visitors = await response.json();
            const activeChatsContainer = document.getElementById('active-chats');
            const visitorCountSpan = document.getElementById('visitor-count');

            // Store current selection
            const currentSelected = selectedVisitorId;

            // Check for new visitors
            const newVisitors = visitors.filter(visitorId => !knownVisitors.has(visitorId));
            if (newVisitors.length > 0) {
                newVisitors.forEach(visitorId => {
                    showNotification(`New visitor ${visitorId.substring(0, 8)}... has joined the chat!`);
                });
            }

            // Update known visitors set
            knownVisitors = new Set(visitors);

            activeChatsContainer.innerHTML = '';

            visitors.forEach(visitorId => {
                const visitorElement = document.createElement('div');
                visitorElement.className = 'p-4 border-b border-gray-700 hover:bg-gray-700 cursor-pointer';
                visitorElement.setAttribute('data-visitor-id', visitorId);
                visitorElement.onclick = () => selectVisitor(visitorId);

                // Highlight currently selected visitor
                if (visitorId === currentSelected) {
                    visitorElement.classList.add('bg-gray-700');
                }

                visitorElement.innerHTML = `
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                            <span>Visitor: ${visitorId.substring(0, 8)}...</span>
                        </div>
                    </div>
                `;
                activeChatsContainer.appendChild(visitorElement);
            });

            // Update visitor count
            visitorCountSpan.textContent = visitors.length;

            // Auto-select first visitor if none is selected
            if (!currentSelected && visitors.length > 0) {
                console.log('Auto-selecting first visitor:', visitors[0]);
                selectVisitor(visitors[0]);
            }
            // If the previously selected visitor is no longer in the list, select the first available
            else if (currentSelected && !visitors.includes(currentSelected) && visitors.length > 0) {
                console.log('Previously selected visitor no longer available, selecting first visitor:', visitors[0]);
                selectVisitor(visitors[0]);
            }
        } catch (error) {
            console.error('Error loading visitors:', error);
        }
    }

    async function loadVisitorMessages(visitorId) {
        console.log('Loading messages for visitor:', visitorId);
        try {
            // Clear current messages
            const messagesContainer = document.getElementById('messages');
            messagesContainer.innerHTML = '';
            
            // Fetch messages for this visitor
            const response = await fetch(`/messages?session_id=${visitorId}`);
            const messages = await response.json();
            
            console.log('Fetched messages:', messages);
            
            // Display messages
            if (messages.length === 0) {
                messagesContainer.innerHTML = '<div class="text-center text-gray-500 text-sm mt-4">No messages yet. Start the conversation!</div>';
            } else {
                messages.forEach(message => {
                    addMessageToChat(
                        message.message, 
                        message.from_admin, 
                        message.id, 
                        message.created_at
                    );
                });
                
                // Scroll to bottom
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
            
            return messages;
        } catch (error) {
            console.error('Error loading messages:', error);
            return [];
        }
    }
    
    async function selectVisitor(visitorId) {
        console.log('Selecting visitor:', visitorId);
        selectedVisitorId = visitorId;
        
        // Update URL with visitor ID
        const newUrl = new URL(window.location);
        newUrl.searchParams.set('visitor', visitorId);
        window.history.pushState({}, '', newUrl);
        
        // Update UI to show selected visitor
        document.querySelectorAll('.visitor-item').forEach(el => {
            el.classList.remove('bg-gray-700');
            if (el.dataset.visitorId === visitorId) {
                el.classList.add('bg-gray-700');
                
                // Update chat header with visitor info
                document.getElementById('chat-header').innerHTML = `
                    <div>
                        <h2 class="text-lg font-semibold">Chat with Visitor</h2>
                        <p class="text-sm text-gray-500">${el.dataset.visitorIp || 'Unknown IP'}</p>
                    </div>
                `;
            }
        });
        
        // Load messages for this visitor
        await loadVisitorMessages(visitorId);
        
        // Clear unread count for this visitor
        unreadMessages.set(visitorId, 0);
        updateUnreadIndicator(visitorId);
    }

    function clearCurrentChat() {
        console.log('Clearing current chat...');
        const messagesEl = document.getElementById('messages');
        if (messagesEl) {
            messagesEl.innerHTML = '';
        }
        currentChatMessages = [];
        displayedMessageIds.clear();
        console.log('Current chat cleared');
    }

    function displayMessageInCurrentChat(message) {
        console.log('Displaying message in current chat:', message);

        // Only display if this message belongs to the currently selected visitor
        if (selectedVisitorId && message.visitor_ip === selectedVisitorId) {
            const messagesContainer = document.getElementById('messages');

            if (messagesContainer) {
                // Remove the "No messages yet" placeholder if it exists
                const placeholder = messagesContainer.querySelector('.text-center.text-gray-500');
                if (placeholder) {
                    placeholder.remove();
                }

                // Create and display the message
                const messageElement = createMessageElement(message.message, message.from_admin, message.id, message.timestamp);
                messagesContainer.appendChild(messageElement);
                messagesContainer.scrollTop = messagesContainer.scrollHeight;

                // Add to displayed set to prevent duplicates
                if (message.id) {
                    displayedMessageIds.add(message.id);
                }

                console.log('Message displayed in current chat successfully');
            } else {
                console.error('Messages container not found');
            }
        } else {
            console.log('Message does not belong to currently selected visitor');
        }
    }

    async function loadMessagesForVisitor(visitorId) {
        try {
            console.log('Loading messages for visitor:', visitorId);

            // Clear current chat messages
            currentChatMessages = [];
            displayedMessageIds.clear();

            const messagesEl = document.getElementById('messages');
            messagesEl.innerHTML = '';

            // First, try to load from stored visitor messages
            if (visitorMessages.has(visitorId)) {
                const storedMessages = visitorMessages.get(visitorId);
                console.log('Found stored messages for visitor:', storedMessages.length);

                if (storedMessages.length > 0) {
                    storedMessages.forEach(msg => {
                        displayMessageInCurrentChat(msg);
                        currentChatMessages.push(msg);
                    });
                    console.log('Stored messages loaded successfully');
                    return;
                }
            }

            // If no stored messages, load from database
            console.log('No stored messages, loading from database...');
            const response = await fetch('/messages');
            const messages = await response.json();

            console.log('Total messages from database:', messages.length);

            // Filter messages for this visitor
            const visitorMessagesFromDB = messages.filter(msg => msg.visitor_ip === visitorId);
            console.log('Messages for this visitor from DB:', visitorMessagesFromDB.length);

            if (visitorMessagesFromDB.length === 0) {
                messagesEl.innerHTML = '<div class="text-center text-gray-500 text-sm mt-4">No messages from this visitor yet.</div>';
            } else {
                // Store messages for this visitor
                visitorMessages.set(visitorId, visitorMessagesFromDB);

                // Display messages
                visitorMessagesFromDB.forEach(msg => {
                    displayMessageInCurrentChat(msg);
                    currentChatMessages.push(msg);
                });
            }

            console.log('Final message count:', messagesEl.children.length);
        } catch (err) {
            console.error('Failed to load messages:', err);
        }
    }

    function updateVisitorStatus(visitorId, isOnline = true) {
        const visitorElement = document.querySelector(`[data-visitor-id="${visitorId}"]`);
        if (visitorElement) {
            const statusDot = visitorElement.querySelector('.w-3.h-3');
            if (statusDot) {
                statusDot.className = `w-3 h-3 rounded-full mr-2 ${isOnline ? 'bg-green-500' : 'bg-gray-500'}`;
            }
        }
    }

    function updateUnreadIndicator(visitorId) {
        const visitorElement = document.querySelector(`[data-visitor-id="${visitorId}"]`);
        if (visitorElement) {
            const unreadCount = unreadMessages.get(visitorId) || 0;
            let badge = visitorElement.querySelector('.unread-badge');

            if (unreadCount > 0) {
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'unread-badge ml-auto bg-red-500 text-white text-xs rounded-full px-2 py-1';
                    visitorElement.querySelector('.flex').appendChild(badge);
                }
                badge.textContent = unreadCount;
            } else if (badge) {
                badge.remove();
            }
        }
    }

    function clearUnreadMessages(visitorId) {
        unreadMessages.set(visitorId, 0);
        updateUnreadIndicator(visitorId);
    }

    function refreshCurrentVisitorMessages() {
        if (selectedVisitorId) {
            console.log('Refreshing messages for current visitor:', selectedVisitorId);
            loadMessagesForVisitor(selectedVisitorId);
        }
    }

    function showNotification(message) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 bg-blue-600 text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-x-full';
        notification.textContent = message;

        // Add to body
        document.body.appendChild(notification);

        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);

        // Remove after 5 seconds
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 5000);
    }

    async function sendMessage() {
        if (!selectedVisitorId) {
            alert('Please select a visitor first');
            return;
        }

        console.log('Sending message to visitor:', selectedVisitorId);

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
                    from_admin: true,
                    session_id: selectedVisitorId
                })
            });

            if (response.ok) {
                console.log('Message sent successfully to visitor:', selectedVisitorId);

                // Store admin message in visitor's message list
                if (visitorMessages.has(selectedVisitorId)) {
                    const visitorMsgList = visitorMessages.get(selectedVisitorId);
                    const adminMessage = {
                        id: 'admin-' + Date.now(),
                        message: message,
                        from_admin: true,
                        timestamp: new Date().toISOString(),
                        visitor_ip: selectedVisitorId
                    };
                    visitorMsgList.push(adminMessage);
                    console.log('Admin message stored for visitor:', selectedVisitorId);
                }

                addMessageToChat(message, true);
                input.value = '';
                input.focus();
            } else {
                console.error('Failed to send message:', response.status, response.statusText);
            }
        } catch (error) {
            console.error('Error sending message:', error);
        }
    }

    function addMessageToChat(message, isAdmin = false, messageId = null, timestamp = null) {
        console.log('addMessageToChat called with:', { message, isAdmin, messageId, timestamp });

        const messagesContainer = document.getElementById('messages');
        console.log('Messages container:', messagesContainer);

        // If message with this ID already exists, don't add it again
        if (messageId && displayedMessageIds.has(messageId)) {
            console.log('Message already displayed, skipping:', messageId);
            return false; // Indicate failure
        }

        if (messageId) {
            displayedMessageIds.add(messageId);
            console.log('Added message ID to displayed set:', messageId);
        }

        // Remove the "No messages yet" placeholder if it exists
        const placeholder = messagesContainer.querySelector('.text-center.text-gray-500');
        if (placeholder) {
            placeholder.remove();
        }

        const messageElement = createMessageElement(message, isAdmin, messageId, timestamp);
        console.log('Created message element:', messageElement);

        messagesContainer.appendChild(messageElement);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
        console.log('Message added to chat successfully');
        return true; // Indicate success
    }

    function testMessageDisplay() {
        console.log('Testing message display...');
        // Simulate adding a message to the chat
        addMessageToChat('This is a test message from the admin.', true, 'test-message-id', new Date().toISOString());
        console.log('Test message added.');
    }

    function testRealTimeMessage() {
        console.log('Testing real-time message display...');
        if (selectedVisitorId) {
            console.log('Selected visitor:', selectedVisitorId);

            // Simulate receiving a real-time message
            const testEvent = {
                message: 'This is a test real-time message',
                fromAdmin: false,
                messageId: 'test-realtime-' + Date.now(),
                sessionId: selectedVisitorId,
                time: new Date().toISOString()
            };

            console.log('Simulating real-time message event:', testEvent);

            // Manually trigger the message processing
            const messageElement = createMessageElement(testEvent.message, false, testEvent.messageId, testEvent.time);
            const messagesContainer = document.getElementById('messages');

            if (messagesContainer) {
                // Remove placeholder if exists
                const placeholder = messagesContainer.querySelector('.text-center.text-gray-500');
                if (placeholder) {
                    placeholder.remove();
                }

                messagesContainer.appendChild(messageElement);
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
                console.log('Test real-time message added successfully');

                // Add to displayed set
                displayedMessageIds.add(testEvent.messageId);
            } else {
                console.error('Messages container not found for test');
            }
        } else {
            console.log('No visitor selected for test');
        }
    }

    function checkChatState() {
        console.log('=== Chat State Debug ===');
        console.log('Selected Visitor ID:', selectedVisitorId);
        console.log('Messages Container:', document.getElementById('messages'));
        console.log('Displayed Message IDs:', Array.from(displayedMessageIds));
        console.log('Current Messages Count:', document.getElementById('messages').children.length);
        console.log('========================');
    }

    function createMessageElement(message, isAdmin = false, messageId = null, timestamp = null) {
        const messageElement = document.createElement('div');
        messageElement.className = `flex ${isAdmin ? 'justify-end' : 'justify-start'} mb-4`;

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

        return messageElement;
    }
</script>
</body>
</html>
