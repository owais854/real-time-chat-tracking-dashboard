<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Chat Dashboard</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-id" content="{{ $userId }}">

    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

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
            <p class="text-sm text-gray-400">All Visitors: <span id="visitor-count">0</span></p>
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

            <!-- Agents Dropdown -->
            <div class="relative" id="agents-dropdown">
                <button id="agents-dropdown-button" class="flex items-center space-x-2 bg-blue-50 hover:bg-blue-100 text-blue-700 px-4 py-2 rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-300">
                    <span>Agents</span>
                    <svg id="dropdown-arrow" class="w-4 h-4 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <div id="agents-dropdown-menu" class="absolute right-0 mt-2 w-56 bg-white rounded-md shadow-lg py-1 z-50 hidden">
                    <div class="px-4 py-2 text-sm text-gray-700 border-b border-gray-100">
                        <p class="font-medium">Agents</p>
                    </div>
                    <div id="agents-list" class="max-h-60 overflow-y-auto">
                        <!-- Agents will be loaded here via JavaScript -->
                        <div class="px-4 py-2 text-sm text-gray-500">Loading agents...</div>
                    </div>
                </div>
            </div>
{{--                <button onclick="pruneVisitorsNow()" class="p-2 rounded-full hover:bg-gray-100" title="Prune offline visitors now">--}}
{{--                    <i class="fas fa-user-times text-red-500"></i>--}}
{{--                </button>--}}
{{--                <button onclick="debugUnreadCounts()" class="p-2 rounded-full hover:bg-gray-100" title="Debug unread counts">--}}
{{--                    <i class="fas fa-bug text-blue-500"></i>--}}
{{--                </button>--}}
{{--            </div>--}}
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
                <input type="file" id="admin-file-input" accept="image/*,.pdf" multiple class="hidden" onchange="handleAdminFileSelect()">
                <!-- <button onclick="document.getElementById('admin-file-input').click()"
                        class="p-2 text-gray-500 hover:text-gray-700" title="Attach files">
                    <i class="fas fa-paperclip"></i>
                </button> -->
                <input type="text" id="message-input"
                       placeholder="Type a message..."
                       class="flex-1 px-4 py-2 border rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500"
                       onkeypress="if(event.key === 'Enter') sendMessage()">
                <button onclick="sendMessage()"
                        class="bg-blue-600 text-white p-2 rounded-full hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
            <div id="admin-file-preview" class="mt-2 hidden">
                <div id="admin-file-list" class="space-y-2">
                    <!-- Files will be added here dynamically -->
                </div>
                <button onclick="clearAllAdminFiles()" class="mt-2 text-sm text-red-600 hover:text-red-800">
                    <i class="fas fa-times mr-1"></i>Clear All Files
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let selectedVisitorId = null;
    let knownVisitors = new Set();
    let unreadMessages = new Map(); // Track unread messages per visitor
    let displayedMessageIds = new Set(); // Track displayed messages to prevent duplicates
    let visitorMessages = new Map(); // Store messages for each visitor separately
    let currentChatMessages = []; // Current chat's messages only
    let visitors = {}; // Store visitor data for message handling
    let selectedAdminFiles = []; // Track selected files for admin

    // Function to toggle dropdown
    function toggleDropdown() {
        const menu = document.getElementById('agents-dropdown-menu');
        const arrow = document.getElementById('dropdown-arrow');

        if (menu.classList.contains('hidden')) {
            menu.classList.remove('hidden');
            arrow.classList.add('rotate-180');
            // Close when clicking outside
            document.addEventListener('click', closeDropdownOnClickOutside);
        } else {
            closeDropdown();
        }
    }

    // Function to close dropdown
    function closeDropdown() {
        const menu = document.getElementById('agents-dropdown-menu');
        const arrow = document.getElementById('dropdown-arrow');

        menu.classList.add('hidden');
        arrow.classList.remove('rotate-180');
        document.removeEventListener('click', closeDropdownOnClickOutside);
    }

    // Close dropdown when clicking outside
    function closeDropdownOnClickOutside(event) {
        const dropdown = document.getElementById('agents-dropdown');
        const button = document.getElementById('agents-dropdown-button');

        if (!dropdown.contains(event.target) && event.target !== button) {
            closeDropdown();
        }
    }

    // Function to initialize dropdown events
    function initDropdown() {
        const button = document.getElementById('agents-dropdown-button');
        if (button) {
            // Remove any existing event listeners to prevent duplicates
            const newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);

            // Add click event to the new button
            newButton.addEventListener('click', (e) => {
                e.stopPropagation();
                toggleDropdown();
            });
        }
    }

    // Function to transfer chat to another agent
    async function transferChat(agentId, agentName) {
        if (!selectedVisitorId) {
            showNotification('Please select a visitor first', 'error');
            return;
        }

        try {
            const response = await fetch('/agent/transfer', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    session_id: selectedVisitorId,
                    new_agent_id: agentId
                })
            });

            if (!response.ok) {
                throw new Error(`Server error: ${response.status}`);
            }

            const result = await response.json();

            if (result.status === 'ok') {
                showNotification(`Chat transferred to ${agentName} successfully`, 'success');
                // Just show notification, don't clear messages
                const messagesContainer = document.getElementById('messages');
                if (messagesContainer) {
                    const transferMessage = document.createElement('div');
                    transferMessage.className = 'text-center text-sm text-gray-600 mb-4 p-2';
                    transferMessage.textContent = `✓ Chat transferred to ${agentName} ${new Date().toLocaleTimeString()}`;
                    messagesContainer.appendChild(transferMessage);
                }
            } else {
                throw new Error(result.message || 'Failed to transfer chat');
            }
        } catch (error) {
            console.error('Error transferring chat:', error);
            showNotification(error.message || 'Failed to transfer chat', 'error');
        }
    }

    // Function to load agents into the dropdown
    async function loadAgents() {
        try {
            const response = await fetch('/agents');
            const agents = await response.json();

            const agentsList = document.getElementById('agents-list');

            if (agents.length === 0) {
                agentsList.innerHTML = '<div class="px-4 py-2 text-sm text-gray-500">No agents found</div>';
                return;
            }

            // Clear loading message
            agentsList.innerHTML = '';

            // Add each agent to the dropdown
            agents.forEach(agent => {
                const agentElement = document.createElement('div');
                agentElement.className = 'px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 cursor-pointer flex items-center';
                agentElement.innerHTML = `
<!--                    <span class="w-2 h-2 rounded-full bg-green-500 mr-2"></span>-->
                    <span>${agent.name}</span>
<!--                    <span class="text-xs text-gray-500 ml-auto">${agent.email}</span>-->
                `;

                // Add click handler
                agentElement.addEventListener('click', (e) => {
                    e.stopPropagation();
                    console.log('Agent selected:', agent);

                    // Show confirmation dialog
                    if (confirm(`Transfer chat to ${agent.name}?`)) {
                        transferChat(agent.id, agent.name);
                    }

                    // Close dropdown after selection
                    closeDropdown();
                });

                agentsList.appendChild(agentElement);
            });

            // Re-initialize dropdown events after updating agents
            initDropdown();

        } catch (error) {
            console.error('Error loading agents:', error);
            const agentsList = document.getElementById('agents-list');
            agentsList.innerHTML = '<div class="px-4 py-2 text-sm text-red-500">Error loading agents</div>';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        console.log('Admin-Chat: DOM loaded, initializing real-time tracking...');

        // Load agents into the dropdown
        loadAgents();

        // Wait for Echo to be available
        function waitForEcho() {
            if (window.Echo) {
                console.log('Admin-Chat: Echo is available, joining presence channel...');

                // Listen to visitor online/offline events
                window.Echo.channel('visitors.public')
                    .listen('visitor.online', (e) => {
                        console.log('Admin-Chat: Visitor came online:', e);
                        if (e.visitor && e.visitor.ip_address) {
                            showNotification(`Visitor ${e.visitor.ip_address} is now online`);
                            // Update visitor status in real-time
                            updateVisitorStatusInSidebar(e.visitor.ip_address, true);
                            // Immediately update the visitor status in the global visitors object
                            if (visitors[e.visitor.ip_address]) {
                                visitors[e.visitor.ip_address].is_active = true;
                                visitors[e.visitor.ip_address].last_activity = new Date().toISOString();
                            }
                            // Update active visitors count
                            updateActiveVisitorsCount();
                            // Refresh the list after a short delay
                            setTimeout(() => loadActiveVisitors(), 500);
                        }
                    })
                    .listen('visitor.offline', (e) => {
                        console.log('Admin-Chat: Visitor went offline:', e);
                        if (e.visitor && e.visitor.ip_address) {
                            showNotification(`Visitor ${e.visitor.ip_address} went offline`);
                            // Update visitor status in real-time
                            updateVisitorStatusInSidebar(e.visitor.ip_address, false);
                            // Immediately update the visitor status in the global visitors object
                            if (visitors[e.visitor.ip_address]) {
                                visitors[e.visitor.ip_address].is_active = false;
                                visitors[e.visitor.ip_address].last_activity = new Date().toISOString();
                            }
                            // Update active visitors count
                            updateActiveVisitorsCount();
                            // Refresh the list after a short delay
                            setTimeout(() => loadActiveVisitors(), 500);
                        }
                    })
                    .error((error) => {
                        console.error('Admin-Chat: Public channel error:', error);
                    });
            } else {
                console.log('Admin-Chat: Echo not ready yet, retrying in 500ms...');
                setTimeout(waitForEcho, 500);
            }
        }

        // Start waiting for Echo
        waitForEcho();

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
                console.log('Admin received message:', e);
                console.log('Message details:', {
                    message: e.message,
                    fromAdmin: e.fromAdmin,
                    messageId: e.messageId,
                    sessionId: e.sessionId,
                    files: e.files,
                    file: e.file,
                    selectedVisitorId: selectedVisitorId,
                    isMatch: selectedVisitorId === e.sessionId
                });

                // Process visitor messages (fromAdmin = false)
                if (!e.fromAdmin && e.messageId) {
                    console.log('Processing visitor message. Message ID:', e.messageId, 'Message:', e.message);

                    // Find the visitor ID that matches this session_id
                    let targetVisitorId = null;

                    console.log('Looking for visitor with session_id:', e.sessionId);
                    console.log('Available visitors:', Object.keys(visitors));

                    // First, try to find by session_id in our visitor list
                    for (const [visitorId, visitor] of Object.entries(visitors)) {
                        if (visitor.session_id === e.sessionId || visitorId === e.sessionId) {
                            targetVisitorId = visitorId;
                            console.log('Found visitor by session_id:', targetVisitorId);
                            break;
                        }
                    }

                    // If not found, try to find by IP address (for IP-based visitors)
                    if (!targetVisitorId) {
                        // Try to find visitor by IP address
                        for (const [visitorId, visitor] of Object.entries(visitors)) {
                            if (visitor.ip_address && visitor.sessions && visitor.sessions.includes(e.sessionId)) {
                                targetVisitorId = visitorId;
                                console.log('Found visitor by IP address:', targetVisitorId);
                                break;
                            }
                        }
                    }

                    // If still not found, try to find a visitor from the same IP by checking message history
                    if (!targetVisitorId) {
                        // Reload visitors to get the latest data
                        console.log('Visitor not found in current list, reloading...');
                        loadActiveVisitors().then(() => {
                            // Try again after reload
                            for (const [visitorId, visitor] of Object.entries(visitors)) {
                                if (visitor.session_id === e.sessionId || visitorId === e.sessionId) {
                                    targetVisitorId = visitorId;
                                    console.log('Found visitor after reload:', targetVisitorId);

                                    // Now process the message with the found visitor
                                    processMessageForVisitor(e, targetVisitorId);
                                    break;
                                }
                            }
                        });
                        return; // Exit early since we're handling this in the reload callback
                    }

                    // If still not found, use the session_id as fallback
                    if (!targetVisitorId) {
                        targetVisitorId = e.sessionId;
                        console.log('Using session_id as fallback:', targetVisitorId);
                    }

                    if (targetVisitorId) {
                        processMessageForVisitor(e, targetVisitorId);
                    } else {
                        console.log('Cannot determine target visitor. sessionId:', e.sessionId, 'selectedVisitorId:', selectedVisitorId);
                    }

                    // Refresh visitor list to show new activity (but not too frequently)
                    setTimeout(() => {
                        loadActiveVisitors();
                    }, 1000);
                } else if (e.fromAdmin && e.messageId) {
                    // Process admin messages (fromAdmin = true) - these are admin's own messages
                    console.log('Processing admin message. Message ID:', e.messageId, 'Message:', e.message);
                    console.log('Admin message sessionId:', e.sessionId, 'Selected visitor:', selectedVisitorId);

                    // Check if this admin message is for the currently selected visitor
                    let isForCurrentVisitor = false;

                    // Check if the sessionId matches the selected visitor's session_id
                    if (visitors[selectedVisitorId] && visitors[selectedVisitorId].session_id === e.sessionId) {
                        isForCurrentVisitor = true;
                        console.log('Admin message sessionId matches selected visitor session_id');
                    }
                    // Also check if the sessionId matches the selected visitor ID directly (fallback)
                    else if (selectedVisitorId === e.sessionId) {
                        isForCurrentVisitor = true;
                        console.log('Admin message sessionId matches selected visitor ID directly');
                    }

                    if (isForCurrentVisitor) {
                        console.log('Admin message is for currently selected visitor, displaying immediately');

                        // Handle files data
                        let messageFiles = null;
                        if (e.files && Array.isArray(e.files) && e.files.length > 0) {
                            messageFiles = e.files;
                        } else if (e.file) {
                            messageFiles = [e.file];
                        }

                        // Add message directly to chat
                        addMessageToChat(e.message, true, e.messageId, e.time, messageFiles);

                        // Also store in visitor messages for consistency
                        if (!visitorMessages.has(selectedVisitorId)) {
                            visitorMessages.set(selectedVisitorId, []);
                        }

                        const newMessage = {
                            id: e.messageId,
                            message: e.message,
                            from_admin: true,
                            timestamp: e.time,
                            visitor_ip: selectedVisitorId,
                            files: messageFiles
                        };

                        visitorMessages.get(selectedVisitorId).push(newMessage);
                        console.log('Admin message stored and displayed for visitor:', selectedVisitorId);
                    } else {
                        console.log('Admin message is for different visitor, not displaying');
                        console.log('Available visitors:', Object.keys(visitors));
                        console.log('Selected visitor data:', visitors[selectedVisitorId]);
                    }
                } else {
                    console.log('Message not processed. fromAdmin:', e.fromAdmin, 'messageId:', e.messageId);
                }
            })
            .error((error) => {
                console.error('Subscription error:', error);
            });

        // Remove the duplicate channel listener since we're handling everything in the private channel
        // Auto-refresh visitor list every 10 seconds for more responsive updates
        setInterval(() => {
            loadActiveVisitors();
        }, 10000);

        // Listen for chat transfer events
        Echo.channel('agents')
            .listen('VisitorTransferred', (data) => {
                console.log('Received transfer event:', data);

                // Get current user ID from meta tag or data attribute
                const currentUserId = document.querySelector('meta[name="user-id"]')?.content;

                // If this transfer is for the current user
                if (data.new_agent_id.toString() === currentUserId) {
                    // Show SweetAlert to accept/reject the transfer
                    Swal.fire({
                        title: 'Chat Transfer Request',
                        html: `A chat has been transferred to you. Would you like to accept it?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Accept',
                        cancelButtonText: 'Reject',
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // If accepted, load the chat
                            window.location.href = `/admin/chat?visitor=${encodeURIComponent(data.session_id)}`;
                        } else {
                            // If rejected, notify the system (optional)
                            console.log('Chat transfer rejected by user');
                        }
                    });
                }
            });
    });

    function processMessageForVisitor(e, targetVisitorId) {
        console.log('Processing message for visitor:', targetVisitorId);
        console.log('Available visitors for mapping:', Object.keys(visitors));

        // Find the IP address for this visitor to use for unread tracking
        let visitorIpForUnread = targetVisitorId;
        for (const [visitorId, visitor] of Object.entries(visitors)) {
            console.log(`Checking visitor ${visitorId}:`, {
                session_id: visitor.session_id,
                ip_address: visitor.ip_address,
                targetVisitorId: targetVisitorId,
                matches: visitor.session_id === targetVisitorId || visitorId === targetVisitorId
            });

            if (visitor.session_id === targetVisitorId || visitorId === targetVisitorId) {
                visitorIpForUnread = visitor.ip_address || visitorId;
                console.log('Found IP for unread tracking:', visitorIpForUnread);
                break;
            }
        }

        // Handle files data more reliably
        let messageFiles = null;
        if (e.files && Array.isArray(e.files) && e.files.length > 0) {
            messageFiles = e.files;
        } else if (e.file) {
            messageFiles = [e.file];
        }

        const newMessage = {
            id: e.messageId,
            message: e.message,
            from_admin: e.fromAdmin || false,
            timestamp: e.time,
            visitor_ip: visitorIpForUnread,
            files: messageFiles
        };

        // Add message to visitor's message list
        if (!visitorMessages.has(targetVisitorId)) {
            visitorMessages.set(targetVisitorId, []);
        }

        const visitorMsgList = visitorMessages.get(targetVisitorId);
        visitorMsgList.push(newMessage);
        console.log(`Message stored for visitor ${targetVisitorId}. Total messages: ${visitorMsgList.length}`);

        // Only display the message if this visitor's chat is currently selected
        if (selectedVisitorId === visitorIpForUnread) {
            console.log('Visitor chat is selected! Displaying message immediately');
            displayMessageInCurrentChat(newMessage);
        } else {
            console.log('Visitor chat not selected. Message stored but not displayed');
            // Mark as unread for other visitors using IP address
            const currentUnread = unreadMessages.get(visitorIpForUnread) || 0;
            unreadMessages.set(visitorIpForUnread, currentUnread + 1);
            console.log('Marked message as unread for visitor IP:', visitorIpForUnread, 'Unread count:', currentUnread + 1);

            // Update the unread indicator immediately using IP address
            updateUnreadIndicator(visitorIpForUnread);

            // Also update the sidebar display
            setTimeout(() => {
                loadActiveVisitors();
            }, 100);
        }

        // If no visitor is currently selected, auto-select this one
        if (!selectedVisitorId) {
            console.log('No visitor selected, auto-selecting message sender:', visitorIpForUnread);
            selectVisitor(visitorIpForUnread);
        }
    }

    async function loadActiveVisitors() {
        try {
            console.log('Loading all visitors...');

            // First, trigger pruning to mark offline visitors
            try {
                await fetch('/admin/visitors/prune-now', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
            } catch (pruneError) {
                console.log('Pruning failed, continuing with visitor load:', pruneError);
            }

            const response = await fetch('/admin/visitors/all');
            console.log('Response status:', response.status);
            const visitorsArray = await response.json();
            console.log('Visitors array:', visitorsArray);
            const activeChatsContainer = document.getElementById('active-chats');
            const visitorCountSpan = document.getElementById('visitor-count');

            if (!activeChatsContainer || !visitorCountSpan) {
                console.error('Required DOM elements not found!');
                return;
            }

            // Store current selection and unread counts
            const currentSelected = selectedVisitorId;
            const currentUnreadMessages = new Map(unreadMessages); // Preserve unread counts

            // Remove duplicates by IP address - keep only the most recent one
            const uniqueVisitors = [];
            const seenIPs = new Set();

            // Sort visitors by last_activity to get the most recent one for each IP
            visitorsArray.sort((a, b) => {
                const aTime = new Date(a.last_activity || a.created_at || 0);
                const bTime = new Date(b.last_activity || b.created_at || 0);
                return bTime - aTime; // Most recent first
            });

            visitorsArray.forEach(visitor => {
                const ip = visitor.ip_address;
                if (ip && !seenIPs.has(ip)) {
                    seenIPs.add(ip);
                    uniqueVisitors.push(visitor);
                }
            });

            console.log('Unique visitors after deduplication:', uniqueVisitors.length);

            // Update global visitors object for message handling
            visitors = {};
            uniqueVisitors.forEach(visitorData => {
                const visitorId = visitorData.ip_address || visitorData.id;
                visitors[visitorId] = visitorData;
            });

            // Clear and render visitors
            activeChatsContainer.innerHTML = '';

            uniqueVisitors.forEach((visitor, index) => {
                const visitorId = visitor.ip_address || visitor.id;
                const visitorElement = document.createElement('div');
                visitorElement.className = 'p-4 border-b border-gray-700 hover:bg-gray-700 cursor-pointer visitor-item';
                visitorElement.setAttribute('data-visitor-id', visitorId);
                visitorElement.setAttribute('data-visitor-ip', visitor.ip_address);
                visitorElement.onclick = () => selectVisitor(visitorId);

                // Highlight currently selected visitor
                if (visitorId === currentSelected) {
                    visitorElement.classList.add('bg-gray-700');
                }

                // Check if visitor is active (from database is_active field or recent activity)
                const isActive = visitor.is_active === true ||
                    (visitor.last_activity && new Date(visitor.last_activity) > new Date(Date.now() - 60000)); // 1 minute threshold

                const unreadCount = currentUnreadMessages.get(visitorId) || 0;
                visitorElement.innerHTML = `
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-3 h-3 ${isActive ? 'bg-green-500' : 'bg-gray-400'} rounded-full mr-2"></div>
                            <div>
                                <div class="text-sm font-medium">${visitor.ip_address || 'Unknown'}</div>
                                <div class="text-xs text-gray-400">${visitor.device_type || 'Unknown'} - ${visitor.browser || 'Unknown'}</div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            ${unreadCount > 0 ? `<span class="unread-badge bg-red-500 text-white text-xs rounded-full px-2 py-1">${unreadCount}</span>` : ''}
                            <div class="text-xs ${isActive ? 'text-green-600' : 'text-gray-500'}">
                                ${isActive ? 'Online' : 'Offline'}
                            </div>
                        </div>
                    </div>
                `;
                activeChatsContainer.appendChild(visitorElement);
            });

            // Update visitor count
            visitorCountSpan.textContent = uniqueVisitors.length;

            // Also update active visitors count
            updateActiveVisitorsCount();

            // If no visitors, show a message
            if (uniqueVisitors.length === 0) {
                activeChatsContainer.innerHTML = `
                    <div class="p-4 text-center text-gray-400">
                        <i class="fas fa-users text-2xl mb-2"></i>
                        <p>No visitors found</p>
                        <p class="text-sm">Visitors will appear here when they visit</p>
                    </div>
                `;
            }

            // Auto-select first visitor if none is selected
            if (!currentSelected && uniqueVisitors.length > 0) {
                const firstVisitorId = uniqueVisitors[0].ip_address || uniqueVisitors[0].id;
                console.log('Auto-selecting first visitor:', firstVisitorId);
                selectVisitor(firstVisitorId);
            }
        } catch (error) {
            console.error('Error loading visitors:', error);
        }
    }

    async function loadVisitorMessages(visitorId) {
        console.log('Loading messages for visitor:', visitorId);
        try {
            // Clear current messages and displayed message IDs
            const messagesContainer = document.getElementById('messages');
            messagesContainer.innerHTML = '';
            displayedMessageIds.clear(); // Clear displayed message IDs to prevent duplicates

            // Get messages by visitor IP address
            const response = await fetch(`/messages?visitor_ip=${visitorId}`);
            const messages = await response.json();

            console.log('Fetched messages:', messages);

            // Display messages
            if (messages.length === 0) {
                messagesContainer.innerHTML = '<div class="text-center text-gray-500 text-sm mt-4">No messages yet. Start the conversation!</div>';
            } else {
                // Sort messages by creation time
                messages.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));

                messages.forEach(message => {
                    // Handle backward compatibility - use files if available, otherwise use file
                    const files = message.files || (message.file ? [message.file] : null);
                    addMessageToChat(
                        message.message,
                        message.from_admin,
                        message.id,
                        message.created_at,
                        files
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

                // Update chat header with visitor info (preserve agents dropdown)
                const chatHeaderLeft = document.createElement('div');
                chatHeaderLeft.className = 'flex items-center space-x-4';
                chatHeaderLeft.innerHTML = `
                    <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-800 transition-colors duration-200" title="Back to Dashboard">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <div>
                        <h2 class="text-lg font-semibold">Chat with Visitor</h2>
                        <p class="text-sm text-gray-500">${el.dataset.visitorIp || 'Unknown IP'}</p>
                    </div>
                `;

                // Get the agents dropdown container
                const agentsDropdown = document.getElementById('agents-dropdown');

                // Clear the chat header but preserve the agents dropdown
                const chatHeader = document.getElementById('chat-header');
                chatHeader.innerHTML = '';
                chatHeader.appendChild(chatHeaderLeft);
                chatHeader.appendChild(agentsDropdown);

                // Re-initialize the dropdown after updating the DOM
                initDropdown();
            }
        });

        // Load messages for this visitor
        await loadVisitorMessages(visitorId);

        // Clear unread count for this visitor
        unreadMessages.set(visitorId, 0);
        updateUnreadIndicator(visitorId);
        console.log('Cleared unread messages for visitor:', visitorId);
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
                const messageElement = createMessageElement(message.message, message.from_admin, message.id, message.timestamp, message.files);
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

    function updateOnlineVisitors(users) {
        // Update visitor count in header
        const visitorCountSpan = document.getElementById('visitor-count');
        if (visitorCountSpan) {
            visitorCountSpan.textContent = users.length;
        }

        // Update status indicators for each visitor
        users.forEach(user => {
            if (user.visitor && user.visitor.session_id) {
                updateVisitorStatus(user.visitor.session_id, true);
            }
        });
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

    function updateVisitorStatusInSidebar(visitorIp, isOnline = true) {
        console.log('Updating visitor status in sidebar:', visitorIp, isOnline);
        const visitorElement = document.querySelector(`[data-visitor-id="${visitorIp}"]`);
        if (visitorElement) {
            // Update status dot
            const statusDot = visitorElement.querySelector('.w-3.h-3');
            if (statusDot) {
                statusDot.className = `w-3 h-3 ${isOnline ? 'bg-green-500' : 'bg-gray-400'} rounded-full mr-2`;
            }

            // Update status text
            const statusText = visitorElement.querySelector('.text-xs');
            if (statusText) {
                statusText.className = `text-xs ${isOnline ? 'text-green-600' : 'text-gray-500'}`;
                statusText.textContent = isOnline ? 'Online' : 'Offline';
            }

            // Also update the global visitors object
            if (visitors[visitorIp]) {
                visitors[visitorIp].is_active = isOnline;
                visitors[visitorIp].last_activity = new Date().toISOString();
            }

            console.log('Visitor status updated successfully');
        } else {
            console.log('Visitor element not found for IP:', visitorIp);
        }
    }

    // Function to update active visitors count
    function updateActiveVisitorsCount() {
        console.log('Admin-Chat: Updating active visitors count...');

        // Count active visitors from the global visitors object
        const activeVisitors = Object.values(visitors).filter(visitor =>
            visitor.is_active === true ||
            (visitor.last_activity && new Date(visitor.last_activity) > new Date(Date.now() - 60000))
        );

        console.log('Admin-Chat: Active visitors count:', activeVisitors.length);

        // Update the visitor count in the header
        const visitorCountSpan = document.getElementById('visitor-count');
        if (visitorCountSpan) {
            visitorCountSpan.textContent = activeVisitors.length;
            console.log('Admin-Chat: Updated visitor count to:', activeVisitors.length);
        }

        // Also update any other visitor count elements on the page
        const visitorCountElements = document.querySelectorAll('[data-visitor-count]');
        visitorCountElements.forEach(element => {
            element.textContent = activeVisitors.length;
        });
    }

    function updateUnreadIndicator(visitorId) {
        console.log('Updating unread indicator for visitor:', visitorId);
        const visitorElement = document.querySelector(`[data-visitor-id="${visitorId}"]`);
        if (visitorElement) {
            const unreadCount = unreadMessages.get(visitorId) || 0;
            console.log('Unread count for visitor', visitorId, ':', unreadCount);

            // Update existing badge or create new one
            const existingBadge = visitorElement.querySelector('.unread-badge');
            if (unreadCount > 0) {
                if (existingBadge) {
                    // Update existing badge
                    existingBadge.textContent = unreadCount;
                    console.log('Updated unread badge for visitor:', visitorId, 'Count:', unreadCount);
                } else {
                    // Create new badge
                    const flexContainer = visitorElement.querySelector('.flex.items-center.space-x-2');
                    if (flexContainer) {
                        const badge = document.createElement('span');
                        badge.className = 'unread-badge bg-red-500 text-white text-xs rounded-full px-2 py-1';
                        badge.textContent = unreadCount;
                        flexContainer.insertBefore(badge, flexContainer.firstChild);
                        console.log('Added unread badge for visitor:', visitorId, 'Count:', unreadCount);
                    }
                }
            } else {
                // Remove badge if count is 0
                if (existingBadge) {
                    existingBadge.remove();
                    console.log('Removed unread badge for visitor:', visitorId);
                }
            }
        } else {
            console.log('Visitor element not found for ID:', visitorId);
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

    function showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');

        // Set base classes
        notification.className = 'fixed top-4 right-4 text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-x-full flex items-center';

        // Set color based on type
        let bgColor = 'bg-blue-600'; // default info
        if (type === 'success') {
            bgColor = 'bg-green-600';
        } else if (type === 'error') {
            bgColor = 'bg-red-600';
        } else if (type === 'warning') {
            bgColor = 'bg-yellow-600';
        }

        notification.classList.add(bgColor);

        // Add icon based on type
        let icon = '';
        if (type === 'success') {
            icon = '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
        } else if (type === 'error') {
            icon = '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
        } else if (type === 'warning') {
            icon = '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>';
        } else {
            icon = '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
        }

        notification.innerHTML = `${icon}<span>${message}</span>`;

        // Add to DOM
        document.body.appendChild(notification);

        // Trigger reflow to ensure the initial state is applied
        void notification.offsetWidth;

        // Slide in
        notification.classList.remove('translate-x-full');
        notification.classList.add('translate-x-0');

        // Auto remove after 5 seconds (longer for important messages)
        const duration = type === 'error' ? 5000 : 3000;

        setTimeout(() => {
            notification.classList.remove('translate-x-0');
            notification.classList.add('translate-x-full');

            // Remove from DOM after animation
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, duration);

        // Return the notification element in case we want to manually remove it
        return notification;
    }

    async function sendMessage() {
        if (!selectedVisitorId) {
            alert('Please select a visitor first');
            return;
        }

        console.log('Sending message to visitor:', selectedVisitorId);

        const input = document.getElementById('message-input');
        const message = input.value.trim();
        if (!message && selectedAdminFiles.length === 0) return;

        // Get the correct session ID for this visitor
        let sessionId = selectedVisitorId;
        if (visitors[selectedVisitorId] && visitors[selectedVisitorId].session_id) {
            sessionId = visitors[selectedVisitorId].session_id;
            console.log('Using visitor session_id:', sessionId, 'instead of IP:', selectedVisitorId);
        } else {
            console.log('No session_id found for visitor, using IP as fallback:', selectedVisitorId);
        }

        try {
            const formData = new FormData();
            formData.append('message', message || '');
            formData.append('from_admin', 'true');
            formData.append('session_id', sessionId);
            console.log('Admin sending message with session_id:', sessionId);
            if (selectedAdminFiles.length > 0) {
                selectedAdminFiles.forEach((file, index) => {
                    formData.append('files[]', file);
                });
            }

            const response = await fetch('/messages', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            if (response.ok) {
                const data = await response.json();
                console.log('Message sent successfully to visitor:', selectedVisitorId);
                console.log('Response data:', data);

                // Clear input and files - the message will be added via real-time listener
                input.value = '';
                clearAllAdminFiles();
                input.focus();

                console.log('Admin message will be displayed via real-time listener');
            } else {
                const errorData = await response.json().catch(() => ({}));
                console.error('Failed to send message:', response.status, response.statusText, errorData);
                alert('Failed to send message: ' + (errorData.error || response.statusText));
            }
        } catch (error) {
            console.error('Error sending message:', error);
        }
    }

    function addMessageToChat(message, isAdmin = false, messageId = null, timestamp = null, files = null) {
        console.log('addMessageToChat called with:', { message, isAdmin, messageId, timestamp, files });

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

        const messageElement = createMessageElement(message, isAdmin, messageId, timestamp, files);
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

    function createMessageElement(message, isAdmin = false, messageId = null, timestamp = null, files = null) {
        const messageElement = document.createElement('div');
        messageElement.className = `flex ${isAdmin ? 'justify-end' : 'justify-start'} mb-4`;

        if (messageId) {
            messageElement.setAttribute('data-message-id', messageId);
        }

        const time = timestamp ? new Date(timestamp).toLocaleTimeString() : new Date().toLocaleTimeString();

        // Handle multiple files display
        let fileHtml = '';
        console.log('createMessageElement - files:', files);
        if (files && files.length > 0) {
            console.log('Processing files for display:', files.length, files);
            fileHtml = '<div class="mt-2 space-y-2">';
            files.forEach((file, index) => {
                console.log(`Processing file ${index + 1}:`, file);
                const fileExtension = file.split('.').pop().toLowerCase();
                const isImage = ['jpg', 'jpeg', 'png', 'gif'].includes(fileExtension);
                const isPdf = fileExtension === 'pdf';
                console.log(`File ${index + 1} - Extension: ${fileExtension}, IsImage: ${isImage}, IsPdf: ${isPdf}`);

                if (isImage) {
                    const imageUrl = `/storage/${file}`;
                    console.log(`Generating image HTML for: ${file}, URL: ${imageUrl}`);
                    fileHtml += `<div>
                        <img src="${imageUrl}" alt="Uploaded image" class="max-w-full h-auto rounded-lg cursor-pointer border" style="max-height: 200px;" onclick="window.open('${imageUrl}', '_blank')">
                    </div>`;
                } else if (isPdf) {
                    fileHtml += `<div class="p-3 bg-white rounded-lg border border-gray-300">
                        <a href="/storage/${file}" target="_blank" class="inline-flex items-center text-blue-600 hover:text-blue-800">
                            <i class="fas fa-file-pdf mr-2 text-red-500 text-lg"></i>
                            <span class="font-medium">View PDF Document</span>
                        </a>
                    </div>`;
                } else {
                    fileHtml += `<div class="p-3 bg-white rounded-lg border border-gray-300">
                        <a href="/storage/${file}" target="_blank" class="inline-flex items-center text-blue-600 hover:text-blue-800">
                            <i class="fas fa-file mr-2 text-lg"></i>
                            <span class="font-medium">Download File</span>
                        </a>
                    </div>`;
                }
            });
            fileHtml += '</div>';
        }

        const messageText = message || (files && files.length > 0 ? 'Sent files' : '');
        const maxWidth = files && files.length > 0 ? 'max-w-md' : 'max-w-xs lg:max-w-md';

        console.log('Final HTML being generated:', {
            messageText,
            fileHtml,
            maxWidth,
            isAdmin
        });

        // Test if images are accessible
        if (files && files.length > 0) {
            files.forEach((file, index) => {
                const imageUrl = `/storage/${file}`;
                console.log(`Testing image accessibility: ${imageUrl}`);
                const testImg = new Image();
                testImg.onload = () => console.log(`✅ Image ${index + 1} loaded successfully: ${imageUrl}`);
                testImg.onerror = () => console.log(`❌ Image ${index + 1} failed to load: ${imageUrl}`);
                testImg.src = imageUrl;
            });
        }

        messageElement.innerHTML = `
            <div class="${maxWidth} px-4 py-2 rounded-lg ${isAdmin ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800'}">
                ${messageText}
                ${fileHtml}
                <div class="text-xs mt-1 ${isAdmin ? 'text-blue-100' : 'text-gray-500'}">${time}</div>
            </div>
        `;

        return messageElement;
    }

    async function pruneVisitorsNow() {
        try {
            console.log('Manually pruning offline visitors...');
            const response = await fetch('/admin/visitors/prune-now', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const data = await response.json();
                console.log('Pruning completed:', data);
                showNotification(`Pruned ${data.pruned} offline visitors`);
                // Refresh the visitor list
                loadActiveVisitors();
            } else {
                console.error('Failed to prune visitors:', response.status);
            }
        } catch (error) {
            console.error('Error pruning visitors:', error);
        }
    }

    function debugUnreadCounts() {
        console.log('=== Unread Counts Debug ===');
        console.log('Current unreadMessages Map:', unreadMessages);
        console.log('Selected Visitor ID:', selectedVisitorId);
        console.log('Available visitors:', Object.keys(visitors));

        // Check each visitor's unread count
        for (const [visitorId, count] of unreadMessages) {
            console.log(`Visitor ${visitorId}: ${count} unread messages`);
        }

        // Check DOM elements
        document.querySelectorAll('.unread-badge').forEach(badge => {
            console.log('Found unread badge in DOM:', badge.textContent);
        });

        // Check visitor elements in sidebar
        document.querySelectorAll('.visitor-item').forEach(item => {
            const visitorId = item.getAttribute('data-visitor-id');
            const unreadBadge = item.querySelector('.unread-badge');
            console.log(`Sidebar visitor ${visitorId}:`, unreadBadge ? `Badge: ${unreadBadge.textContent}` : 'No badge');
        });

        console.log('========================');
    }

    // Admin file handling functions
    function handleAdminFileSelect() {
        const fileInput = document.getElementById('admin-file-input');
        const files = Array.from(fileInput.files);

        if (files.length > 0) {
            // Validate each file
            for (let file of files) {
                // Check file size (10MB limit)
                if (file.size > 10 * 1024 * 1024) {
                    alert(`File "${file.name}" size must be less than 10MB`);
                    fileInput.value = '';
                    return;
                }

                // Check file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf'];
                if (!allowedTypes.includes(file.type)) {
                    alert(`File "${file.name}" type not allowed. Only images (JPG, PNG, GIF) and PDF files are allowed`);
                    fileInput.value = '';
                    return;
                }
            }

            // Add files to selected files array
            selectedAdminFiles = [...selectedAdminFiles, ...files];
            updateAdminFilePreview();
            document.getElementById('admin-file-preview').classList.remove('hidden');
        }
    }

    function updateAdminFilePreview() {
        const fileList = document.getElementById('admin-file-list');
        fileList.innerHTML = '';

        selectedAdminFiles.forEach((file, index) => {
            const fileItem = document.createElement('div');
            fileItem.className = 'flex items-center space-x-2 bg-blue-100 p-2 rounded';
            fileItem.innerHTML = `
                <i class="fas fa-file text-blue-600"></i>
                <span class="text-sm text-blue-800 flex-1">${file.name}</span>
                <button onclick="removeAdminFile(${index})" class="text-red-600 hover:text-red-800">
                    <i class="fas fa-times"></i>
                </button>
            `;
            fileList.appendChild(fileItem);
        });
    }

    function removeAdminFile(index) {
        selectedAdminFiles.splice(index, 1);
        if (selectedAdminFiles.length === 0) {
            document.getElementById('admin-file-preview').classList.add('hidden');
        } else {
            updateAdminFilePreview();
        }
    }

    function clearAllAdminFiles() {
        selectedAdminFiles = [];
        document.getElementById('admin-file-input').value = '';
        document.getElementById('admin-file-preview').classList.add('hidden');
    }
</script>


</body>
</html>
