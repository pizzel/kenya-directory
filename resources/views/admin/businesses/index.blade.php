<x-admin.layouts.app>
    <x-slot name="header">
        Business Listings
    </x-slot>

    <div x-data="{ 
        search: '{{ request('search') }}', 
        status: '{{ request('status') }}',
        loading: false,
        fetchResults(url = null) {
            this.loading = true;
            const params = new URLSearchParams();
            if (this.search) params.append('search', this.search);
            if (this.status) params.append('status', this.status);
            
            const fetchUrl = url || `{{ route('admin.businesses.index') }}?${params.toString()}`;
            
            fetch(fetchUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                this.$refs.results.innerHTML = data.html;
                this.loading = false;
                // Update URL without reload
                if (!url) {
                    const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
                    window.history.pushState({}, '', newUrl);
                } else {
                    window.history.pushState({}, '', url);
                }
            });
        }
    }" 
    x-init="$watch('search', value => fetchResults()); $watch('status', value => fetchResults())"
    class="bg-white rounded-lg shadow-sm border border-gray-200">
        <!-- Toolbar -->
        <div class="p-4 border-b border-gray-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
             <div class="flex-1 max-w-lg flex gap-2">
                <div class="relative flex-1">
                      <input type="text" 
                        x-model.debounce.300ms="search" 
                        class="block w-full pl-3 pr-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Search businesses...">
                      <div x-show="loading" class="absolute right-3 top-2.5">
                          <svg class="animate-spin h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                      </div>
                </div>
                  <select 
                    x-model="status" 
                    class="block w-40 pl-3 pr-10 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="pending_approval">Pending</option>
                    <option value="delisted">Delisted</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.businesses.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    New Business
                </a>
            </div>
        </div>

        <!-- Table Container -->
        <div x-ref="results" class="overflow-x-auto">
            @include('admin.businesses._table', ['businesses' => $businesses])
        </div>
    </div>
</x-admin.layouts.app>
