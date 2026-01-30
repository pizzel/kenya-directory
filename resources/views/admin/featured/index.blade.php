<x-admin.layouts.app>
    <x-slot:title>Manage Featured Businesses</x-slot:title>

    <div class="p-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Featured Businesses</h1>
                <p class="text-sm text-gray-600">Promote specific businesses to the featured sections of the site.</p>
            </div>
            <a href="{{ route('admin.featured.create') }}" 
               class="inline-flex items-center px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium rounded-lg transition-colors duration-150 shadow-sm">
                <i class="fas fa-star mr-2"></i>
                Add Featured Business
            </a>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
            <form id="filterForm" class="relative max-w-md">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" name="search" id="searchInput" value="{{ request('search') }}"
                    class="block w-full pl-10 pr-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent sm:text-sm"
                    placeholder="Search featured businesses...">
            </form>
        </div>

        <!-- Table Container -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden" id="tableContainer">
            @include('admin.featured._table')
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            let searchTimer;
            const tableContainer = $('#tableContainer');
            const searchInput = $('#searchInput');

            function fetchResults() {
                const search = searchInput.val();
                const url = `{{ route('admin.featured.index') }}?search=${search}`;
                
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

            searchInput.on('input', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(fetchResults, 300);
            });

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

        function removeFeatured(id) {
            if (confirm('Are you sure you want to remove the featured status for this business?')) {
                fetch(`{{ url('custom-admin/featured') }}/${id}`, {
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
