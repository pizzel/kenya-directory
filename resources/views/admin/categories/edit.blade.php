<x-admin.layouts.app>
    <x-slot name="header">
        Edit Category: {{ $category->name }}
    </x-slot>

    <div class="max-w-xl mx-auto bg-white rounded-lg shadow-sm border border-gray-200">
        <form action="{{ route('admin.categories.update', $category) }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Category Name</label>
                <input type="text" name="name" id="name" value="{{ old('name', $category->name) }}" required 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Slug -->
            <div>
                <label for="slug" class="block text-sm font-medium text-gray-700">Slug</label>
                <input type="text" name="slug" id="slug" value="{{ old('slug', $category->slug) }}" 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                @error('slug') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

             <!-- Icon Class -->
            <div>
                <label for="icon_class" class="block text-sm font-medium text-gray-700">Icon Class (FontAwesome)</label>
                <div class="flex gap-2">
                    <input type="text" name="icon_class" id="icon_class" value="{{ old('icon_class', $category->icon_class) }}" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                     @if($category->icon_class)
                        <div class="flex items-center justify-center w-10 h-10 bg-gray-100 rounded border border-gray-200 mt-1">
                            <i class="{{ $category->icon_class }} text-gray-600"></i>
                        </div>
                    @endif
                </div>
                 <p class="mt-1 text-xs text-gray-500">Use FontAwesome 5 classes.</p>
                @error('icon_class') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Sticky Action Bar -->
            <div class="sticky bottom-6 z-50 mt-12">
                <div class="bg-white border border-gray-200 rounded-2xl shadow-2xl p-4 flex items-center justify-between gap-8 max-w-xl mx-auto">
                    <a href="{{ route('admin.categories.index') }}" class="text-gray-500 hover:text-gray-900 font-semibold text-sm">Cancel</a>
                    <button type="submit" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-blue-500/30 transition-all transform active:scale-95">
                        Update Category
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-admin.layouts.app>
