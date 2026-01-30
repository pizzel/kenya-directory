<x-admin.layouts.app>
    <x-slot name="header">
        Performance & SEO Audit
    </x-slot>

    <div class="space-y-6">
        <!-- Control Panel -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <h2 class="text-lg font-medium text-gray-900 mb-4">
                <i class="fas fa-cog text-gray-400 mr-2"></i> Audit Configuration
            </h2>
            
            <form action="{{ route('admin.performance.run') }}" method="POST" onsubmit="return showLoading()" class="space-y-4">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                    <!-- URL Input -->
                    <div class="md:col-span-6">
                        <label for="url" class="block text-sm font-medium text-gray-700 mb-1">Target URL</label>
                        <input type="url" name="url" id="url" required
                            value="{{ old('url', route('home')) }}"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                            placeholder="http://kenya-directory.test/">
                    </div>

                    <!-- Strategy -->
                    <div class="md:col-span-3">
                        <label for="strategy" class="block text-sm font-medium text-gray-700 mb-1">Device Strategy</label>
                        <select name="strategy" id="strategy" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="mobile" {{ old('strategy') == 'mobile' ? 'selected' : '' }}>📱 Mobile</option>
                            <option value="desktop" {{ old('strategy') == 'desktop' ? 'selected' : '' }}>💻 Desktop</option>
                        </select>
                    </div>

                    <!-- Action -->
                    <div class="md:col-span-3">
                        <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150 h-[38px]">
                            <i class="fas fa-play mr-2"></i> Run Audit
                        </button>
                    </div>
                </div>

                <!-- Advanced Options -->
                <div class="flex items-center space-x-2 mt-2">
                    <input type="checkbox" name="disable_throttling" id="disable_throttling" value="1" {{ old('disable_throttling') ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <label for="disable_throttling" class="text-xs text-gray-600">
                        Disable CPU Throttling (Fixes "Device slower than expected" warning)
                    </label>
                </div>
            </form>
        </div>

        <!-- Alerts -->
        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm" role="alert">
                <p class="font-bold">Success</p>
                <p>{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm" role="alert">
                <p class="font-bold">Error</p>
                <p>{{ session('error') }}</p>
            </div>
        @endif

        <!-- Main Report Card -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Latest Lighthouse Report</h3>
                <div class="flex items-center space-x-4">
                    @if($lastRun)
                        <span class="text-xs text-gray-500 font-medium">Last updated: {{ date('M d, Y h:i A', $lastRun) }}</span>
                        <a href="{{ asset('reports/performance.html') }}" target="_blank" class="inline-flex items-center px-3 py-1 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            <i class="fas fa-external-link-alt mr-2"></i> Open in New Tab
                        </a>
                    @endif
                </div>
            </div>
            
            <div class="p-0">
                @if($lastRun)
                    <div class="w-full relative" style="height: 1200px; min-height: 85vh;">
                        <!-- Loading Indicator for Iframe -->
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none z-0">
                            <i class="fas fa-circle-notch fa-spin text-gray-300 text-4xl"></i>
                        </div>
                        <iframe class="w-full h-full border-0 relative z-10 bg-white" src="{{ asset('reports/performance.html') }}?v={{ time() }}" allowfullscreen></iframe>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-16 text-center">
                        <img src="https://lh3.googleusercontent.com/POa2A6_f4aZ4Xg0aYI0u9XhXV8yLhP7uH0h0tG0_B0_B0_B0_B0" alt="Lighthouse" class="w-24 h-24 opacity-50 mb-4">
                        <h4 class="text-xl font-medium text-gray-500">No Report Generated Yet</h4>
                        <p class="text-gray-400 mt-2">Configure the audit above and click "Run Audit" to start.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Loading Script -->
    <script>
    function showLoading() {
        const btn = document.querySelector('button[type="submit"]');
        
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-2"></i> Running...';
        btn.classList.add('opacity-75', 'cursor-not-allowed');
        
        return true; 
    }
    </script>
</x-admin.layouts.app>
