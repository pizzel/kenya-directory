<x-admin.layouts.app>
    <x-slot name="header">
        Create Business Listing
    </x-slot>

    <div class="max-w-6xl mx-auto pb-24">
        <form action="{{ route('admin.businesses.store') }}" method="POST" class="space-y-8" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column: Core Info -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Core Information -->
                    <section class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                            <h3 class="text-lg font-bold text-gray-800">Core Business Information</h3>
                            <p class="text-sm text-gray-500">Basic details and descriptions.</p>
                        </div>
                        <div class="p-6 space-y-6">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-semibold text-gray-700">Business Name</label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required 
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="Enter business name">
                                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <!-- Descriptions -->
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label for="about_us" class="block text-sm font-semibold text-gray-700">About Us (Short Intro)</label>
                                    <textarea name="about_us" id="about_us" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">{{ old('about_us') }}</textarea>
                                </div>
                                <div>
                                    <label for="description" class="block text-sm font-semibold text-gray-700">Full Description</label>
                                    <textarea name="description" id="description" rows="10" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">{{ old('description') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Location & Contact -->
                    <section class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                            <h3 class="text-lg font-bold text-gray-800">Location & Contact Details</h3>
                        </div>
                        <div class="p-6 space-y-6">
                             <div>
                                <label for="address" class="block text-sm font-semibold text-gray-700">Physical Address</label>
                                <textarea name="address" id="address" rows="2" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">{{ old('address') }}</textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="county_id" class="block text-sm font-semibold text-gray-700">County</label>
                                    <select name="county_id" id="county_id" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                        <option value="">Select a county...</option>
                                        @foreach($counties as $county)
                                            <option value="{{ $county->id }}" {{ old('county_id') == $county->id ? 'selected' : '' }}>{{ $county->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="latitude" class="block text-sm font-semibold text-gray-700">Latitude</label>
                                        <input type="text" name="latitude" id="latitude" value="{{ old('latitude') }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    </div>
                                    <div>
                                        <label for="longitude" class="block text-sm font-semibold text-gray-700">Longitude</label>
                                        <input type="text" name="longitude" id="longitude" value="{{ old('longitude') }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="phone_number" class="block text-sm font-semibold text-gray-700">Phone Number</label>
                                    <input type="text" name="phone_number" id="phone_number" value="{{ old('phone_number') }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="email" class="block text-sm font-semibold text-gray-700">Email Address</label>
                                    <input type="email" name="email" id="email" value="{{ old('email') }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="website" class="block text-sm font-semibold text-gray-700">Website URL</label>
                                    <input type="url" name="website" id="website" value="{{ old('website') }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="https://...">
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Activities & Facilities -->
                    <section class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                            <h3 class="text-lg font-bold text-gray-800">Activities & Facilities</h3>
                        </div>
                        <div class="p-6 space-y-8">
                            <!-- Categories -->
                            <x-admin.forms.multi-select 
                                name="categories[]" 
                                label="Activities" 
                                :options="$categories" 
                                :selected="old('categories', [])" 
                            />

                            <!-- Facilities -->
                            <div class="pt-6 border-t border-gray-100">
                                <x-admin.forms.multi-select 
                                    name="facilities[]" 
                                    label="Facilities" 
                                    :options="$facilities" 
                                    :selected="old('facilities', [])" 
                                />
                            </div>

                            <!-- Tags -->
                            <div class="pt-6 border-t border-gray-100">
                                <x-admin.forms.multi-select 
                                    name="tags[]" 
                                    label="Tags" 
                                    :options="$tags" 
                                    :selected="old('tags', [])" 
                                />
                            </div>
                        </div>
                    </section>
                    
                     <!-- Social Links -->
                    <section class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                            <h3 class="text-lg font-bold text-gray-800">Social Media Links</h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700">Facebook URL</label>
                                    <input type="text" name="social_links[facebook]" value="{{ old('social_links.facebook') }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700">Instagram URL</label>
                                    <input type="text" name="social_links[instagram]" value="{{ old('social_links.instagram') }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700">YouTube URL</label>
                                    <input type="text" name="social_links[youtube]" value="{{ old('social_links.youtube') }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700">LinkedIn URL</label>
                                    <input type="text" name="social_links[linkedin]" value="{{ old('social_links.linkedin') }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Image Upload -->
                    <section class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                            <h3 class="text-lg font-bold text-gray-800">Business Images</h3>
                            <p class="text-sm text-gray-500">Upload initial photos for this listing. First image will be used as the primary hero.</p>
                        </div>
                        <div class="p-6">
                            <label for="images" class="block text-sm font-semibold text-gray-700 mb-2">Upload Photos</label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:border-blue-400 transition-colors bg-gray-50/50">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 00-4 4H12a4 4 0 00-4-4v-4m32-4l-3.172-3.172a4 4 0 015.656 0L42 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-gray-600">
                                        <label for="images" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                            <span>Upload multiple files</span>
                                            <input id="images" name="images[]" type="file" class="sr-only" multiple accept="image/*">
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG up to 10MB each</p>
                                </div>
                            </div>
                            <div id="image-preview" class="grid grid-cols-4 md:grid-cols-6 gap-4 mt-4"></div>
                        </div>
                    </section>
                </div>

                <!-- Right Column: Meta & Sidebar -->
                <div class="space-y-8">
                    <!-- Status & Ownership -->
                    <section class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                            <h3 class="text-lg font-bold text-gray-800">Status & Ownership</h3>
                        </div>
                        <div class="p-6 space-y-6">
                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-sm font-semibold text-gray-700">Listing Status</label>
                                <select name="status" id="status" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    <option value="pending_approval" {{ old('status') == 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                                    <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active (Live)</option>
                                    <option value="delisted" {{ old('status') == 'delisted' ? 'selected' : '' }}>Delisted (Hidden)</option>
                                    <option value="closed_permanently" {{ old('status') == 'closed_permanently' ? 'selected' : '' }}>Closed Permanently</option>
                                </select>
                            </div>

                            <!-- Owner -->
                            <div>
                                <label for="user_id" class="block text-sm font-semibold text-gray-700">Business Owner</label>
                                <select name="user_id" id="user_id" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    <option value="">Select an owner...</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <hr class="border-gray-100">

                            <!-- Verification & Features -->
                            <div class="space-y-4">
                                <label class="relative flex items-center group cursor-pointer">
                                    <input type="checkbox" name="is_verified" value="1" {{ old('is_verified') ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <span class="ml-3 text-sm font-medium text-gray-700 group-hover:text-blue-600 transition-colors">Verified Listing</span>
                                </label>

                                <label class="relative flex items-center group cursor-pointer">
                                    <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <span class="ml-3 text-sm font-medium text-gray-700 group-hover:text-blue-600 transition-colors">Featured Listing</span>
                                </label>
                            </div>
                        </div>
                    </section>

                    <!-- Pricing Info -->
                    <section class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                            <h3 class="text-lg font-bold text-gray-800">Pricing Info</h3>
                        </div>
                        <div class="p-6 space-y-4">
                             <div>
                                <label for="price_range" class="block text-sm font-semibold text-gray-700">Label (e.g. $, $$, $$$)</label>
                                <input type="text" name="price_range" id="price_range" value="{{ old('price_range') }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="$, $$, $$$">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="min_price" class="block text-sm font-semibold text-gray-700">Min Price</label>
                                    <input type="number" step="0.01" name="min_price" id="min_price" value="{{ old('min_price') }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="max_price" class="block text-sm font-semibold text-gray-700">Max Price</label>
                                    <input type="number" step="0.01" name="max_price" id="max_price" value="{{ old('max_price') }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                </div>
                            </div>
                        </div>
                    </section>
                    
                    <!-- Promo Dates -->
                    <section class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                            <h3 class="text-lg font-bold text-gray-800">Promo Dates</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <label for="featured_expires_at" class="block text-sm font-semibold text-gray-700">Featured Expires At</label>
                                <input type="datetime-local" name="featured_expires_at" id="featured_expires_at" value="{{ old('featured_expires_at') }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            </div>
                            <div class="pt-4 border-t border-gray-100">
                                <label for="hero_slider_paid_at" class="block text-sm font-semibold text-gray-700">Hero Slider Paid At</label>
                                <input type="datetime-local" name="hero_slider_paid_at" id="hero_slider_paid_at" value="{{ old('hero_slider_paid_at') }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="hero_slider_set_to_expire_at" class="block text-sm font-semibold text-gray-700">Hero Slider Expires At</label>
                                <input type="datetime-local" name="hero_slider_set_to_expire_at" id="hero_slider_set_to_expire_at" value="{{ old('hero_slider_set_to_expire_at') }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            </div>
                        </div>
                    </section>
                </div>
            </div>

            <!-- Save Action Bar (Sticky version) -->
            <div class="sticky bottom-6 z-50 mt-12">
                <div class="bg-white border border-gray-200 rounded-2xl shadow-2xl p-4 flex items-center justify-between gap-8 max-w-5xl mx-auto">
                    <div class="flex items-center gap-4">
                        <a href="{{ route('admin.businesses.index') }}" class="flex items-center text-gray-500 hover:text-gray-900 font-semibold text-sm transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                            Back to List
                        </a>
                        <div class="h-6 w-px bg-gray-200 hidden sm:block"></div>
                        <div class="hidden lg:block text-xs">
                            <span class="text-gray-400 uppercase font-bold tracking-widest block">New Listing</span>
                            <span class="text-gray-700 font-bold block">Creation Mode</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                         <button type="submit" class="inline-flex items-center px-10 py-4 bg-slate-900 hover:bg-slate-800 text-white text-base font-bold rounded-xl shadow-xl shadow-slate-900/30 transition-all transform hover:-translate-y-1 active:scale-95 active:translate-y-0">
                            <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                            Create Business Posting
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-admin.layouts.app>

<script>
document.getElementById('images').addEventListener('change', function(e) {
    const preview = document.getElementById('image-preview');
    preview.innerHTML = '';
    
    Array.from(this.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = function(ex) {
            const div = document.createElement('div');
            div.className = 'relative aspect-video rounded-lg overflow-hidden border border-gray-200';
            div.innerHTML = `<img src="${ex.target.result}" class="w-full h-full object-cover">`;
            preview.appendChild(div);
        }
        reader.readAsDataURL(file);
    });
});
</script>
