<x-admin.layouts.app>
    <x-slot name="header">
        Edit Business: {{ $business->name }}
    </x-slot>

    <div class="max-w-6xl mx-auto pb-24">
        <form action="{{ route('admin.businesses.update', $business) }}" method="POST" class="space-y-8" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column: Core Info -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Core Information -->
                    <section class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                            <div>
                                <h3 class="text-lg font-bold text-gray-800">Core Business Information</h3>
                                <p class="text-sm text-gray-500">Basic details and descriptions.</p>
                            </div>
                            <div class="text-right">
                                <span class="bg-blue-50 text-blue-700 px-3 py-1 rounded-full text-xs font-mono">ID: {{ $business->id }}</span>
                            </div>
                        </div>
                        <div class="p-6 space-y-6">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-semibold text-gray-700">Business Name</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $business->name) }}" required 
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <!-- Slug -->
                            <div>
                                <label for="slug" class="block text-sm font-semibold text-gray-700">Slug (URL Segment)</label>
                                <input type="text" name="slug" id="slug" value="{{ old('slug', $business->slug) }}" 
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm bg-gray-50 font-mono text-xs">
                                <p class="mt-1 text-xs text-gray-400 italic">Leave as is unless rename is required. Slug is generated automatically on create.</p>
                            </div>

                            <!-- Descriptions -->
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label for="about_us" class="block text-sm font-semibold text-gray-700">About Us (Short Intro)</label>
                                    <textarea name="about_us" id="about_us" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">{{ old('about_us', $business->about_us) }}</textarea>
                                </div>
                                <div>
                                    <label for="description" class="block text-sm font-semibold text-gray-700">Full Description</label>
                                    <textarea name="description" id="description" rows="10" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">{{ old('description', $business->description) }}</textarea>
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
                                <textarea name="address" id="address" rows="2" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">{{ old('address', $business->address) }}</textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="county_id" class="block text-sm font-semibold text-gray-700">County</label>
                                    <select name="county_id" id="county_id" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                        @foreach($counties as $county)
                                            <option value="{{ $county->id }}" {{ old('county_id', $business->county_id) == $county->id ? 'selected' : '' }}>{{ $county->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="latitude" class="block text-sm font-semibold text-gray-700">Latitude</label>
                                        <input type="text" name="latitude" id="latitude" value="{{ old('latitude', $business->latitude) }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    </div>
                                    <div>
                                        <label for="longitude" class="block text-sm font-semibold text-gray-700">Longitude</label>
                                        <input type="text" name="longitude" id="longitude" value="{{ old('longitude', $business->longitude) }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="phone_number" class="block text-sm font-semibold text-gray-700">Phone Number</label>
                                    <input type="text" name="phone_number" id="phone_number" value="{{ old('phone_number', $business->phone_number) }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="email" class="block text-sm font-semibold text-gray-700">Email Address</label>
                                    <input type="email" name="email" id="email" value="{{ old('email', $business->email) }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="website" class="block text-sm font-semibold text-gray-700">Website URL</label>
                                    <input type="url" name="website" id="website" value="{{ old('website', $business->website) }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="https://...">
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
                                :selected="$business->categories->pluck('id')->toArray()" 
                            />

                            <!-- Facilities -->
                            <div class="pt-6 border-t border-gray-100">
                                <x-admin.forms.multi-select 
                                    name="facilities[]" 
                                    label="Facilities" 
                                    :options="$facilities" 
                                    :selected="$business->facilities->pluck('id')->toArray()" 
                                />
                            </div>

                            <!-- Tags -->
                            <div class="pt-6 border-t border-gray-100">
                                <x-admin.forms.multi-select 
                                    name="tags[]" 
                                    label="Tags" 
                                    :options="$tags" 
                                    :selected="$business->tags->pluck('id')->toArray()" 
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
                                    <input type="text" name="social_links[facebook]" value="{{ old('social_links.facebook', $business->social_links['facebook'] ?? '') }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700">Instagram URL</label>
                                    <input type="text" name="social_links[instagram]" value="{{ old('social_links.instagram', $business->social_links['instagram'] ?? '') }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700">YouTube URL</label>
                                    <input type="text" name="social_links[youtube]" value="{{ old('social_links.youtube', $business->social_links['youtube'] ?? '') }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700">LinkedIn URL</label>
                                    <input type="text" name="social_links[linkedin]" value="{{ old('social_links.linkedin', $business->social_links['linkedin'] ?? '') }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Media Gallery & Hero Slider -->
                    <section class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                            <div>
                                <h3 class="text-lg font-bold text-gray-800">Business Gallery & Hero Images</h3>
                                <p class="text-sm text-gray-500">Manage listing photos and hero slider images.</p>
                            </div>
                        </div>
                        <div class="p-6 space-y-8">
                            <!-- Current Images -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-4">Current Media ({{ $business->getMedia('images')->count() }})</label>
                                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                    @forelse($business->getMedia('images') as $media)
                                        <div class="relative group aspect-video rounded-lg overflow-hidden border border-gray-200 bg-gray-50">
                                            <img src="{{ $media->getUrl('card') }}" alt="Gallery image" class="w-full h-full object-cover">
                                            
                                            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                                <!-- Delete Button -->
                                                <button type="button" 
                                                    onclick="if(confirm('Are you sure you want to delete this image?')) document.getElementById('delete-media-{{ $media->id }}').submit();"
                                                    class="p-2 bg-red-600 text-white rounded-lg hover:bg-red-700 shadow-lg" title="Delete Image">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                                <a href="{{ $media->getUrl() }}" target="_blank" class="p-2 bg-white text-gray-800 rounded-lg hover:bg-gray-100 shadow-lg" title="View Full Image">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                </a>
                                            </div>
                                            
                                            @if($loop->first)
                                                <div class="absolute top-2 left-2 px-2 py-0.5 bg-blue-600 text-[10px] text-white font-bold rounded shadow-sm">
                                                    PRIMARY / HERO
                                                </div>
                                            @endif
                                        </div>
                                    @empty
                                        <div class="col-span-full py-12 border-2 border-dashed border-gray-200 rounded-xl flex flex-col items-center justify-center text-gray-400">
                                            <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            <p class="text-sm">No images uploaded yet.</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            <!-- Upload New Images -->
                            <div class="pt-8 border-t border-gray-100">
                                <label for="images" class="block text-sm font-semibold text-gray-700 mb-2">Upload New Photos</label>
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
                                        <p class="text-xs text-gray-500">PNG, JPG, WEBP up to 10MB each</p>
                                    </div>
                                </div>
                                <div id="image-preview" class="grid grid-cols-4 md:grid-cols-6 gap-4 mt-4"></div>
                            </div>
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
                                @php
                                    $statusColors = [
                                        'active' => 'bg-green-50 text-green-700 border-green-200',
                                        'pending_approval' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                        'delisted' => 'bg-red-50 text-red-700 border-red-200',
                                        'closed_permanently' => 'bg-gray-100 text-gray-700 border-gray-200',
                                    ];
                                    $currentColor = $statusColors[$business->status] ?? 'bg-white';
                                @endphp
                                <select name="status" id="status" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm {{ $currentColor }}">
                                    <option value="pending_approval" {{ old('status', $business->status) == 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                                    <option value="active" {{ old('status', $business->status) == 'active' ? 'selected' : '' }}>Active (Live)</option>
                                    <option value="delisted" {{ old('status', $business->status) == 'delisted' ? 'selected' : '' }}>Delisted (Hidden)</option>
                                    <option value="closed_permanently" {{ old('status', $business->status) == 'closed_permanently' ? 'selected' : '' }}>Closed Permanently</option>
                                </select>
                            </div>

                            <!-- Owner -->
                            <div>
                                <label for="user_id" class="block text-sm font-semibold text-gray-700">Business Owner</label>
                                <select name="user_id" id="user_id" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('user_id', $business->user_id) == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <hr class="border-gray-100">

                            <!-- Verification & Features -->
                            <div class="space-y-4">
                                <label class="relative flex items-center group cursor-pointer">
                                    <input type="checkbox" name="is_verified" value="1" {{ old('is_verified', $business->is_verified) ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <span class="ml-3 text-sm font-medium text-gray-700 group-hover:text-blue-600 transition-colors">Verified Listing</span>
                                </label>

                                <label class="relative flex items-center group cursor-pointer">
                                    <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $business->is_featured) ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
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
                                <input type="text" name="price_range" id="price_range" value="{{ old('price_range', $business->price_range) }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="$, $$, $$$">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="min_price" class="block text-sm font-semibold text-gray-700">Min Price</label>
                                    <input type="number" step="0.01" name="min_price" id="min_price" value="{{ old('min_price', $business->min_price) }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="max_price" class="block text-sm font-semibold text-gray-700">Max Price</label>
                                    <input type="number" step="0.01" name="max_price" id="max_price" value="{{ old('max_price', $business->max_price) }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
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
                                <input type="datetime-local" name="featured_expires_at" id="featured_expires_at" value="{{ old('featured_expires_at', $business->featured_expires_at ? $business->featured_expires_at->format('Y-m-d\TH:i') : '') }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            </div>
                            <div class="pt-4 border-t border-gray-100">
                                <label for="hero_slider_paid_at" class="block text-sm font-semibold text-gray-700">Hero Slider Paid At</label>
                                <input type="datetime-local" name="hero_slider_paid_at" id="hero_slider_paid_at" value="{{ old('hero_slider_paid_at', $business->hero_slider_paid_at ? $business->hero_slider_paid_at->format('Y-m-d\TH:i') : '') }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="hero_slider_set_to_expire_at" class="block text-sm font-semibold text-gray-700">Hero Slider Expires At</label>
                                <input type="datetime-local" name="hero_slider_set_to_expire_at" id="hero_slider_set_to_expire_at" value="{{ old('hero_slider_set_to_expire_at', $business->hero_slider_set_to_expire_at ? $business->hero_slider_set_to_expire_at->format('Y-m-d\TH:i') : '') }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            </div>
                        </div>
                    </section>
                    
                    <!-- Meta Stats (Read Only) -->
                     <section class="bg-gray-50 rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-4 border-b border-gray-200">
                            <h3 class="text-sm font-bold text-gray-600 uppercase">Platform Meta</h3>
                        </div>
                        <div class="p-4 space-y-3">
                            <div class="flex justify-between text-xs">
                                <span class="text-gray-500">Total Views:</span>
                                <span class="font-bold text-gray-800">{{ number_format($business->views_count) }}</span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-gray-500">Rating:</span>
                                <span class="font-bold text-gray-800">{{ $business->average_rating ?? '0.0' }} ({{ $business->reviews_count_approved ?? 0 }} reviews)</span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-gray-500">Created:</span>
                                <span class="font-bold text-gray-800">{{ $business->created_at->format('M j, Y H:i') }}</span>
                            </div>
                             <div class="flex justify-between text-xs">
                                <span class="text-gray-500">Last Updated:</span>
                                <span class="font-bold text-gray-800">{{ $business->updated_at->format('M j, Y H:i') }}</span>
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
                            <span class="text-gray-400 uppercase font-bold tracking-widest block">Editing Business</span>
                            <span class="text-gray-700 font-bold truncate max-w-[200px] block">{{ $business->name }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                         <button type="submit" class="inline-flex items-center px-10 py-4 bg-blue-600 hover:bg-blue-700 text-white text-base font-bold rounded-xl shadow-xl shadow-blue-500/30 transition-all transform hover:-translate-y-1 active:scale-95 active:translate-y-0">
                            <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            Update Business Details
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-admin.layouts.app>

<!-- Hidden Deletion Forms -->
@foreach($business->getMedia('images') as $media)
<form id="delete-media-{{ $media->id }}" action="{{ route('admin.businesses.media.destroy', [$business, $media]) }}" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>
@endforeach

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
