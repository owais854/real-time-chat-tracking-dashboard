<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard Overview') }}
            </h2>
            <div class="text-sm text-gray-500">
                Last updated: {{ now()->format('M d, Y h:i A') }}
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-blue-500">
                    <div class="text-gray-500 font-medium">Total Messages</div>
                    <div class="text-3xl font-bold text-gray-900">{{ $totalMessages ?? 0 }}</div>
                    <div class="text-sm text-green-600 mt-1">+12% from last week</div>
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

@push('scripts')
<script>
    // Auto refresh every 30 seconds
    setInterval(function() {
        window.location.reload();
    }, 30000);
</script>
@endpush
