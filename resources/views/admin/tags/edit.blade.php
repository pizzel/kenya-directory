<x-admin.layouts.app>
    <x-slot name="header">
        Edit Tag: {{ $tag->name }}
    </x-slot>

    <div class="max-w-xl mx-auto">
        <form action="{{ route('admin.tags.update', $tag) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Tag Name</label>
                        <input type="text" name="name" value="{{ old('name', $tag->name) }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Slug</label>
                        <input type="text" name="slug" value="{{ old('slug', $tag->slug) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        @error('slug') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                    <a href="{{ route('admin.tags.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-800">
                        Back to List
                    </a>
                    <button type="submit" class="inline-flex items-center px-6 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-white hover:bg-blue-700 transition-all shadow-sm">
                        Update Tag
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-admin.layouts.app>
