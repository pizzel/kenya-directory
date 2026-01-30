<x-admin.layouts.app>
    <x-slot name="header">
        Dashboard Overview
    </x-slot>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Users -->
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-blue-500 uppercase tracking-wide">Total Users</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($stats['users']) }}</p>
                </div>
                <div class="p-3 bg-blue-50 rounded-full">
                    <i class="fas fa-users text-blue-500 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Businesses -->
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-emerald-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-emerald-500 uppercase tracking-wide">Businesses</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($stats['businesses']) }}</p>
                </div>
                <div class="p-3 bg-emerald-50 rounded-full">
                    <i class="fas fa-store text-emerald-500 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Events -->
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-purple-500 uppercase tracking-wide">Active Events</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($stats['events']) }}</p>
                </div>
                <div class="p-3 bg-purple-50 rounded-full">
                    <i class="fas fa-calendar-check text-purple-500 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Reports -->
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-red-500 uppercase tracking-wide">Pending Reports</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($stats['reports']) }}</p>
                </div>
                <div class="p-3 bg-red-50 rounded-full">
                     <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Main Chart -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-chart-line mr-2 text-blue-500"></i> Platform Activity
            </h3>
            <div class="h-64 relative">
                <canvas id="mainActivityChart"></canvas>
            </div>
        </div>

        <!-- Top Categories (Keep) -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
                <i class="fas fa-chart-pie mr-2 text-purple-500"></i> Top Categories
            </h3>
            <div class="h-56 relative mb-4">
                <canvas id="categoryChart"></canvas>
            </div>
            <div class="space-y-1">
                @foreach($topCategories as $category)
                <div class="flex items-center justify-between text-xs">
                    <span class="text-gray-600 truncate">{{ $category->name }}</span>
                    <span class="text-gray-400 font-bold ml-2">{{ $category->businesses_count }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Bottom Feed / Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Moderation Feed (Keep styling) -->
        <div class="bg-white rounded-xl shadow-sm p-6 overflow-hidden">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-800">Moderation Feed</h3>
                <a href="{{ route('admin.reports.index') }}" class="text-sm text-blue-600 hover:underline">View All</a>
            </div>
            <div class="space-y-4">
                @forelse($recentReports as $report)
                    <div class="flex items-start gap-3 pb-3 border-b border-gray-100 last:border-0 last:pb-0">
                        <div class="flex-shrink-0 mt-1">
                            <span class="flex items-center justify-center w-8 h-8 rounded-full {{ $report->status === 'pending' ? 'bg-red-50 text-red-500' : 'bg-gray-50 text-gray-400' }}">
                                <i class="fas fa-flag text-xs"></i>
                            </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate">
                                {{ $report->business->name ?? ($report->event->title ?? 'Unknown Item') }}
                            </p>
                            <p class="text-xs text-gray-500 italic truncate">"{{ $report->report_reason }}"</p>
                            <div class="flex items-center justify-between mt-1">
                                <span class="text-[10px] text-gray-400">{{ $report->created_at->diffForHumans() }}</span>
                                <a href="{{ route('admin.reports.index') }}" class="text-[10px] font-bold text-blue-600 hover:underline">REVIEW</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-400">
                        <p>No pending reports.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Users -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-800">New Users</h3>
                <a href="{{ route('admin.users.index') }}" class="text-sm text-blue-600 hover:underline">Manage</a>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($recentUsers as $user)
                    <div class="p-4 flex items-center gap-3 hover:bg-gray-50 transition-colors">
                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-xs font-bold">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-semibold text-gray-800 truncate">{{ $user->name }}</h4>
                            <p class="text-[10px] text-gray-500 truncate">{{ $user->email }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Recent Businesses -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
             <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-800">New Listings</h3>
                <a href="{{ route('admin.businesses.index') }}" class="text-sm text-blue-600 hover:underline">View All</a>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($recentBusinesses as $business)
                     <div class="p-4 flex items-center gap-3 hover:bg-gray-50 transition-colors">
                        <img src="{{ $business->getImageUrl('thumbnail') }}" class="w-10 h-10 rounded object-cover bg-gray-100" alt="Biz">
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-semibold text-gray-800 truncate">{{ $business->name }}</h4>
                            <p class="text-[10px] text-gray-500 truncate">{{ $business->categories->first()->name ?? 'General' }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @push('footer-scripts')
    <script>
        // Main activity (static data as before)
        const activityCtx = document.getElementById('mainActivityChart').getContext('2d');
        new Chart(activityCtx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Page Views',
                    data: [150, 230, 180, 320, 290, 450, 410],
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { 
                    y: { beginAtZero: true, grid: { borderDash: [2, 2] } },
                    x: { grid: { display: false } }
                }
            }
        });

        // Top Categories (Keep)
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: @json($topCategories->pluck('name')),
                datasets: [{
                    data: @json($topCategories->pluck('businesses_count')),
                    backgroundColor: ['#6366f1', '#6d28d9', '#a855f7', '#d946ef', '#ec4899'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: { legend: { display: false } }
            }
        });
    </script>
    @endpush
</x-admin.layouts.app>
