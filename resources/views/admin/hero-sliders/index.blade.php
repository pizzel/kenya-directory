<x-admin.layouts.app>
    <x-slot:title>Manage Hero Sliders</x-slot:title>

    <div class="p-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Hero Slider Management</h1>
                <p class="text-sm text-gray-600">Schedule and manage homepage hero advertisements.</p>
            </div>
            <a href="{{ route('admin.hero-sliders.create') }}" 
               class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors duration-150 shadow-sm">
                <i class="fas fa-plus mr-2"></i>
                Schedule New Slider
            </a>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
            <form id="filterForm" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="search" id="searchInput" value="{{ request('search') }}"
                        class="block w-full pl-10 pr-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent sm:text-sm"
                        placeholder="Search business name...">
                </div>
                <div>
                    <select name="status" id="statusFilter"
                        class="block w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent sm:text-sm">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active Now</option>
                        <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                    </select>
                </div>
            </form>
        </div>

        <!-- Table Container -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden" id="tableContainer">
            @include('admin.hero-sliders._table')
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            let searchTimer;
            const tableContainer = $('#tableContainer');
            const filterForm = $('#filterForm');

            function fetchResults() {
                const formData = filterForm.serialize();
                const url = `{{ route('admin.hero-sliders.index') }}?${formData}`;
                
                tableContainer.addClass('opacity-50 pointer-events-none');
                
                fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.text())
                .then(html => {
                    tableContainer.html(html);
                    tableContainer.removeClass('opacity-50 pointer-events-none');
                });
            }

            $('#searchInput').on('input', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(fetchResults, 300);
            });

            $('#statusFilter').on('change', fetchResults);

            $(document).on('click', '.pagination a', function(e) {
                e.preventDefault();
                const url = $(this).attr('href');
                
                tableContainer.addClass('opacity-50 pointer-events-none');
                
                fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.text())
                .then(html => {
                    tableContainer.html(html);
                    tableContainer.removeClass('opacity-50 pointer-events-none');
                    window.history.pushState(null, null, url);
                });
            });
        });

        function deleteSlider(id) {
            if (confirm('Are you sure you want to remove this slider schedule?')) {
                fetch(`{{ url('custom-admin/hero-sliders') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                });
            }
        }
    </script>
    @endpush
</x-admin.layouts.app>
