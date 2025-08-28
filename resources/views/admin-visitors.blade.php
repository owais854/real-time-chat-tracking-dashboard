<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800">Active Visitors</h2>
            <div class="flex items-center bg-blue-100 text-blue-800 text-sm font-semibold px-3 py-1 rounded-full">
                <span id="visitorCountBadge" class="inline-flex items-center justify-center h-6 w-6 bg-blue-600 text-white text-xs font-bold rounded-full mr-2">0</span>
                Active Visitors
            </div>
        </div>
    </x-slot>

    <div class="py-8 px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow-lg rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-blue-600 to-blue-800">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-white uppercase tracking-wider">IP Address</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-white uppercase tracking-wider">Visited URL</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-white uppercase tracking-wider">Device</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-white uppercase tracking-wider">OS / Browser</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-white uppercase tracking-wider">Last Activity</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-medium text-white uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="visitorTableBody" class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                <div class="flex justify-center">
                                    <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500"></div>
                                </div>
                                <p class="mt-2">Loading visitor data...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <script>
        // Time ago function
        function timeAgo(date) {
            const seconds = Math.floor((new Date() - date) / 1000);
            let interval = Math.floor(seconds / 31536000);

            if (interval >= 1) return interval + ' year' + (interval === 1 ? '' : 's') + ' ago';
            interval = Math.floor(seconds / 2592000);
            if (interval >= 1) return interval + ' month' + (interval === 1 ? '' : 's') + ' ago';
            interval = Math.floor(seconds / 86400);
            if (interval >= 1) return interval + ' day' + (interval === 1 ? '' : 's') + ' ago';
            interval = Math.floor(seconds / 3600);
            if (interval >= 1) return interval + ' hour' + (interval === 1 ? '' : 's') + ' ago';
            interval = Math.floor(seconds / 60);
            if (interval >= 1) return interval + ' minute' + (interval === 1 ? '' : 's') + ' ago';
            return Math.floor(seconds) + ' seconds ago';
        }
    </script>

    <script>
        (function () {
            const tbody = document.getElementById('visitorTableBody');
            const badge = document.getElementById('visitorCountBadge');
            let visitors = {};

            function render() {
                // update navbar badge count
                if (badge) badge.textContent = Object.keys(visitors).length;

                let rows = '';
                Object.values(visitors)
                    .sort((a, b) => new Date(b.last_activity) - new Date(a.last_activity))
                    .forEach(v => {
                        rows += `<tr class="border-t">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8 flex items-center justify-center bg-blue-100 rounded-full">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">${v.ip_address || 'N/A'}</div>
                                        <div class="text-xs text-gray-500">${v.country || 'Unknown'}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="max-w-xs truncate group relative">
                                    <a href="${v.referrer || '#'}" target="_blank" class="text-sm text-blue-600 hover:underline">
                                        ${v.referrer || 'Direct / Unknown'}
                                    </a>
                                    <div class="hidden group-hover:block absolute z-10 w-64 p-2 mt-1 text-xs text-gray-600 bg-white border border-gray-200 rounded shadow-lg">
                                        ${v.referrer || 'No referrer information available'}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">${v.device_type || 'Unknown'}</div>
                                <div class="text-xs text-gray-500">${v.is_mobile ? 'Mobile' : v.is_tablet ? 'Tablet' : 'Desktop'}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-5 w-5 mr-2 text-gray-500">
                                        ${v.os && v.os.includes('Windows') ?
                                            '<i class="fab fa-windows"></i>' :
                                            v.os && v.os.includes('Mac') ?
                                            '<i class="fab fa-apple"></i>' :
                                            v.os && v.os.includes('Linux') ?
                                            '<i class="fab fa-linux"></i>' :
                                            v.os && v.os.includes('Android') ?
                                            '<i class="fab fa-android"></i>' :
                                            v.os && v.os.includes('iOS') ?
                                            '<i class="fab fa-apple"></i>' :
                                            '<i class="fas fa-question-circle"></i>'}
                                    </div>
                                    <div>
                                        <div class="text-sm text-gray-900">${v.os || 'Unknown OS'}</div>
                                        <div class="text-xs text-gray-500">${v.browser || 'Unknown Browser'}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">${v.last_activity ? new Date(v.last_activity).toLocaleString() : 'N/A'}</div>
                                <div class="text-xs text-gray-500">${v.last_activity ? timeAgo(new Date(v.last_activity)) : ''}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button onclick="startChat('${v.session_id}')" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                    Chat
                                </button>
                            </td>

                        </tr>`;
                    });
                tbody.innerHTML = rows || `
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <h3 class="text-lg font-medium text-gray-900">No active visitors</h3>
                                <p class="mt-1 text-sm text-gray-500">Visitors will appear here when they access your site.</p>
                            </div>
                        </td>
                    </tr>`;
            }

            // Initial fetch
            async function fetchVisitors() {
                try {
                    const { data } = await axios.get('{{ route('admin.visitors.active') }}');
                    visitors = {};
                    (data || []).forEach(v => visitors[v.id] = v);
                    render();
                } catch (e) {
                    console.error(e);
                }
            }
            fetchVisitors();

            // Realtime updates
            if (window.Echo) {
                window.Echo.channel('visitors')
                    .listen('VisitorJoined', (e) => {
                        visitors[e.visitor.id] = e.visitor;
                        render();
                    })
                    .listen('VisitorLeft', (e) => {
                        delete visitors[e.visitorId];
                        render();
                    });
            }

            // Auto-prune every 60s
            setInterval(() => {
                axios.post('{{ route('admin.visitors.prune') }}').then(() => {
                    fetchVisitors();
                });
            }, 60000);
        })();
        function startChat(visitorId) {
            // Implement chat initiation logic
            window.location.href = `/admin/chat?visitor=${visitorId}`;
        }
    </script>
</x-app-layout>
