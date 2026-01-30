<x-admin.layouts.app>
    <x-slot name="header">
        Edit Collection: {{ $collection->title }}
    </x-slot>

    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-sm border border-gray-200">
        <form action="{{ route('admin.collections.update', $collection) }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <!-- Title -->
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">Collection Title</label>
                <input type="text" name="title" id="title" value="{{ old('title', $collection->title) }}" required 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Display Order -->
            <div>
                 <label for="display_order" class="block text-sm font-medium text-gray-700">Display Order</label>
                <input type="number" name="display_order" id="display_order" value="{{ old('display_order', $collection->display_order) }}" required 
                    class="mt-1 block w-32 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                 @error('display_order') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Is Active -->
             <div class="flex items-center">
                <input id="is_active" name="is_active" type="checkbox" value="1" {{ old('is_active', $collection->is_active) ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="is_active" class="ml-2 block text-sm text-gray-900">
                    Active (Visible publicly)
                </label>
            </div>

            <div class="border-t border-gray-100 my-4 pt-4">
                <x-admin.forms.multi-select 
                    name="businesses[]" 
                    label="Select Businesses (Curated List)" 
                    :options="$businesses" 
                    :selected="$collection->businesses->pluck('id')->toArray()" 
                />
                <p class="mt-2 text-xs text-gray-500 italic">Curate the list of businesses that will appear in this landing page collection.</p>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('admin.collections.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Cancel</a>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">Update Collection</button>
            </div>
        </form>
    </div>
</x-admin.layouts.app>
