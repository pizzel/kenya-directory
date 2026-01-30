<x-business-owner-layout>
    {{-- DIRECT CSS LOAD --}}
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <style>
        /* Modern Input Styling Override */
        .bo-card input[type="text"],
        .bo-card input[type="email"],
        .bo-card input[type="tel"],
        .bo-card input[type="url"],
        .bo-card textarea,
        .bo-card select {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 0.95rem;
            color: #1e293b;
            transition: border 0.2s, box-shadow 0.2s;
            background-color: #fff;
        }
        .bo-card input:focus, .bo-card textarea:focus, .bo-card select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        /* TomSelect Theme */
        .ts-wrapper.multi .ts-control > div { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; border-radius: 4px; }
        select[multiple] { display: none; }
        
        .premium-time-input {
            width: 110px !important;
            display: inline-block;
            text-align: center;
        }
    </style>

    {{-- 1. PREMIUM HEADER --}}
    <div class="bo-header" style="background: white; border-bottom: 1px solid #e2e8f0; padding: 40px 0;">
        <div class="bo-container">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1 style="font-size: 2rem; font-weight: 800; color: #1e293b; margin-bottom: 5px;">Add New Business</h1>
                    <p style="color: #64748b; font-size: 1rem;">Create a listing to reach thousands of travelers.</p>
                </div>
                <a href="{{ route('business-owner.dashboard') }}" class="bo-button-secondary">Cancel</a>
            </div>
        </div>
    </div>

    <div class="bo-container" style="margin-top: 30px;">
        <form method="POST" action="{{ route('business-owner.businesses.store') }}" enctype="multipart/form-data" id="createBusinessForm">
            @csrf

            <!-- SECTION 1: DETAILS -->
            <div class="bo-card">
                <div class="bo-card-header" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 20px;">
                    <h3 style="font-size: 1.2rem; font-weight: 700; color: #1e293b; margin: 0;">Basic Information</h3>
                </div>
                
                <div class="form-group">
                    <label for="name">Business Name</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" placeholder="e.g. The Safari View Hotel" required>
                </div>

                <!-- CATEGORIES (Searchable) -->
                <div class="form-group">
                    <label for="categories">Activities (Type to select)</label>
                    <select id="categories" name="categories[]" multiple autocomplete="off" placeholder="Select activities...">
                        @foreach($categories as $activity)
                            <option value="{{ $activity->id }}" {{ (collect(old('categories'))->contains($activity->id)) ? 'selected' : '' }}>
                                {{ $activity->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="about_us">Short Intro</label>
                    <textarea id="about_us" name="about_us" rows="2" placeholder="Briefly describe what makes your place special..." required>{{ old('about_us') }}</textarea>
                </div>

                <div class="form-group">
                    <label for="description">Full Description</label>
                    <textarea id="description" name="description" rows="5" placeholder="Detailed description of services, history, etc.">{{ old('description') }}</textarea>
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
                        <select id="facilities" name="facilities[]" multiple autocomplete="off" placeholder="Select amenities...">
                            @foreach($facilities as $facility)
                                <option value="{{ $facility->id }}" {{ (collect(old('facilities'))->contains($facility->id)) ? 'selected' : '' }}>
                                    {{ $facility->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="tags">Tags (Vibe)</label>
                        <select id="tags" name="tags[]" multiple autocomplete="off" placeholder="Select vibe tags...">
                            @foreach($tags as $tag)
                                <option value="{{ $tag->id }}" {{ (collect(old('tags'))->contains($tag->id)) ? 'selected' : '' }}>
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
                    <h3 style="font-size: 1.2rem; font-weight: 700; color: #1e293b; margin: 0;">Location & Contact</h3>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                     <div class="form-group" style="margin-bottom:0;">
                        <label for="county_id">County</label>
                        <select id="county_id" name="county_id" required>
                            <option value="">Select County</option>
                            @foreach($counties as $county)
                                <option value="{{ $county->id }}" {{ old('county_id') == $county->id ? 'selected' : '' }}>{{ $county->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label for="address">Physical Address</label>
                        <input id="address" type="text" name="address" value="{{ old('address') }}" required placeholder="Street name, building, etc.">
                    </div>
                </div>
                
                <div style="background: #f8fafc; padding: 1.5rem; border: 1px solid #e2e8f0; border-radius: 0.5rem; margin-bottom: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items:center; margin-bottom: 1rem;">
                        <label style="margin:0; font-weight:600; color:#475569;">GPS Coordinates (Optional)</label>
                        <button type="button" id="getGeolocationBtn" class="bo-button-secondary" style="font-size: 0.8rem; padding: 0.5rem 1rem;">
                            <i class="fas fa-map-marker-alt" style="margin-right:5px;"></i> Use Current Location
                        </button>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group" style="margin-bottom:0;">
                            <input id="latitude" type="text" name="latitude" value="{{ old('latitude') }}" readonly placeholder="Latitude">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <input id="longitude" type="text" name="longitude" value="{{ old('longitude') }}" readonly placeholder="Longitude">
                        </div>
                    </div>
                    <div id="geolocationMessage" style="font-size: 0.8rem; margin-top: 0.5rem; font-weight:500;"></div>
                </div>

                <div class="bo-form-grid" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group">
                        <label for="phone_number">Phone</label>
                        <input id="phone_number" type="tel" name="phone_number" value="{{ old('phone_number') }}" placeholder="+254...">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="contact@example.com">
                    </div>
                    <div class="form-group">
                        <label for="website">Website</label>
                        <input id="website" type="url" name="website" value="{{ old('website') }}" placeholder="https://...">
                    </div>
                </div>
            </div>

            <!-- SECTION 4: PHOTOS -->
            <div class="bo-card">
                <div class="bo-card-header" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 20px;">
                    <h3 style="font-size: 1.2rem; font-weight: 700; color: #1e293b; margin: 0;">Photo Gallery</h3>
                </div>
                
                <div style="background: #f8fafc; padding: 2rem; border-radius: 0.5rem; border: 2px dashed #cbd5e1; text-align: center;">
                    <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: #94a3b8; margin-bottom: 10px;"></i>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label style="cursor: pointer; display: inline-block; background: white; border: 1px solid #cbd5e1; padding: 8px 20px; border-radius: 6px; font-weight: 600; color: #475569;">
                            Select Files
                            <input id="images" type="file" name="images[]" multiple accept="image/*" style="display: none;">
                        </label>
                        <p style="font-size: 0.8rem; color: #64748b; margin-top: 10px;">Supported formats: JPEG, PNG. Max 5MB per image.</p>
                    </div>
                    
                    <div class="form-group" style="max-width: 300px; margin: 0 auto; text-align: left;">
                        <label for="new_main_image_index" style="font-size: 0.8rem;">Cover Photo Preference</label>
                        <select name="new_main_image_index" id="new_main_image_index">
                            <option value="">-- Auto Select First --</option>
                        </select>
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
                            $oldOpen = old("schedule.$day.open_time", '08:00');
                            $oldClose = old("schedule.$day.close_time", '17:00');
                            $oldClosed = old("schedule.$day.is_closed_all_day", 0);
                        @endphp
                        
                        <div class="schedule-row" id="row-{{ $lowerDay }}">
                            <div class="day-info">
                                <label class="switch">
                                    <input type="checkbox" id="toggle-{{ $lowerDay }}" onchange="toggleDay('{{ $lowerDay }}')" {{ $oldClosed ? '' : 'checked' }}>
                                    <span class="slider round"></span>
                                </label>
                                <span style="margin-left: 10px; font-weight: 600; font-size: 0.9rem;">{{ $day }}</span>
                                <input type="hidden" name="schedule[{{ $day }}][is_closed_all_day]" id="input-closed-{{ $lowerDay }}" value="{{ $oldClosed }}">
                            </div>

                            <div class="time-selection" id="times-{{ $lowerDay }}">
                                <input type="time" name="schedule[{{ $day }}][open_time]" id="open-{{ $lowerDay }}" class="premium-time-input" value="{{ $oldOpen }}">
                                <span style="margin: 0 10px; color: #94a3b8; font-size: 0.8rem;">to</span>
                                <input type="time" name="schedule[{{ $day }}][close_time]" id="close-{{ $lowerDay }}" class="premium-time-input" value="{{ $oldClose }}">
                            </div>

                            <div class="closed-label" id="closed-text-{{ $lowerDay }}">Closed</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 15px;">
                 <a href="{{ route('business-owner.dashboard') }}" class="bo-button-secondary">Cancel</a>
                 <button type="submit" class="bo-button-primary">Create Listing</button>
            </div>
        </form>
    </div>

    {{-- SCRIPTS --}}
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        window.toggleDay = function(day) {
            const toggle = document.getElementById(`toggle-${day}`);
            const row = document.getElementById(`row-${day}`);
            const closedInput = document.getElementById(`input-closed-${day}`);
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
            const tsConfig = { plugins: ['remove_button', 'caret_position'], create: false, maxItems: null };
            if(document.getElementById('categories')) new TomSelect('#categories', tsConfig);
            if(document.getElementById('tags')) new TomSelect('#tags', tsConfig);
            if(document.getElementById('facilities')) new TomSelect('#facilities', tsConfig);

            const getLocBtn = document.getElementById('getGeolocationBtn');
            const geoMsg = document.getElementById('geolocationMessage');
            if (getLocBtn) {
                getLocBtn.addEventListener('click', function() {
                    geoMsg.textContent = "Locating...";
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            function(position) {
                                document.getElementById('latitude').value = position.coords.latitude.toFixed(6);
                                document.getElementById('longitude').value = position.coords.longitude.toFixed(6);
                                geoMsg.textContent = "Location found!"; geoMsg.style.color = "#10b981";
                            },
                            function(error) { geoMsg.textContent = "Could not get location."; geoMsg.style.color = "#ef4444"; }
                        );
                    }
                });
            }
            const imagesInput = document.getElementById('images');
            const newMainImageSelect = document.getElementById('new_main_image_index');
            if (imagesInput && newMainImageSelect) {
                imagesInput.addEventListener('change', function (event) {
                    newMainImageSelect.innerHTML = '<option value="">-- First Image --</option>';
                    if (event.target.files) {
                        for (let i = 0; i < event.target.files.length; i++) {
                            const option = document.createElement('option'); option.value = i; option.textContent = `Image ${i + 1}: ${event.target.files[i].name}`;
                            newMainImageSelect.appendChild(option);
                        }
                    }
                });
            }
            ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'].forEach(day => { window.toggleDay(day); });
        });
    </script>
</x-business-owner-layout>