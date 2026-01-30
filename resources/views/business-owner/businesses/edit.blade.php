<x-business-owner-layout>
    {{-- DIRECT CSS LOAD --}}
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <style>
        .bo-card input[type="text"], .bo-card input[type="email"], .bo-card input[type="tel"], .bo-card input[type="url"], .bo-card textarea, .bo-card select {
            width: 100%; padding: 10px 15px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; color: #1e293b; transition: border 0.2s; background-color: #fff;
        }
        .bo-card input:focus, .bo-card textarea:focus, .bo-card select:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
        .ts-wrapper.multi .ts-control > div { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; border-radius: 4px; }
        select[multiple] { display: none; }
        .premium-time-input { width: 110px !important; display: inline-block; text-align: center; }
    </style>

    {{-- PREMIUM HEADER --}}
    <div class="bo-header" style="background: white; border-bottom: 1px solid #e2e8f0; padding: 40px 0;">
        <div class="bo-container">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1 style="font-size: 2rem; font-weight: 800; color: #1e293b; margin-bottom: 5px;">Edit Listing</h1>
                    <p style="color: #64748b; font-size: 1rem;">Update details for <strong style="color:#1e293b;">{{ $business->name }}</strong></p>
                </div>
                <div style="display: flex; gap: 10px;">
                    <a href="{{ route('listings.show', $business->slug) }}" target="_blank" class="bo-button-secondary" style="display: flex; align-items: center; gap: 5px;">
                        <i class="fas fa-external-link-alt"></i> View Live
                    </a>
                    <a href="{{ route('business-owner.dashboard') }}" class="bo-button-secondary">Cancel</a>
                </div>
            </div>
        </div>
    </div>

    <div class="bo-container" style="margin-top: 30px;">
        <form method="POST" action="{{ route('business-owner.businesses.update', $business) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- SECTION 1: DETAILS -->
            <div class="bo-card">
                <div class="bo-card-header" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 20px;">
                    <h3 style="font-size: 1.2rem; font-weight: 700; color: #1e293b; margin: 0;">Basic Information</h3>
                </div>
                
                <div class="form-group">
                    <label for="name">Business Name</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $business->name) }}" required>
                </div>

                <div class="form-group">
                    <label for="categories">Activities</label>
                    <select id="categories" name="categories[]" multiple autocomplete="off">
                        @foreach($categories as $activity)
                            <option value="{{ $activity->id }}" {{ in_array($activity->id, old('categories', $business->categories->pluck('id')->toArray())) ? 'selected' : '' }}>
                                {{ $activity->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="about_us">Short Intro</label>
                    <textarea id="about_us" name="about_us" rows="2" required>{{ old('about_us', $business->about_us) }}</textarea>
                </div>

                <div class="form-group">
                    <label for="description">Full Description</label>
                    <textarea id="description" name="description" rows="5">{{ old('description', $business->description) }}</textarea>
                </div>
            </div>

            <!-- SECTION 2: ATTRIBUTES -->
            <div class="bo-card">
                <div class="bo-card-header" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 20px;">
                    <h3 style="font-size: 1.2rem; font-weight: 700; color: #1e293b; margin: 0;">Features & Keywords</h3>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label for="facilities">Facilities</label>
                        <select id="facilities" name="facilities[]" multiple autocomplete="off">
                            @foreach($facilities as $facility)
                                <option value="{{ $facility->id }}" {{ in_array($facility->id, old('facilities', $business->facilities->pluck('id')->toArray())) ? 'selected' : '' }}>
                                    {{ $facility->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="tags">Tags</label>
                        <select id="tags" name="tags[]" multiple autocomplete="off">
                            @foreach($tags as $tag)
                                <option value="{{ $tag->id }}" {{ in_array($tag->id, old('tags', $business->tags->pluck('id')->toArray())) ? 'selected' : '' }}>
                                    {{ $tag->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- SECTION 3: LOCATION -->
            <div class="bo-card">
                <div class="bo-card-header" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 20px;">
                    <h3 style="font-size: 1.2rem; font-weight: 700; color: #1e293b; margin: 0;">Location</h3>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                     <div class="form-group">
                        <label for="county_id">County</label>
                        <select id="county_id" name="county_id" required>
                            @foreach($counties as $county)
                                <option value="{{ $county->id }}" {{ old('county_id', $business->county_id) == $county->id ? 'selected' : '' }}>{{ $county->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <input id="address" type="text" name="address" value="{{ old('address', $business->address) }}" required>
                    </div>
                </div>
                
                <div class="bo-form-grid" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem; margin-top: 15px;">
                    <div class="form-group"><label>Phone</label><input id="phone_number" type="tel" name="phone_number" value="{{ old('phone_number', $business->phone_number) }}"></div>
                    <div class="form-group"><label>Email</label><input id="email" type="email" name="email" value="{{ old('email', $business->email) }}"></div>
                    <div class="form-group"><label>Website</label><input id="website" type="url" name="website" value="{{ old('website', $business->website) }}"></div>
                </div>
            </div>

            <!-- SECTION 4: PHOTOS (Clean Gallery) -->
            <div class="bo-card">
                <div class="bo-card-header" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 20px;">
                    <h3 style="font-size: 1.2rem; font-weight: 700; color: #1e293b; margin: 0;">Photo Gallery</h3>
                </div>
                
                @if($business->getMedia('images')->count() > 0)
                    <div class="photo-gallery-grid">
                        @foreach($business->getMedia('images')->sortBy('order_column') as $media)
                            <div class="photo-card">
                                <img src="{{ $media->getUrl('thumbnail') }}" alt="Business Image">
                                <div class="photo-actions">
                                    <label>
                                        <input type="radio" name="main_image_id" value="{{ $media->id }}" {{ $loop->first ? 'checked' : '' }}> Set as Cover
                                    </label>
                                    <label class="delete-label">
                                        <input type="checkbox" name="delete_images[]" value="{{ $media->id }}"> Delete
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="text-align:center; padding: 2rem; background:#f9fafb; border-radius:8px; border:1px dashed #e2e8f0; margin-bottom:1.5rem;">
                        <p style="color:#64748b;">No photos uploaded yet.</p>
                    </div>
                @endif

                <div style="background: #f8fafc; padding: 1.5rem; border-radius: 0.5rem; border: 1px solid #e2e8f0; margin-top: 20px;">
                    <div class="form-group">
                        <label>Upload New Images</label>
                        <input id="images" type="file" name="images[]" multiple accept="image/*">
                    </div>
                </div>
            </div>
            
            <!-- SECTION 5: SCHEDULE -->
            <div class="bo-card">
                <div class="schedule-header">
                    <h3 style="font-size: 1.2rem; font-weight: 700; color: #1e293b; margin: 0;">Operating Hours</h3>
                    <div class="copy-hours-btn" onclick="copyMondayToAll()">
                        <i class="fas fa-copy"></i> Copy Monday to All
                    </div>
                </div>
                <div class="schedule-container" style="border-top:none; border-top-left-radius:0; border-top-right-radius:0;">
                    @foreach($daysOfWeek as $day)
                        @php
                            $lowerDay = strtolower($day);
                            $open = $schedulesData[$day]['open_time'] ?: '08:00';
                            $close = $schedulesData[$day]['close_time'] ?: '17:00';
                            $isClosed = $schedulesData[$day]['is_closed_all_day'];
                        @endphp
                        <div class="schedule-row" id="row-{{ $lowerDay }}">
                            <div class="day-info">
                                <label class="switch"><input type="checkbox" id="toggle-{{ $lowerDay }}" onchange="toggleDay('{{ $lowerDay }}')" {{ $isClosed ? '' : 'checked' }}><span class="slider round"></span></label>
                                <span style="margin-left: 10px; font-weight: 600;">{{ $day }}</span>
                                <input type="hidden" name="schedule[{{ $day }}][is_closed_all_day]" id="input-closed-{{ $lowerDay }}" value="{{ $isClosed }}">
                            </div>
                            <div class="time-selection" id="times-{{ $lowerDay }}">
                                <input type="time" name="schedule[{{ $day }}][open_time]" id="open-{{ $lowerDay }}" class="premium-time-input" value="{{ $open }}">
                                <span style="margin: 0 10px; color: #94a3b8; font-size: 0.8rem;">to</span>
                                <input type="time" name="schedule[{{ $day }}][close_time]" id="close-{{ $lowerDay }}" class="premium-time-input" value="{{ $close }}">
                            </div>
                            <div class="closed-label" id="closed-text-{{ $lowerDay }}">Closed</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 15px;">
                 <a href="{{ route('business-owner.dashboard') }}" class="bo-button-secondary">Cancel</a>
                 <button type="submit" class="bo-button-primary">Save Changes</button>
            </div>
        </form>
    </div>

    {{-- SCRIPTS (Keep exactly same as Create) --}}
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        window.toggleDay = function(day) {
            const toggle = document.getElementById(`toggle-${day}`); const row = document.getElementById(`row-${day}`); const closedInput = document.getElementById(`input-closed-${day}`);
            if (toggle.checked) { row.classList.remove('closed'); closedInput.value = "0"; } else { row.classList.add('closed'); closedInput.value = "1"; }
        };
        window.copyMondayToAll = function() {
            const days = ['tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
            const monToggle = document.getElementById('toggle-monday').checked;
            const monOpen = document.getElementById('open-monday').value;
            const monClose = document.getElementById('close-monday').value;
            days.forEach(day => {
                const toggle = document.getElementById(`toggle-${day}`); toggle.checked = monToggle;
                document.getElementById(`open-${day}`).value = monOpen; document.getElementById(`close-${day}`).value = monClose;
                window.toggleDay(day);
            });
            alert("Monday's hours copied to all days!");
        };
        document.addEventListener('DOMContentLoaded', function() {
            const tsConfig = { plugins: ['remove_button', 'caret_position'], create: false, maxItems: null, placeholder: 'Click to select...' };
            if(document.getElementById('categories')) new TomSelect('#categories', tsConfig);
            if(document.getElementById('tags')) new TomSelect('#tags', tsConfig);
            if(document.getElementById('facilities')) new TomSelect('#facilities', tsConfig);
            
            // Re-run toggle logic on load to set correct initial state
            ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'].forEach(day => { window.toggleDay(day); });
        });
    </script>
</x-business-owner-layout>