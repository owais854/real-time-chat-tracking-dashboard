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

        <div id="messages" class="messages p-4 space-y-4 overflow-y-auto" style="max-height: 400px; overflow-y: scroll;">
            <div class="text-center text-gray-500 text-sm mt-4">A support agent will be with you shortly...</div>
        </div>

        <div class="border-t p-4 bg-gray-50">
            <div class="flex space-x-2">
                <input type="file" id="file-input" accept="image/*,.pdf" multiple class="hidden" onchange="handleFileSelect()">
                <button onclick="console.log('File button clicked'); document.getElementById('file-input').click()"
                        class="bg-gray-500 text-white px-4 py-2 rounded-full hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    <i class="fas fa-paperclip"></i>
                </button>
                <input type="text" id="message-input" placeholder="Type your message..."
                       class="flex-1 px-4 py-2 border rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500"
                       onkeypress="if(event.key === 'Enter') sendMessage()">
                <button onclick="sendMessage()"
                        class="bg-blue-600 text-white px-6 py-2 rounded-full hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
            <div id="file-preview" class="mt-2 hidden">
                <div id="file-list" class="space-y-2">
                    <!-- Files will be added here dynamically -->
                </div>
                <button onclick="clearAllFiles()" class="mt-2 text-sm text-red-600 hover:text-red-800">
                    <i class="fas fa-times mr-1"></i>Clear All Files
                </button>
            </div>
        </div>
    </div>
</div>
{{--<script src="http://127.0.0.1:8000/js/embed.js"></script>--}}
<script>
    // Function to append messages in chat (global scope)
    let displayedMessageIds = new Set(); // Track displayed messages to prevent duplicates

    // Function to load chat history by IP address
    async function loadChatHistory() {
        try {
            console.log('Loading chat history for visitor...');
            const response = await fetch('/messages');
            const messages = await response.json();
            
            console.log('Fetched messages:', messages);
            
            if (messages && messages.length > 0) {
                // Sort messages by creation time (oldest first)
                messages.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
                
                // Clear the "support agent will be with you" message
                const messagesContainer = document.getElementById('messages');
                messagesContainer.innerHTML = '';
                
                // Display all messages
                messages.forEach(message => {
                    // Handle backward compatibility - use files if available, otherwise use file
                    const files = message.files || (message.file ? [message.file] : null);
                    addMessageToChat(
                        message.message, 
                        message.from_admin, 
                        message.id,
                        files
                    );
                });
                
                console.log('Chat history loaded successfully');
            } else {
                console.log('No chat history found');
            }
        } catch (error) {
            console.error('Error loading chat history:', error);
        }
    }

    function addMessageToChat(message, fromAdmin, messageId = null, files = null) {
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

        let fileHtml = '';
        console.log('visitor addMessageToChat - files:', files);
        if (files && files.length > 0) {
            console.log('Processing files for display in visitor chat:', files.length, files);
            fileHtml = '<div class="mt-2 space-y-2">';
            files.forEach((file, index) => {
                console.log(`Processing file ${index + 1}:`, file);
                const fileExtension = file.split('.').pop().toLowerCase();
                const isImage = ['jpg', 'jpeg', 'png', 'gif'].includes(fileExtension);
                const isPdf = fileExtension === 'pdf';
                console.log(`File ${index + 1} - Extension: ${fileExtension}, IsImage: ${isImage}, IsPdf: ${isPdf}`);
                
                if (isImage) {
                    const imageUrl = `/storage/${file}`;
                    console.log(`Visitor chat - Generating image HTML for: ${file}, URL: ${imageUrl}`);
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

        if (fromAdmin) {
            messageElement.className = "text-left";
            const maxWidth = files && files.length > 0 ? "max-w-md" : "max-w-xs";
            const messageText = message ? `<strong>Admin:</strong> ${message}` : (files && files.length > 0 ? '<strong>Admin:</strong> Sent files' : '<strong>Admin:</strong>');
            console.log('Visitor chat - Admin message HTML:', { messageText, fileHtml, maxWidth });
            messageElement.innerHTML = `
            <div class="inline-block bg-gray-200 text-gray-800 px-4 py-2 rounded-lg ${maxWidth}">
                ${messageText}
                ${fileHtml}
            </div>
        `;
        } else {
            messageElement.className = "text-right";
            const maxWidth = files && files.length > 0 ? "max-w-md" : "max-w-xs";
            const messageText = message || (files && files.length > 0 ? 'Sent files' : '');
            console.log('Visitor chat - Visitor message HTML:', { messageText, fileHtml, maxWidth });
            messageElement.innerHTML = `
            <div class="inline-block bg-blue-500 text-white px-4 py-2 rounded-lg ${maxWidth}">
                ${messageText}
                ${fileHtml}
            </div>
        `;
        }

        messagesContainer.appendChild(messageElement);
        messagesContainer.scrollTop = messagesContainer.scrollHeight; // auto scroll
    }

    document.addEventListener('DOMContentLoaded', async () => {
        console.log('visitor: Connected to WebSocket: ', window.Echo);
        let visitorSessionId = "{{ $visitorId }}";
        
        // Load chat history for this visitor's IP address
        await loadChatHistory();

        // Listen for admin messages (only to this specific visitor)
        const visitorChannel = 'chat.visitor.' + visitorSessionId;
        console.log('Visitor joining channel:', visitorChannel);
        
        Echo.channel(visitorChannel)
            .listen('ChatMessageSent', (e) => {
                console.log('Visitor received message event:', e);
                console.log('Message details - fromAdmin:', e.fromAdmin, 'message:', e.message, 'sessionId:', e.sessionId, 'files:', e.files, 'file:', e.file);
                console.log('Visitor session ID:', visitorSessionId);
                console.log('Event session ID:', e.sessionId);
                console.log('Session IDs match:', visitorSessionId === e.sessionId);

                // Only show admin messages (fromAdmin = true) to this visitor
                if (e.fromAdmin) {
                    console.log('Adding admin message to chat:', e.message);
                    console.log('Admin message sessionId:', e.sessionId, 'Visitor sessionId:', visitorSessionId);
                    
                    // Check if this message is for this visitor
                    if (e.sessionId === visitorSessionId) {
                        console.log('Admin message is for this visitor, displaying');
                        
                        // Handle files data more reliably
                        let messageFiles = null;
                        if (e.files && Array.isArray(e.files) && e.files.length > 0) {
                            messageFiles = e.files;
                        } else if (e.file) {
                            messageFiles = [e.file];
                        }
                        
                        addMessageToChat(e.message, true, e.messageId, messageFiles);
                    } else {
                        console.log('Admin message is for different visitor, ignoring');
                    }
                } else {
                    console.log('Ignoring visitor message (not from admin)');
                }
            })
            .error((error) => {
                console.error('Visitor channel error:', error);
            });

        // Also listen to the admin channel as a fallback to catch any missed messages
        Echo.private('chat.admin')
            .listen('ChatMessageSent', (e) => {
                console.log('Visitor received admin channel message:', e);
                console.log('Admin channel - fromAdmin:', e.fromAdmin, 'sessionId:', e.sessionId);
                
                // Only process admin messages that are meant for this visitor
                if (e.fromAdmin && e.sessionId === visitorSessionId) {
                    console.log('Processing admin message from admin channel fallback');
                    console.log('Fallback - Admin message sessionId:', e.sessionId, 'Visitor sessionId:', visitorSessionId);
                    
                    // Handle files data more reliably
                    let messageFiles = null;
                    if (e.files && Array.isArray(e.files) && e.files.length > 0) {
                        messageFiles = e.files;
                    } else if (e.file) {
                        messageFiles = [e.file];
                    }
                    
                    addMessageToChat(e.message, true, e.messageId, messageFiles);
                } else if (e.fromAdmin) {
                    console.log('Fallback - Admin message is for different visitor, ignoring');
                }
            })
            .error((error) => {
                console.error('Admin channel error:', error);
            });

        // Listen to public visitor presence channel for status updates
        Echo.channel('visitors.public')
            .listen('visitor.online', (e) => {
                console.log('Visitor came online:', e.visitor);
                // You can add visual indicators here if needed
            })
            .listen('visitor.offline', (e) => {
                console.log('Visitor went offline:', e.visitorId);
                // You can add visual indicators here if needed
            })
            .error((error) => {
                console.error('Public visitor channel error:', error);
            });

        // Send message
        let isSending = false; // Prevent multiple simultaneous sends
        let selectedFiles = [];

        window.sendMessage = async function () {
            if (isSending) {
                console.log('Message already being sent, please wait...');
                return;
            }

            const input = document.getElementById('message-input');
            const message = input.value.trim();
            
            // Debug: Log current state
            console.log('=== SEND MESSAGE DEBUG ===');
            console.log('Message:', message);
            console.log('Selected Files Count:', selectedFiles.length);
            console.log('Selected Files:', selectedFiles);
            console.log('File details:', selectedFiles.map(f => ({name: f.name, size: f.size, type: f.type})));
            
            if (!message && selectedFiles.length === 0) {
                console.log('No message or files to send');
                return;
            }

            isSending = true;

            try {
                const formData = new FormData();
                formData.append('message', message || '');
                formData.append('from_admin', 'false');
                formData.append('session_id', visitorSessionId);
                console.log('Selected files before sending:', selectedFiles.length, selectedFiles);
                if (selectedFiles.length > 0) {
                    selectedFiles.forEach((file, index) => {
                        formData.append('files[]', file);
                        console.log(`Sending file ${index + 1}:`, file.name, 'Size:', file.size, 'Type:', file.type);
                    });
                } else {
                    console.log('No files selected');
                }

                console.log('Sending message:', message || '');
                console.log('FormData contents:');
                for (let [key, value] of formData.entries()) {
                    console.log(key, value);
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
                    console.log('Message sent successfully:', data);
                    addMessageToChat(message || '', false, data.message_id, data.file_paths);
                    input.value = '';
                    clearAllFiles();
                    input.focus();
                } else {
                    const errorData = await response.json().catch(() => ({}));
                    console.error('Failed to send message:', response.status, response.statusText, errorData);
                    alert('Failed to send message: ' + (errorData.error || response.statusText));
                }
            } catch (error) {
                console.error('Error sending message:', error);
            } finally {
                isSending = false;
            }
        };

        // File handling functions
        window.handleFileSelect = function() {
            console.log('handleFileSelect called');
            const fileInput = document.getElementById('file-input');
            const files = Array.from(fileInput.files);
            console.log('Files from input:', files.length, files);
            
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
                selectedFiles = [...selectedFiles, ...files];
                console.log('Files added to selectedFiles. Total count:', selectedFiles.length);
                updateFilePreview();
                document.getElementById('file-preview').classList.remove('hidden');
            }
        };

        window.updateFilePreview = function() {
            const fileList = document.getElementById('file-list');
            fileList.innerHTML = '';
            
            selectedFiles.forEach((file, index) => {
                const fileItem = document.createElement('div');
                fileItem.className = 'flex items-center space-x-2 bg-blue-100 p-2 rounded';
                fileItem.innerHTML = `
                    <i class="fas fa-file text-blue-600"></i>
                    <span class="text-sm text-blue-800 flex-1">${file.name}</span>
                    <button onclick="removeFile(${index})" class="text-red-600 hover:text-red-800">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                fileList.appendChild(fileItem);
            });
        };

        window.removeFile = function(index) {
            selectedFiles.splice(index, 1);
            if (selectedFiles.length === 0) {
                document.getElementById('file-preview').classList.add('hidden');
            } else {
                updateFilePreview();
            }
        };

        window.clearAllFiles = function() {
            selectedFiles = [];
            document.getElementById('file-input').value = '';
            document.getElementById('file-preview').classList.add('hidden');
        };
    });
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    
    // Join when page loads and ensure active status
    console.log('Visitor: Joining chat...');
    axios.post('{{ route('visitor.join') }}', {}, {
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
        }
    }).then(response => {
        console.log('Visitor: Successfully joined chat:', response.data);
        // Also ensure the visitor is marked as active
        return axios.post('{{ route('visitor.active') }}', {}, {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
    }).then(response => {
        console.log('Visitor: Successfully marked as active on page load:', response.data);
    }).catch(error => {
        console.error('Visitor: Error joining chat or marking as active:', error);
    });

    // Enhanced heartbeat ping every 10 seconds
    let pingInterval = setInterval(() => {
        // Only ping if page is visible
        if (!document.hidden) {
            // Send ping to maintain connection
            axios.post('{{ route('visitor.ping') }}', {}, {
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(response => {
                console.log('Ping successful');
                // Also ensure active status is maintained
                return axios.post('{{ route('visitor.active') }}', {}, {
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
            }).then(response => {
                console.log('Active status maintained via ping');
            }).catch(error => {
                console.error('Ping or active status failed:', error);
            });
        }
    }, 10000); // 10 seconds for responsive tracking
    
    // Track page visibility changes
    let isPageVisible = true;
    let visibilityTimeout = null;
    
    // Handle page visibility changes
    document.addEventListener('visibilitychange', function() {
        console.log('Visibility change detected. Document hidden:', document.hidden);
        
        if (document.hidden) {
            // Page became hidden (user switched tabs or minimized browser)
            console.log('Visitor: Page became hidden - marking as inactive');
            isPageVisible = false;
            
            // Send inactive event immediately
            axios.post('{{ route('visitor.inactive') }}', {}, {
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(response => {
                console.log('Visitor: Successfully marked as inactive', response.data);
            }).catch(error => {
                console.error('Visitor: Error marking as inactive:', error);
            });
            
        } else {
            // Page became visible again (user came back to this tab)
            console.log('Visitor: Page became visible - marking as active');
            isPageVisible = true;
            
            // Clear any pending timeout
            if (visibilityTimeout) {
                clearTimeout(visibilityTimeout);
                visibilityTimeout = null;
            }
            
            // Send active event with a small delay to ensure proper processing
            setTimeout(() => {
                axios.post('{{ route('visitor.active') }}', {}, {
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).then(response => {
                    console.log('Visitor: Successfully marked as active', response.data);
                    // Also send a join event to ensure the visitor is properly registered
                    return axios.post('{{ route('visitor.join') }}', {}, {
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                }).then(response => {
                    console.log('Visitor: Successfully rejoined chat', response.data);
                }).catch(error => {
                    console.error('Visitor: Error marking as active or rejoining:', error);
                });
            }, 100); // Small delay to ensure proper processing
        }
    });
    
    // Additional tracking for window focus/blur events
    window.addEventListener('focus', function() {
        if (isPageVisible) {
            console.log('Visitor: Window focused - ensuring active status');
            // Send active event to ensure status is correct
            setTimeout(() => {
                axios.post('{{ route('visitor.active') }}', {}, {
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).then(response => {
                    console.log('Visitor: Focus event - marked as active');
                }).catch(error => {
                    console.error('Visitor: Error on focus event:', error);
                });
            }, 50); // Small delay to ensure proper processing
        }
    });
    
    window.addEventListener('blur', function() {
        console.log('Visitor: Window blurred - marking as inactive');
        // Send inactive event when window loses focus
        axios.post('{{ route('visitor.inactive') }}', {}, {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(response => {
            console.log('Visitor: Blur event - marked as inactive');
        }).catch(error => {
            console.error('Visitor: Error on blur event:', error);
        });
    });
    
    // Only handle page unload, not visibility changes
    let isLeaving = false;

    // Best-effort leave on unload
    window.addEventListener('beforeunload', function () {
        if (!isLeaving) {
            isLeaving = true;
            console.log('Visitor: Leaving chat...');
            clearInterval(pingInterval);
            const data = new FormData();
            data.append('_token', csrfToken);
            navigator.sendBeacon('{{ route('visitor.leave') }}', data);
        }
    });
});
</script>

</body>
</html>
