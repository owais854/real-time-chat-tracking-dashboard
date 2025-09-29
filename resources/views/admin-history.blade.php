<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Chat History</h2>
                <p class="mt-1 text-sm text-gray-600">View and manage all visitor chat histories</p>
            </div>
            <div class="mt-4 md:mt-0 flex items-center space-x-4">
{{--                <button onclick="testStatusUpdate('127.0.0.1', true)" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">--}}
{{--                    Test Online--}}
{{--                </button>--}}
{{--                <button onclick="testStatusUpdate('127.0.0.1', false)" class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-1 rounded text-sm">--}}
{{--                    Test Offline--}}
{{--                </button>--}}
{{--                <button onclick="debugData()" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm">--}}
{{--                    Debug Data--}}
{{--                </button>--}}
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <input id="search" type="text"
                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                           placeholder="Search by IP, URL, or message..." />
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Chats</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900" id="totalChats">0</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0111.273-3.306" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Unique Visitors</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900" id="uniqueVisitors">0</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Avg. Response Time</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900">2.4s</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Active Chats</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900" id="activeChats">0</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chat History Table -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Chat Sessions</h3>
                        <div class="mt-3 sm:mt-0">
                            <div class="flex items-center">
                                <span class="text-sm text-gray-500 mr-2">Filter:</span>
                                <select id="timeFilter" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                    <option value="today">Today</option>
                                    <option value="week">This Week</option>
                                    <option value="month">This Month</option>
                                    <option value="all" selected>All Time</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Visitor</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Activity</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="historyBody" class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    <div class="flex justify-center items-center py-8">
                                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                                        <span class="ml-2">Loading chat history...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700" id="paginationInfo">
                                Showing <span class="font-medium">1</span> to <span class="font-medium">10</span> of <span class="font-medium">20</span> results
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination" id="pagination">
                                <!-- Pagination will be inserted here by JavaScript -->
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const body = document.getElementById('historyBody');
            const search = document.getElementById('search');
            const timeFilter = document.getElementById('timeFilter');
            let currentPage = 1;
            const itemsPerPage = 10;
            let allData = [];
            let filteredData = [];

            // Format date to relative time (e.g., "2 hours ago")
            function formatRelativeTime(dateString) {
                const date = new Date(dateString);
                const now = new Date();
                const seconds = Math.floor((now - date) / 1000);

                let interval = Math.floor(seconds / 31536000);
                if (interval >= 1) return `${interval} year${interval === 1 ? '' : 's'} ago`;

                interval = Math.floor(seconds / 2592000);
                if (interval >= 1) return `${interval} month${interval === 1 ? '' : 's'} ago`;

                interval = Math.floor(seconds / 86400);
                if (interval >= 1) return `${interval} day${interval === 1 ? '' : 's'} ago`;

                interval = Math.floor(seconds / 3600);
                if (interval >= 1) return `${interval} hour${interval === 1 ? '' : 's'} ago`;

                interval = Math.floor(seconds / 60);
                if (interval >= 1) return `${interval} minute${interval === 1 ? '' : 's'} ago`;

                return 'Just now';
            }

            // Fetch chat history data
            function fetchHistory() {
                body.innerHTML = `
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                            <div class="flex justify-center items-center py-8">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                                <span class="ml-2">Loading chat history...</span>
                            </div>
                        </td>
                    </tr>`;

                console.log('Admin-History: Fetching history data...');
                axios.get('/admin/history/data')
                    .then(res => {
                        console.log('Admin-History: Received data from server:', res.data);
                        allData = Array.isArray(res.data) ? res.data : [];
                        console.log('Admin-History: Processed allData:', allData);
                        console.log('Admin-History: Number of visitors:', allData.length);
                        allData.forEach((visitor, index) => {
                            console.log(`Admin-History: Visitor ${index + 1}:`, visitor.visitor_ip, 'last_activity:', visitor.last_activity, 'is_active:', visitor.is_active);
                        });
                        updateStats(allData);
                        filterAndRender();
                    })
                    .catch(err => {
                        console.error('Admin-History: Error fetching history:', err);
                        body.innerHTML = `
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-red-500">
                                    Error loading chat history. Please try again.
                                </td>
                            </tr>`;
                    });
            }

            // Enhanced statistics update function
            function updateStats(data) {
                console.log('Admin-History: Updating statistics with data:', data);

                // Update total chats count
                document.getElementById('totalChats').textContent = data.length;

                // Count unique visitors
                const uniqueVisitors = new Set(data.map(chat => chat.visitor_ip));
                document.getElementById('uniqueVisitors').textContent = uniqueVisitors.size;

                // Count active chats using the is_active field from API
                const activeChats = data.filter(chat => {
                    // Check if visitor is active based on multiple criteria
                    const isActive = chat.is_active === true;
                    const hasRecentActivity = chat.last_activity &&
                        new Date(chat.last_activity) > new Date(Date.now() - 60000); // Last minute

                    return isActive || hasRecentActivity;
                }).length;

                console.log('Admin-History: Active chats count:', activeChats, 'from data:', data);
                document.getElementById('activeChats').textContent = activeChats;

                // Add visual feedback for active count changes
                const activeChatsElement = document.getElementById('activeChats');
                if (activeChatsElement) {
                    activeChatsElement.style.color = '#059669'; // Green color for active
                    setTimeout(() => {
                        activeChatsElement.style.color = '';
                    }, 1000);
                }
            }

            // Filter data based on search and time filter
            function filterData() {
                const searchTerm = (search.value || '').toLowerCase().trim();
                const timeFilterValue = timeFilter.value;
                const now = new Date();

                console.log('Admin-History: Filtering data with timeFilter:', timeFilterValue, 'total data:', allData.length);

                return allData.filter(chat => {
                    console.log('Admin-History: Processing visitor:', chat.visitor_ip, 'last_activity:', chat.last_activity);

                    // Apply time filter
                    if (chat.last_activity) {
                        const lastActivity = new Date(chat.last_activity);

                        if (timeFilterValue === 'today') {
                            const isToday = lastActivity.toDateString() === now.toDateString();
                            console.log('Admin-History: Today filter - isToday:', isToday);
                            if (!isToday) return false;
                        }

                        if (timeFilterValue === 'week') {
                            const oneWeekAgo = new Date();
                            oneWeekAgo.setDate(now.getDate() - 7);
                            const isWithinWeek = lastActivity >= oneWeekAgo;
                            console.log('Admin-History: Week filter - oneWeekAgo:', oneWeekAgo, 'lastActivity:', lastActivity, 'isWithinWeek:', isWithinWeek);
                            if (!isWithinWeek) return false;
                        }

                        if (timeFilterValue === 'month') {
                            const oneMonthAgo = new Date();
                            oneMonthAgo.setMonth(now.getMonth() - 1);
                            const isWithinMonth = lastActivity >= oneMonthAgo;
                            console.log('Admin-History: Month filter - oneMonthAgo:', oneMonthAgo, 'lastActivity:', lastActivity, 'isWithinMonth:', isWithinMonth);
                            if (!isWithinMonth) return false;
                        }
                    } else {
                        // If no last_activity, include the visitor (they might be cached visitors)
                        console.log('Admin-History: No last_activity for visitor:', chat.visitor_ip, '- including anyway');
                    }

                    // Apply search filter
                    if (!searchTerm) {
                        console.log('Admin-History: No search term - including visitor:', chat.visitor_ip);
                        return true;
                    }

                    const matchesSearch = (
                        (chat.visitor_ip && chat.visitor_ip.toLowerCase().includes(searchTerm)) ||
                        (chat.current_url && chat.current_url.toLowerCase().includes(searchTerm)) ||
                        (chat.last_message && chat.last_message.toLowerCase().includes(searchTerm))
                    );

                    console.log('Admin-History: Search filter - matchesSearch:', matchesSearch);
                    return matchesSearch;
                });
            }

            // Render the table with pagination
            function filterAndRender() {
                console.log('Admin-History: Starting filterAndRender...');
                filteredData = filterData();
                console.log('Admin-History: Filtered data result:', filteredData);
                console.log('Admin-History: Number of filtered visitors:', filteredData.length);
                filteredData.forEach((visitor, index) => {
                    console.log(`Admin-History: Filtered visitor ${index + 1}:`, visitor.visitor_ip, 'last_activity:', visitor.last_activity);
                });
                renderPagination(filteredData);
                renderTable(currentPage, filteredData);
            }

            // Render the table body
            function renderTable(page, data) {
                const startIndex = (page - 1) * itemsPerPage;
                const paginatedItems = data.slice(startIndex, startIndex + itemsPerPage);

                if (paginatedItems.length === 0) {
                    body.innerHTML = `
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No chat history found</h3>
                                <p class="mt-1 text-sm text-gray-500">Get started by having visitors chat with you.</p>
                            </td>
                        </tr>`;
                    return;
                }

                let out = '';
                paginatedItems.forEach((row, idx) => {
                    const ip = row.visitor_ip || 'Unknown';
                    const url = row.current_url ? new URL(row.current_url).pathname : '/';
                    const lastActive = row.last_activity ? formatRelativeTime(row.last_activity) : 'Never';
                    const lastMessage = row.last_message_at ? formatRelativeTime(row.last_message_at) : 'No messages';
                    const isActive = row.is_active || false; // Use the is_active field from the API
                    console.log('Admin-History: Row data:', row, 'isActive:', isActive);
                    out += `
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                    <span class="text-indigo-600 font-medium">${ip.split('.').pop()}</span>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">${ip}</div>
                                    <div class="text-sm text-gray-500">${row.total_messages || 0} messages</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">${url}</div>
                            <div class="text-sm text-gray-500">Last message: ${lastMessage}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${isActive ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                                ${isActive ? 'Online' : 'Offline'}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            ${lastActive}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button onclick="openChat('${encodeURIComponent(row.visitor_ip)}')" class="text-indigo-600 hover:text-indigo-900 mr-4" title="Open chat with ${ip}">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </td>
                    </tr>`;
                });

                body.innerHTML = out;

            }

            // Render pagination
            function renderPagination(data) {
                const totalPages = Math.ceil(data.length / itemsPerPage);
                const pagination = document.getElementById('pagination');
                const paginationInfo = document.getElementById('paginationInfo');

                if (data.length === 0) {
                    paginationInfo.innerHTML = 'No results found';
                    pagination.innerHTML = '';
                    return;
                }

                const startItem = ((currentPage - 1) * itemsPerPage) + 1;
                const endItem = Math.min(currentPage * itemsPerPage, data.length);

                paginationInfo.innerHTML = `Showing <span class="font-medium">${startItem}</span> to <span class="font-medium">${endItem}</span> of <span class="font-medium">${data.length}</span> results`;

                let buttons = '';

                // Previous button
                buttons += `
                    <button ${currentPage === 1 ? 'disabled' : ''}
                            onclick="changePage(${currentPage - 1})"
                            class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium ${currentPage === 1 ? 'text-gray-300' : 'text-gray-500 hover:bg-gray-50'}">
                        <span class="sr-only">Previous</span>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </button>`;

                // Page numbers
                const maxVisiblePages = 5;
                let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
                let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

                if (endPage - startPage + 1 < maxVisiblePages) {
                    startPage = Math.max(1, endPage - maxVisiblePages + 1);
                }

                if (startPage > 1) {
                    buttons += `
                        <button onclick="changePage(1)" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                            1
                        </button>`;
                    if (startPage > 2) {
                        buttons += `
                            <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                                ...
                            </span>`;
                    }
                }

                for (let i = startPage; i <= endPage; i++) {
                    buttons += `
                        <button onclick="changePage(${i})"
                                class="${i === currentPage ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'} relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                            ${i}
                        </button>`;
                }

                if (endPage < totalPages) {
                    if (endPage < totalPages - 1) {
                        buttons += `
                            <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                                ...
                            </span>`;
                    }
                    buttons += `
                        <button onclick="changePage(${totalPages})" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                            ${totalPages}
                        </button>`;
                }

                // Next button
                buttons += `
                    <button ${currentPage === totalPages ? 'disabled' : ''}
                            onclick="changePage(${currentPage + 1})"
                            class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium ${currentPage === totalPages ? 'text-gray-300' : 'text-gray-500 hover:bg-gray-50'}">
                        <span class="sr-only">Next</span>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </button>`;

                pagination.innerHTML = buttons;
            }

            // Global function to change page
            window.changePage = function(page) {
                if (page < 1 || page > Math.ceil(filteredData.length / itemsPerPage)) return;
                currentPage = page;
                renderTable(currentPage, filteredData);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            };

            // Event listeners
            search.addEventListener('input', () => {
                currentPage = 1;
                filterAndRender();
            });

            timeFilter.addEventListener('change', () => {
                currentPage = 1;
                filterAndRender();
            });

            // Global function to open chat
            window.openChat = function(id) {
                console.log('Admin-History: Opening chat with ID:', id);
                // Use session_id if available, otherwise fall back to IP address
                window.location.href = '/admin/chat?visitor=' + encodeURIComponent(id);
            };

            // Test function to manually update status (for debugging)
            window.testStatusUpdate = function(ip, isOnline) {
                console.log('Testing status update for IP:', ip, 'isOnline:', isOnline);
                updateOnlineStatusByIP(ip, isOnline);
            };

            // Debug function to check data
            window.debugData = function() {
                console.log('=== DEBUG DATA ===');
                console.log('All data:', allData);
                console.log('Filtered data:', filteredData);
                console.log('Current time filter:', timeFilter.value);
                console.log('Current search term:', search.value);
                console.log('Current page:', currentPage);
                console.log('Items per page:', itemsPerPage);

                // Force refresh
                console.log('Forcing data refresh...');
                fetchHistory();
            };

            // Enhanced real-time visitor status tracking
            console.log('Admin-History: Initializing enhanced real-time tracking...');

            // Wait for Echo to be available
            function waitForEcho() {
                if (window.Echo) {
                    console.log('Admin-History: Echo is available, joining public channel...');

                    // Listen to visitor online/offline events with enhanced handling
                    window.Echo.channel('visitors.public')
                        .listen('visitor.online', (e) => {
                            console.log('Admin-History: Visitor came online event received:', e);
                            console.log('Admin-History: Event visitor data:', e.visitor);
                            if (e.visitor && e.visitor.ip_address) {
                                console.log('Admin-History: Processing online event for IP:', e.visitor.ip_address);
                                // Use enhanced status update function
                                updateVisitorStatusInHistoryPage(e.visitor.ip_address, true);
                                // Refresh the history data to get updated status and show all visitors
                                setTimeout(() => {
                                    console.log('Admin-History: Refreshing history data after online event...');
                                    fetchHistory();
                                }, 500);
                            } else {
                                console.log('Admin-History: No visitor data or IP address in online event');
                            }
                        })
                        .listen('visitor.offline', (e) => {
                            console.log('Admin-History: Visitor went offline event received:', e);
                            console.log('Admin-History: Event visitor data:', e.visitor);
                            if (e.visitor && e.visitor.ip_address) {
                                console.log('Admin-History: Processing offline event for IP:', e.visitor.ip_address);
                                // Use enhanced status update function
                                updateVisitorStatusInHistoryPage(e.visitor.ip_address, false);
                                // Refresh the history data to get updated status and show all visitors
                                setTimeout(() => {
                                    console.log('Admin-History: Refreshing history data after offline event...');
                                    fetchHistory();
                                }, 500);
                            } else {
                                console.log('Admin-History: No visitor data or IP address in offline event');
                            }
                        })
                        .error((error) => {
                            console.error('Admin-History: Public channel error:', error);
                        });
                } else {
                    console.log('Admin-History: Echo not ready yet, retrying in 500ms...');
                    setTimeout(waitForEcho, 500);
                }
            }

            // Start waiting for Echo
            waitForEcho();

            // Initial load
            fetchHistory();

            // Auto refresh visitor status every 10 seconds
            setInterval(function() {
                console.log('Admin-History: Auto-refreshing visitor status...');
                // Refresh the history data to get updated status
                fetchHistory();
            }, 10000);

            // Function to update active visitors count in real-time
            function updateActiveVisitorsCountInHistory(count = null) {
                console.log('Admin-History: Updating active visitors count, count provided:', count);
                if (count !== null) {
                    // Update the active chats count in the stats card
                    const activeChatsElement = document.getElementById('activeChats');
                    if (activeChatsElement) {
                        activeChatsElement.textContent = count;
                        console.log('Admin-History: Updated active chats count to:', count);

                        // Add visual feedback
                        activeChatsElement.style.color = '#059669';
                        setTimeout(() => {
                            activeChatsElement.style.color = '';
                        }, 1000);
                    } else {
                        console.log('Admin-History: Active chats element not found');
                    }
                } else {
                    // Fetch updated count from server
                    console.log('Admin-History: Fetching updated count from server...');
                    fetch('/admin/visitors/active')
                        .then(response => response.json())
                        .then(visitors => {
                            console.log('Admin-History: Fetched active visitors:', visitors);
                            // Filter to only show truly online visitors
                            const onlineVisitors = visitors.filter(visitor => {
                                const isActive = visitor.is_active === true ||
                                    (visitor.last_activity && new Date(visitor.last_activity) > new Date(Date.now() - 60000));
                                return isActive;
                            });

                            console.log('Admin-History: Online visitors count:', onlineVisitors.length);
                            updateActiveVisitorsCountInHistory(onlineVisitors.length);
                        })
                        .catch(error => {
                            console.error('Admin-History: Error fetching active visitors:', error);
                        });
                }
            }
        });

        // Enhanced function to update online status by IP address
        function updateOnlineStatusByIP(ipAddress, isOnline) {
            console.log('Admin-History: Updating status for IP:', ipAddress, 'isOnline:', isOnline);
            const rows = document.querySelectorAll('#historyBody tr');
            console.log('Admin-History: Found rows:', rows.length);

            let updated = false;
            rows.forEach((row, index) => {
                // Find rows that contain this IP address
                const ipCell = row.querySelector('td:first-child .text-sm.font-medium');
                console.log(`Admin-History: Row ${index}:`, ipCell ? ipCell.textContent : 'No IP cell found');

                if (ipCell && ipCell.textContent === ipAddress) {
                    console.log('Admin-History: Found matching IP row for:', ipAddress);
                    const statusCell = row.querySelector('td:nth-child(3)');
                    if (statusCell) {
                        statusCell.innerHTML = `
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${isOnline ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                                ${isOnline ? 'Online' : 'Offline'}
                            </span>
                        `;
                        console.log('Admin-History: Updated status for IP:', ipAddress, 'to:', isOnline ? 'Online' : 'Offline');
                        updated = true;

                        // Add visual feedback for status change
                        row.style.backgroundColor = isOnline ? '#f0f9ff' : '#fef3c7';
                        setTimeout(() => {
                            row.style.backgroundColor = '';
                        }, 2000);
                    } else {
                        console.log('Admin-History: Status cell not found for IP:', ipAddress);
                    }
                }
            });

            // Also update the data array to reflect the status change
            if (allData && allData.length > 0) {
                allData.forEach(visitor => {
                    if (visitor.visitor_ip === ipAddress) {
                        visitor.is_active = isOnline;
                        visitor.last_activity = new Date().toISOString();
                        console.log('Admin-History: Updated visitor data for IP:', ipAddress);
                    }
                });
            }

            // Update statistics immediately
            updateStats(allData);

            if (!updated) {
                console.log('Admin-History: No matching row found for IP:', ipAddress, '- may need to refresh data');
            }
        }


        // Function to update visitor status in history page
        function updateVisitorStatusInHistoryPage(visitorIp, isOnline = true) {
            console.log('Admin-History: Updating visitor status:', visitorIp, 'isOnline:', isOnline);

            // Update any visitor status indicators if they exist
            const visitorElements = document.querySelectorAll('[data-visitor-ip="' + visitorIp + '"]');
            visitorElements.forEach(element => {
                const statusDot = element.querySelector('.w-3.h-3, .status-dot');
                const statusText = element.querySelector('.status-text');

                if (statusDot) {
                    statusDot.className = `w-3 h-3 ${isOnline ? 'bg-green-500' : 'bg-gray-400'} rounded-full mr-2 status-dot`;
                }

                if (statusText) {
                    statusText.textContent = isOnline ? 'Online' : 'Offline';
                    statusText.className = `text-xs font-medium ${isOnline ? 'text-green-600' : 'text-gray-500'} status-text`;
                }
            });

            // Update the main table status
            updateOnlineStatusByIP(visitorIp, isOnline);

            // Update active visitors count
            updateActiveVisitorsCountInHistory();

            // Update statistics immediately
            if (allData && allData.length > 0) {
                updateStats(allData);
            }

            // Show notification
            showNotification(`Visitor ${visitorIp} is now ${isOnline ? 'online' : 'offline'}`);

            // Force a complete refresh of the table to ensure all visitors are shown
            console.log('Admin-History: Forcing complete table refresh...');
            setTimeout(() => {
                fetchHistory();
            }, 1000);
        }

        // Function to show notifications
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

            // Remove after 3 seconds
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }, 3000);
        }
    </script>
</x-app-layout>

