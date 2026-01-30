<x-admin.layouts.app>
    <x-slot name="header">
        Moderation Queue (Reports)
    </x-slot>

    <div x-data="{ 
        search: '{{ request('search') }}', 
        loading: false,
        fetchResults(url = null) {
            this.loading = true;
            const params = new URLSearchParams();
            if (this.search) params.append('search', this.search);
            
            const fetchUrl = url || `{{ route('admin.reports.index') }}?${params.toString()}`;
            
            fetch(fetchUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                this.$refs.results.innerHTML = data.html;
                this.loading = false;
                if (!url) {
                    const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
                    window.history.pushState({}, '', newUrl);
                } else {
                    window.history.pushState({}, '', url);
                }
            });
        }
    }" 
    x-init="$watch('search', value => fetchResults())"
    class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-4 border-b border-gray-200">
            <div class="relative max-w-sm">
                <input type="text" 
                    x-model.debounce.300ms="search" 
                    class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Search reports...">
                <div x-show="loading" class="absolute right-3 top-2.5">
                    <svg class="animate-spin h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </div>
            </div>
        </div>

        <div x-ref="results" class="overflow-x-auto" @click="if($event.target.tagName === 'A' && $event.target.closest('nav')) { $event.preventDefault(); fetchResults($event.target.href); }">
            @include('admin.reports._table', ['reports' => $reports])
        </div>
    </div>
</x-admin.layouts.app>
