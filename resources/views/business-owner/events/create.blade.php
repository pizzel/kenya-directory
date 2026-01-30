<x-business-owner-layout>
    {{-- DIRECT CSS LOAD --}}
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <style>
        .ts-wrapper.multi .ts-control > div { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; border-radius: 4px; }
        select[multiple] { display: none; }
    </style>

    <x-slot name="header">
        <div class="bo-page-header">
            <h2>Add New Event</h2>
            <a href="{{ route('business-owner.events.index') }}" class="bo-button-secondary">Cancel</a>
        </div>
    </x-slot>

    <div class="bo-container">
        <div class="bo-card">
            <form method="POST" action="{{ route('business-owner.events.store') }}" enctype="multipart/form-data">
                @csrf

                <!-- SECTION 1: EVENT DETAILS -->
                <h3 class="bo-form-section-title">Event Details</h3>
                
                <div class="form-group">
                    <label for="title">Event Title <span style="color:red">*</span></label>
                    <input id="title" type="text" name="title" value="{{ old('title') }}" required placeholder="e.g. Summer Music Festival">
                </div>

                <div class="form-group">
                    <label for="business_id">Host Business <span style="color:red">*</span></label>
                    <select id="business_id" name="business_id" required>
                        <option value="">-- Select Business --</option>
                        @foreach($businesses as $id => $name)
                            <option value="{{ $id }}" {{ old('business_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="event_categories">Event Type (Select multiple)</label>
                    <select id="event_categories" name="event_categories[]" multiple placeholder="Select types..." autocomplete="off">
                        @foreach($eventCategories as $eventType)
                            <option value="{{ $eventType->id }}" {{ (collect(old('event_categories'))->contains($eventType->id)) ? 'selected' : '' }}>
                                {{ $eventType->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="description">Description <span style="color:red">*</span></label>
                    <textarea id="description" name="description" rows="5" required placeholder="What is this event about?">{{ old('description') }}</textarea>
                </div>

                <div class="bo-form-grid">
                    <div class="form-group">
                        <label for="start_datetime">Start Date & Time <span style="color:red">*</span></label>
                        <input id="start_datetime" type="datetime-local" name="start_datetime" value="{{ old('start_datetime') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="end_datetime">End Date & Time <span style="color:red">*</span></label>
                        <input id="end_datetime" type="datetime-local" name="end_datetime" value="{{ old('end_datetime') }}" required>
                    </div>
                </div>

                <!-- SECTION 2: LOCATION -->
                <h3 class="bo-form-section-title" style="margin-top: 2rem;">Location</h3>
                
                <div class="bo-form-grid">
                    <div class="form-group">
                        <label for="county_id">County <span style="color:red">*</span></label>
                        <select id="county_id" name="county_id" required>
                            <option value="">Select County</option>
                            @foreach($counties as $county)
                                <option value="{{ $county->id }}" {{ old('county_id') == $county->id ? 'selected' : '' }}>{{ $county->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="address">Venue / Address</label>
                        <input id="address" type="text" name="address" value="{{ old('address') }}" placeholder="e.g. Sarit Centre Expo Hall">
                    </div>
                </div>

                <!-- SECTION 3: PRICING -->
                <h3 class="bo-form-section-title" style="margin-top: 2rem;">Pricing</h3>
                
                <div class="form-group">
                    <label style="display:flex; align-items:center; cursor:pointer;">
                        <input id="is_free" type="checkbox" name="is_free" value="1" {{ old('is_free') ? 'checked' : '' }} style="width:auto; margin-right:10px;">
                        This is a Free Event
                    </label>
                </div>

                <div id="price_field_group" class="bo-form-grid" style="{{ old('is_free') ? 'display:none;' : 'display:grid;' }}">
                    <div class="form-group">
                        <label for="price">Ticket Price (Ksh)</label>
                        <input id="price" type="number" name="price" value="{{ old('price') }}" placeholder="e.g. 500">
                    </div>
                    <div class="form-group">
                        <label for="ticketing_url">Ticket URL (Optional)</label>
                        <input id="ticketing_url" type="url" name="ticketing_url" value="{{ old('ticketing_url') }}" placeholder="https://...">
                    </div>
                </div>

                <!-- SECTION 4: IMAGES -->
                <h3 class="bo-form-section-title" style="margin-top: 2rem;">Images</h3>
                <div class="form-group">
                    <label>Upload Images (Max 3)</label>
                    <input id="event_images_upload" type="file" name="images[]" multiple accept="image/*">
                </div>

                <div style="margin-top: 2rem; text-align: right;">
                    <button type="submit" class="bo-button-primary">Create Event</button>
                </div>
            </form>
        </div>
    </div>

    @push('footer-scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // TomSelect
            if(document.getElementById('event_categories')) {
                new TomSelect('#event_categories', { plugins: ['remove_button'], create: false });
            }

            // Price Toggle
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