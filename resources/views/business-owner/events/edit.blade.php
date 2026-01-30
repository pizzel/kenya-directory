<x-business-owner-layout>
    {{-- DIRECT CSS LOAD --}}
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <style>
        .ts-wrapper.multi .ts-control > div { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; border-radius: 4px; }
        select[multiple] { display: none; }
        
        /* Photo Grid */
        .photo-gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 1rem; }
        .photo-card { border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; }
        .photo-card img { width: 100%; height: 100px; object-fit: cover; display: block; }
        .photo-actions { padding: 0.5rem; background: #f8fafc; font-size: 0.8rem; }
    </style>

    <x-slot name="header">
        <div class="bo-page-header">
            <div>
                <h2>Edit Event</h2>
                <p style="color: #64748b; font-size: 0.9rem; margin-top: 4px;">{{ $event->title }}</p>
            </div>
            <a href="{{ route('business-owner.events.index') }}" class="bo-button-secondary">Cancel</a>
        </div>
    </x-slot>

    <div class="bo-container">
        <div class="bo-card">
            <form method="POST" action="{{ route('business-owner.events.update', $event) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- SECTION 1: EVENT DETAILS -->
                <h3 class="bo-form-section-title">Event Details</h3>
                
                <div class="form-group">
                    <label for="title">Event Title <span style="color:red">*</span></label>
                    <input id="title" type="text" name="title" value="{{ old('title', $event->title) }}" required>
                </div>

                <div class="form-group">
                    <label for="business_id">Host Business <span style="color:red">*</span></label>
                    <select id="business_id" name="business_id" required>
                        <option value="">-- Select Business --</option>
                        @foreach($businesses as $id => $name)
                            <option value="{{ $id }}" {{ old('business_id', $event->business_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="event_categories">Event Type</label>
                    <select id="event_categories" name="event_categories[]" multiple placeholder="Select types..." autocomplete="off">
                        @foreach($eventCategories as $eventType)
                            <option value="{{ $eventType->id }}" 
                                {{ in_array($eventType->id, old('event_categories', $event->categories->pluck('id')->toArray())) ? 'selected' : '' }}>
                                {{ $eventType->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="description">Description <span style="color:red">*</span></label>
                    <textarea id="description" name="description" rows="5" required>{{ old('description', $event->description) }}</textarea>
                </div>

                <div class="bo-form-grid">
                    <div class="form-group">
                        <label for="start_datetime">Start Date & Time</label>
                        <input id="start_datetime" type="datetime-local" name="start_datetime" 
                               value="{{ old('start_datetime', $event->start_datetime ? \Carbon\Carbon::parse($event->start_datetime)->format('Y-m-d\TH:i') : '') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="end_datetime">End Date & Time</label>
                        <input id="end_datetime" type="datetime-local" name="end_datetime" 
                               value="{{ old('end_datetime', $event->end_datetime ? \Carbon\Carbon::parse($event->end_datetime)->format('Y-m-d\TH:i') : '') }}" required>
                    </div>
                </div>

                <!-- SECTION 2: LOCATION -->
                <h3 class="bo-form-section-title" style="margin-top: 2rem;">Location</h3>
                <div class="bo-form-grid">
                    <div class="form-group">
                        <label for="county_id">County</label>
                        <select id="county_id" name="county_id" required>
                            @foreach($counties as $county)
                                <option value="{{ $county->id }}" {{ old('county_id', $event->county_id) == $county->id ? 'selected' : '' }}>{{ $county->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="address">Venue / Address</label>
                        <input id="address" type="text" name="address" value="{{ old('address', $event->address) }}">
                    </div>
                </div>

                <!-- SECTION 3: PRICING -->
                <h3 class="bo-form-section-title" style="margin-top: 2rem;">Pricing</h3>
                <div class="form-group">
                    <label style="display:flex; align-items:center; cursor:pointer;">
                        <input id="is_free" type="checkbox" name="is_free" value="1" {{ old('is_free', $event->is_free) ? 'checked' : '' }} style="width:auto; margin-right:10px;">
                        This is a Free Event
                    </label>
                </div>

                <div id="price_field_group" class="bo-form-grid" style="{{ old('is_free', $event->is_free) ? 'display:none;' : 'display:grid;' }}">
                    <div class="form-group">
                        <label for="price">Ticket Price (Ksh)</label>
                        <input id="price" type="number" name="price" value="{{ old('price', $event->price) }}">
                    </div>
                    <div class="form-group">
                        <label for="ticketing_url">Ticket URL (Optional)</label>
                        <input id="ticketing_url" type="url" name="ticketing_url" value="{{ old('ticketing_url', $event->ticketing_url) }}">
                    </div>
                </div>

                <!-- SECTION 4: IMAGES -->
                <h3 class="bo-form-section-title" style="margin-top: 2rem;">Images</h3>
                
                @if($event->images->isNotEmpty())
                    <div class="photo-gallery-grid">
                        @foreach($event->images as $image)
                            <div class="photo-card">
                                <img src="{{ $image->url }}">
                                <div class="photo-actions">
                                    <label><input type="radio" name="main_event_image_id" value="{{ $image->id }}" {{ $image->is_main_event_image ? 'checked' : '' }} style="width:auto; margin-right:5px;"> Main</label>
                                    <label style="color:red;"><input type="checkbox" name="delete_images[]" value="{{ $image->id }}" style="width:auto; margin-right:5px;"> Delete</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="form-group">
                    <label>Upload New Images</label>
                    <input id="event_images_upload" type="file" name="images[]" multiple accept="image/*">
                </div>

                <div style="margin-top: 2rem; text-align: right;">
                    <button type="submit" class="bo-button-primary">Update Event</button>
                </div>
            </form>
        </div>
    </div>

    @push('footer-scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if(document.getElementById('event_categories')) {
                new TomSelect('#event_categories', { plugins: ['remove_button'], create: false });
            }
            const isFree = document.getElementById('is_free');
            const priceGroup = document.getElementById('price_field_group');
            if(isFree && priceGroup) {
                isFree.addEventListener('change', function() {
                    priceGroup.style.display = this.checked ? 'none' : 'grid';
                    if(this.checked) document.getElementById('price').value = '';
                });
            }
        });
    </script>
    @endpush
</x-business-owner-layout>