<x-admin.layouts.app>
    <x-slot:title>Add Featured Business</x-slot:title>

    <div class="p-6 max-w-2xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('admin.featured.index') }}" class="text-indigo-600 hover:text-indigo-700 font-medium flex items-center gap-2 mb-2 text-sm">
                <i class="fas fa-arrow-left"></i> Back to Featured List
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Add Featured Business</h1>
            <p class="text-sm text-gray-600">Promote a business to the featured section.</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
            <form action="{{ route('admin.featured.store') }}" method="POST">
                @csrf
                <div class="space-y-6">
                    <div>
                        <label for="business_id" class="block text-sm font-semibold text-gray-700 mb-2">Select Business *</label>
                        <select name="business_id" id="business_id" required
                            class="block w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all outline-none">
                            <option value="">-- Search and select a business --</option>
                            @foreach($availableBusinesses as $business)
                                <option value="{{ $business->id }}">
                                    {{ $business->name }} ({{ $business->county->name ?? 'No County' }})
                                </option>
                            @endforeach
                        </select>
                        @error('business_id') <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="featured_expires_at" class="block text-sm font-semibold text-gray-700 mb-2">Feature Expiration Date *</label>
                        <input type="date" name="featured_expires_at" id="featured_expires_at" required 
                            value="{{ old('featured_expires_at', now()->addMonth()->format('Y-m-d')) }}"
                            class="block w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all outline-none">
                        @error('featured_expires_at') <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div class="pt-4 flex justify-end">
                        <button type="submit" 
                            class="px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-lg shadow-md hover:shadow-lg transition-all transform hover:-translate-y-1">
                            Promote to Featured
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-admin.layouts.app>
