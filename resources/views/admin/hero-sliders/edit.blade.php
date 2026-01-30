<x-admin.layouts.app>
    <x-slot:title>Edit Hero Slider</x-slot:title>

    <div class="p-6 max-w-4xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('admin.hero-sliders.index') }}" class="text-indigo-600 hover:text-indigo-700 font-medium flex items-center gap-2 mb-2">
                <i class="fas fa-arrow-left"></i> Back to Sliders
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Edit Slider Schedule</h1>
            <p class="text-sm text-gray-600">Modify an existing homepage hero placement.</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
            <form action="{{ route('admin.hero-sliders.update', $heroSlider) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Business Selection -->
                    <div class="md:col-span-2">
                        <label for="business_id" class="block text-sm font-semibold text-gray-700 mb-2">Select Business *</label>
                        <select name="business_id" id="business_id" required
                            class="block w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all outline-none">
                            @foreach($businesses as $business)
                                <option value="{{ $business->id }}" {{ old('business_id', $heroSlider->business_id) == $business->id ? 'selected' : '' }}>
                                    {{ $business->name }} ({{ $business->county->name ?? 'No County' }})
                                </option>
                            @endforeach
                        </select>
                        @error('business_id') <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <!-- Activation Date -->
                    <div>
                        <label for="activated_at" class="block text-sm font-semibold text-gray-700 mb-2">Activation Date & Time *</label>
                        <input type="datetime-local" name="activated_at" id="activated_at" required 
                            value="{{ old('activated_at', $heroSlider->activated_at->format('Y-m-d\TH:i')) }}"
                            class="block w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all outline-none">
                        @error('activated_at') <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <!-- Expiry Date -->
                    <div>
                        <label for="set_to_expire_at" class="block text-sm font-semibold text-gray-700 mb-2">Expiry Date & Time *</label>
                        <input type="datetime-local" name="set_to_expire_at" id="set_to_expire_at" required 
                            value="{{ old('set_to_expire_at', $heroSlider->set_to_expire_at->format('Y-m-d\TH:i')) }}"
                            class="block w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all outline-none">
                        @error('set_to_expire_at') <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <!-- Amount Paid -->
                    <div>
                        <label for="amount_paid" class="block text-sm font-semibold text-gray-700 mb-2">Amount Paid (KES)</label>
                        <input type="number" step="0.01" name="amount_paid" id="amount_paid"
                            value="{{ old('amount_paid', $heroSlider->amount_paid) }}"
                            class="block w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all outline-none">
                        @error('amount_paid') <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <!-- Package Name -->
                    <div>
                        <label for="package_name" class="block text-sm font-semibold text-gray-700 mb-2">Package Name</label>
                        <input type="text" name="package_name" id="package_name" placeholder="e.g. Gold Weekly"
                            value="{{ old('package_name', $heroSlider->package_name) }}"
                            class="block w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all outline-none">
                        @error('package_name') <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <!-- Notes -->
                    <div class="md:col-span-2">
                        <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">Internal Notes</label>
                        <textarea name="notes" id="notes" rows="3"
                            class="block w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all outline-none">{{ old('notes', $heroSlider->notes) }}</textarea>
                    </div>
                </div>

                <div class="mt-8 flex justify-end">
                    <button type="submit" 
                        class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg shadow-md hover:shadow-lg transition-all transform hover:-translate-y-1">
                        Update Schedule
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-admin.layouts.app>
