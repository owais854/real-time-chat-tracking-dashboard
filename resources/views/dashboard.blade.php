<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard Overview') }}
            </h2>
            <div class="flex items-center space-x-4">
{{--                <button onclick="testDashboardEvents()" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">--}}
{{--                    Test Events--}}
{{--                </button>--}}
{{--                <button onclick="cleanupVisitors()" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">--}}
{{--                    Cleanup Old Visitors--}}
{{--                </button>--}}
            <div class="text-sm text-gray-500">
                Last updated: {{ now()->format('M d, Y h:i A') }}
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-blue-500">
                    <div class="text-gray-500 font-medium">Total Messages</div>
                    <div class="text-3xl font-bold text-gray-900">{{ $totalMessages ?? 0 }}</div>
                    <div class="text-sm text-green-600 mt-1">+12% from last week</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-indigo-500">
                    <div class="text-gray-500 font-medium">Total Agents</div>
                    <div class="text-3xl font-bold text-gray-900">{{ $totalAgents ?? 0 }}</div>
                    <div class="text-sm text-green-600 mt-1">Active now</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-green-500">
                    <div class="text-gray-500 font-medium">Active Visitors</div>
                    <div class="text-3xl font-bold text-gray-900">{{ $activeVisitors ?? 0 }}</div>
                    <div class="text-sm text-green-600 mt-1">+5 online</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-yellow-500">
                    <div class="text-gray-500 font-medium">Chats Today</div>
                    <div class="text-3xl font-bold text-gray-900">{{ $todayChats ?? 0 }}</div>
                    <div class="text-sm text-green-600 mt-1">+3 from yesterday</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-purple-500">
                    <div class="text-gray-500 font-medium">Avg. Response Time</div>
                    <div class="text-3xl font-bold text-gray-900">2.4s</div>
                    <div class="text-sm text-green-600 mt-1">-0.5s from last week</div>
                </div>
            </div>

            <!-- Recent Messages Table -->
{{--            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">--}}
{{--                <div class="p-6">--}}
{{--                    <div class="flex justify-between items-center mb-6">--}}
{{--                        <h3 class="text-lg font-medium text-gray-900">Recent Messages</h3>--}}
{{--                        <div class="relative">--}}
{{--                            <input type="text" placeholder="Search messages..." class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 pl-10 pr-4 py-2 text-sm w-64" />--}}
{{--                            <div class="absolute left-3 top-2.5 text-gray-400">--}}
{{--                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">--}}
{{--                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />--}}
{{--                                </svg>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}

{{--                    <div class="overflow-x-auto">--}}
{{--                        <table class="min-w-full divide-y divide-gray-200">--}}
{{--                            <thead class="bg-gray-50">--}}
{{--                                <tr>--}}
{{--                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Visitor</th>--}}
{{--                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>--}}
{{--                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>--}}
{{--                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>--}}
{{--                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>--}}
{{--                                </tr>--}}
{{--                            </thead>--}}
{{--                            <tbody class="bg-white divide-y divide-gray-200">--}}
{{--                                @forelse($recentMessages as $message)--}}
{{--                                <tr class="hover:bg-gray-50">--}}
{{--                                    <td class="px-6 py-4 whitespace-nowrap">--}}
{{--                                        <div class="flex items-center">--}}
{{--                                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">--}}
{{--                                                <span class="text-indigo-600 font-medium">{{ substr($message->visitor->name ?? 'U', 0, 1) }}</span>--}}
{{--                                            </div>--}}
{{--                                            <div class="ml-4">--}}
{{--                                                <div class="text-sm font-medium text-gray-900">{{ $message->visitor->name ?? 'Unknown' }}</div>--}}
{{--                                                <div class="text-sm text-gray-500">ID: {{ $message->visitor_id }}</div>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                    </td>--}}
{{--                                    <td class="px-6 py-4">--}}
{{--                                        <div class="text-sm text-gray-900 font-medium">{{ Str::limit($message->content, 50) }}</div>--}}
{{--                                        <div class="text-sm text-gray-500">{{ $message->created_at->diffForHumans() }}</div>--}}
{{--                                    </td>--}}
{{--                                    <td class="px-6 py-4 whitespace-nowrap">--}}
{{--                                        @if($message->is_read)--}}
{{--                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">--}}
{{--                                                Read--}}
{{--                                            </span>--}}
{{--                                        @else--}}
{{--                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">--}}
{{--                                                Unread--}}
{{--                                            </span>--}}
{{--                                        @endif--}}
{{--                                    </td>--}}
{{--                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">--}}
{{--                                        {{ $message->created_at->format('M d, Y h:i A') }}--}}
{{--                                    </td>--}}
{{--                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">--}}
{{--                                        <a href="#" class="text-indigo-600 hover:text-indigo-900 mr-3">View</a>--}}
{{--                                        <a href="#" class="text-green-600 hover:text-green-900">Reply</a>--}}
{{--                                    </td>--}}
{{--                                </tr>--}}
{{--                                @empty--}}
{{--                                <tr>--}}
{{--                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">--}}
{{--                                        No messages found.--}}
{{--                                    </td>--}}
{{--                                </tr>--}}
{{--                                @endforelse--}}
{{--                            </tbody>--}}
{{--                        </table>--}}
{{--                    </div>--}}

{{--                    <div class="mt-4">--}}
{{--                        {{ $recentMessages->links() }}--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
        </div>
    </div>
</x-app-layout>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Dashboard: DOM loaded, initializing real-time tracking...');

        // Wait for Echo to be available
        function waitForEcho() {
            if (window.Echo) {
                console.log('Dashboard: Echo is available, joining public channel...');

                // Listen to visitor online/offline events
                window.Echo.channel('visitors.public')
                    .listen('visitor.online', (e) => {
                        console.log('Dashboard: Visitor came online event received:', e);
                        console.log('Dashboard: Event visitor data:', e.visitor);
                        if (e.visitor && e.visitor.ip_address) {
                            console.log('Dashboard: Processing online event for IP:', e.visitor.ip_address);
                            showNotification(`Visitor ${e.visitor.ip_address} is now online`);
                            // Update visitor status in real-time
                            updateVisitorStatusInDashboard(e.visitor.ip_address, true);
                            // Update the active visitors count
                            updateActiveVisitorsCount();
                        } else {
                            console.log('Dashboard: No visitor data or IP address in online event');
                        }
                    })
                    .listen('visitor.offline', (e) => {
                        console.log('Dashboard: Visitor went offline event received:', e);
                        console.log('Dashboard: Event visitor data:', e.visitor);
                        if (e.visitor && e.visitor.ip_address) {
                            console.log('Dashboard: Processing offline event for IP:', e.visitor.ip_address);
                            showNotification(`Visitor ${e.visitor.ip_address} went offline`);
                            // Update visitor status in real-time
                            updateVisitorStatusInDashboard(e.visitor.ip_address, false);
                            // Update the active visitors count
                            updateActiveVisitorsCount();
                        } else {
                            console.log('Dashboard: No visitor data or IP address in offline event');
                        }
                    })
                    .error((error) => {
                        console.error('Dashboard: Public channel error:', error);
                    });
            } else {
                console.log('Dashboard: Echo not ready yet, retrying in 500ms...');
                setTimeout(waitForEcho, 500);
            }
        }

        // Start waiting for Echo
        waitForEcho();

        // Test function for manual testing
        window.testDashboardEvents = function() {
            console.log('Dashboard: Testing events manually...');
            updateActiveVisitorsCount();
        };

        // Test function to simulate visitor online event
        window.testVisitorOnline = function() {
            console.log('Dashboard: Testing visitor online event...');
            const testEvent = {
                visitor: {
                    ip_address: '127.0.0.1',
                    is_active: true,
                    last_activity: new Date().toISOString()
                }
            };
            console.log('Dashboard: Simulating visitor online event:', testEvent);
            showNotification(`Visitor ${testEvent.visitor.ip_address} is now online`);
            updateActiveVisitorsCount();
        };

        // Initial load of visitor count
        updateActiveVisitorsCount();

        // Function to update visitor status in dashboard
        function updateVisitorStatusInDashboard(visitorIp, isOnline = true) {
            console.log('Dashboard: Updating visitor status:', visitorIp, 'isOnline:', isOnline);

            // Update any visitor status indicators if they exist
            const visitorElements = document.querySelectorAll('[data-visitor-ip="' + visitorIp + '"]');
            visitorElements.forEach(element => {
                const statusDot = element.querySelector('.w-3.h-3, .status-dot');
                const statusText = element.querySelector('.status-text');

                if (statusDot) {
                    statusDot.className = `w-3 h-3 ${isOnline ? 'bg-green-500' : 'bg-gray-400'} rounded-full mr-2 status-dot`;
                }

                if (statusText) {
                    statusText.className = `text-xs ${isOnline ? 'text-green-600' : 'text-gray-500'} status-text`;
                    statusText.textContent = isOnline ? 'Online' : 'Offline';
                }
            });

            console.log('Dashboard: Visitor status updated successfully');
        }

        // Function to update active visitors count
        function updateActiveVisitorsCount(count = null) {
            console.log('Dashboard: Updating active visitors count, count provided:', count);
            if (count !== null) {
                // Update the active visitors count in the stats card
                const activeVisitorsElement = document.querySelector('.border-l-4.border-green-500 .text-3xl');
                if (activeVisitorsElement) {
                    activeVisitorsElement.textContent = count;
                    console.log('Dashboard: Updated active visitors count to:', count);
                } else {
                    console.log('Dashboard: Active visitors element not found');
                }
            } else {
                // Fetch updated count from server using the active endpoint (more reliable)
                console.log('Dashboard: Fetching updated count from server...');
                fetch('/admin/visitors/active')
                    .then(response => response.json())
                    .then(visitors => {
                        console.log('Dashboard: Fetched active visitors:', visitors);

                        // Remove duplicates by IP address to ensure accurate count
                        const uniqueVisitors = [];
                        const seenIPs = new Set();

                        visitors.forEach(visitor => {
                            const ip = visitor.ip_address;
                            if (ip && !seenIPs.has(ip)) {
                                seenIPs.add(ip);
                                uniqueVisitors.push(visitor);
                            }
                        });

                        console.log('Dashboard: Unique active visitors:', uniqueVisitors.length);
                        const activeVisitorsElement = document.querySelector('.border-l-4.border-green-500 .text-3xl');
                        if (activeVisitorsElement) {
                            activeVisitorsElement.textContent = uniqueVisitors.length;
                            console.log('Dashboard: Updated active visitors count to:', uniqueVisitors.length);
                        } else {
                            console.log('Dashboard: Active visitors element not found');
                        }
                    })
                    .catch(error => console.error('Dashboard: Error fetching visitors:', error));
            }
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

        // Function to cleanup old visitors
        function cleanupVisitors() {
            if (confirm('Are you sure you want to cleanup all old visitors? This will mark all inactive visitors as offline.')) {
                fetch('/admin/visitors/cleanup', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    alert(`Cleaned up ${data.cleaned} old visitors`);
                    updateActiveVisitorsCount(); // Refresh the count
                })
                .catch(error => {
                    console.error('Cleanup failed:', error);
                    alert('Cleanup failed. Please try again.');
                });
            }
        }

        // Auto refresh visitor count every 10 seconds to ensure accuracy
        setInterval(function() {
            console.log('Dashboard: Auto-refreshing visitor count...');
            updateActiveVisitorsCount();
        }, 10000);

        // Auto refresh every 30 seconds (reduced frequency since we have real-time updates)
    setInterval(function() {
            // Only refresh if we don't have real-time updates
            if (!window.Echo) {
        window.location.reload();
            }
    }, 30000);
    });
</script>
