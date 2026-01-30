<x-admin.layouts.app>
    <x-slot name="header">
        Edit County: {{ $county->name }}
    </x-slot>

    <div class="max-w-xl mx-auto bg-white rounded-lg shadow-sm border border-gray-200">
        <form action="{{ route('admin.counties.update', $county) }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">County Name</label>
                <input type="text" name="name" id="name" value="{{ old('name', $county->name) }}" required 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('admin.counties.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Cancel</a>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">Update County</button>
            </div>
        </form>
    </div>
</x-admin.layouts.app>
