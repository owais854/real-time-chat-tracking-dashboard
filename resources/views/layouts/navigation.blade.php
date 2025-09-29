<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">

                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link
                        :href="auth()->user()->role === 'admin' ? route('dashboard') : route('agent.dashboard')"
                        :active="request()->routeIs(auth()->user()->role === 'admin' ? 'dashboard' : 'agent.dashboard')"
                    >
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                            </svg>
                            {{ __('Dashboard') }}
                        </div>
                    </x-nav-link>

                    <x-nav-link :href="route('admin.history')" :active="request()->routeIs('admin.history')">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd" />
                            </svg>
                            {{ __('Chat History') }}
                        </div>
                    </x-nav-link>
                    @if(auth()->check() && auth()->user()->role === 'admin')
                    <x-nav-link :href="route('admin.agents.index')" :active="request()->routeIs('admin.agents.*')">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                            </svg>
                            {{ __('Agent Management') }}
                        </div>
                    </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Navigation Controls -->
            <div class="flex items-center space-x-2" style="margin-left: 530px;">
                <!-- Visitor Monitor Button -->
                <a href="{{ route('admin.visitors') }}" class="relative p-2 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span id="visitorCountBadge"
                          class="absolute -top-1 -right-1 text-xs bg-red-600 text-white rounded-full h-5 w-5 flex items-center justify-center">0</span>
                </a>

                <!-- Chat Button -->
                <a href="{{ route('admin.chat') }}" class="p-2 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </a>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                          d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                          clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                             onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                                           onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>

<script>
    // Real-time visitor count updates for navigation
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Navigation: Initializing visitor count tracking...');

        // Wait for Echo to be available
        function waitForEcho() {
            if (window.Echo) {
                console.log('Navigation: Echo is available, joining public channel...');

                // Listen to visitor online/offline events
                window.Echo.channel('visitors.public')
                    .listen('visitor.online', (e) => {
                        console.log('Navigation: Visitor came online event received:', e);
                        if (e.visitor && e.visitor.ip_address) {
                            console.log('Navigation: Processing online event for IP:', e.visitor.ip_address);
                            updateVisitorCountBadge();
                        }
                    })
                    .listen('visitor.offline', (e) => {
                        console.log('Navigation: Visitor went offline event received:', e);
                        if (e.visitor && e.visitor.ip_address) {
                            console.log('Navigation: Processing offline event for IP:', e.visitor.ip_address);
                            updateVisitorCountBadge();
                        }
                    })
                    .error((error) => {
                        console.error('Navigation: Public channel error:', error);
                    });
            } else {
                console.log('Navigation: Echo not ready yet, retrying in 500ms...');
                setTimeout(waitForEcho, 500);
            }
        }

        // Start waiting for Echo
        waitForEcho();

        // Initial load of visitor count
        updateVisitorCountBadge();

        // Auto refresh visitor count every 10 seconds
        setInterval(function() {
            console.log('Navigation: Auto-refreshing visitor count...');
            updateVisitorCountBadge();
        }, 10000);

        // Function to update visitor count badge
        function updateVisitorCountBadge() {
            console.log('Navigation: Updating visitor count badge...');
            fetch('/admin/visitors/active')
                .then(response => response.json())
                .then(visitors => {
                    console.log('Navigation: Fetched active visitors:', visitors);

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

                    console.log('Navigation: Unique active visitors:', uniqueVisitors.length);
                    const badge = document.getElementById('visitorCountBadge');
                    if (badge) {
                        badge.textContent = uniqueVisitors.length;
                        console.log('Navigation: Updated visitor count badge to:', uniqueVisitors.length);
                    } else {
                        console.log('Navigation: Visitor count badge not found');
                    }
                })
                .catch(error => console.error('Navigation: Error fetching visitors:', error));
        }
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
    (function(){
        const badge = document.getElementById('visitorCountBadge');
        let count = 0;

        // Function to update visitor count
        function updateVisitorCount() {
            console.log('Navigation: Updating visitor count...');
            fetch('/admin/visitors/all')
                .then(response => response.json())
                .then(visitors => {
                    console.log('Navigation: Fetched visitors:', visitors);
                    // Filter to only show truly online visitors (those with recent activity)
                    const onlineVisitors = visitors.filter(visitor => {
                        // Check if visitor is active (from database is_active field or recent activity)
                        const isActive = visitor.is_active === true ||
                            (visitor.last_activity && new Date(visitor.last_activity) > new Date(Date.now() - 60000)); // 1 minute threshold
                        return isActive;
                    });

                    console.log('Navigation: Online visitors:', onlineVisitors.length);
                    badge.textContent = onlineVisitors.length;
                })
                .catch(error => console.error('Navigation: Error fetching visitors:', error));
        }

        // Initial fetch count
        updateVisitorCount();

        // Wait for Echo to be available
        function waitForEcho() {
            if (window.Echo) {
                console.log('Navigation: Echo is available, joining public channel...');

                // Listen to visitor online/offline events
                window.Echo.channel('visitors.public')
                    .listen('visitor.online', (e) => {
                        console.log('Navigation: Visitor came online event received:', e);
                        if (e.visitor && e.visitor.ip_address) {
                            console.log('Navigation: Processing online event for IP:', e.visitor.ip_address);
                            updateVisitorCount();
                        } else {
                            console.log('Navigation: No visitor data or IP address in online event');
                        }
                    })
                    .listen('visitor.offline', (e) => {
                        console.log('Navigation: Visitor went offline event received:', e);
                        if (e.visitor && e.visitor.ip_address) {
                            console.log('Navigation: Processing offline event for IP:', e.visitor.ip_address);
                            updateVisitorCount();
                        } else {
                            console.log('Navigation: No visitor data or IP address in offline event');
                        }
                    })
                    .error((error) => {
                        console.error('Navigation: Public channel error:', error);
                    });
            } else {
                console.log('Navigation: Echo not ready yet, retrying in 500ms...');
                setTimeout(waitForEcho, 500);
            }
        }

        // Start waiting for Echo
        waitForEcho();

        // Auto refresh visitor count every 10 seconds to ensure accuracy
        setInterval(function() {
            console.log('Navigation: Auto-refreshing visitor count...');
            updateVisitorCount();
        }, 10000);
    })();
</script>
