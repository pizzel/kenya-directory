<x-admin.layouts.app>
    <x-slot name="header">
        Add New Facility
    </x-slot>

    <div class="max-w-4xl mx-auto">
        <form action="{{ route('admin.facilities.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Facility Name</label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                placeholder="e.g. Free WiFi">
                            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Slug (Optional)</label>
                            <input type="text" name="slug" value="{{ old('slug') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                placeholder="e.g. free-wifi">
                            @error('slug') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Icon Class (FontAwesome)</label>
                            <input type="text" name="icon_class" value="{{ old('icon_class') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                placeholder="e.g. fas fa-wifi">
                            <p class="text-[10px] text-gray-400 mt-1">Visit fontawesome.com for icon classes.</p>
                            @error('icon_class') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Sticky Action Bar -->
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                    <a href="{{ route('admin.facilities.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-800">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex items-center px-6 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all shadow-sm">
                        Create Facility
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-admin.layouts.app>
