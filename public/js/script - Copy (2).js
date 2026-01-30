// ==========================================================================
// GLOBAL HELPER FUNCTIONS
// ==========================================================================

/**
 * Shows a styled loading/message modal.
 * @param {string} message - The message to display.
 * @param {boolean} [hideSpinner=false] - Set to true to hide the spinner.
 * @param {number} [autoCloseDelay=0] - Auto-close delay in ms. 0 means no auto-close.
 */
function showLoadingModal(message, hideSpinner = false, autoCloseDelay = 0) {
    const modal = document.getElementById('loadingMessageModal');
    const messageText = document.getElementById('loadingMessageText');
    const spinner = modal ? modal.querySelector('.spinner') : null;

    if (modal && messageText) {
        messageText.innerHTML = message || 'Processing...';
        if (spinner) {
            spinner.style.display = hideSpinner ? 'none' : 'inline-block';
        }
        modal.style.display = 'flex';
        void modal.offsetWidth; // Force reflow for CSS transition
        modal.classList.add('is-visible');

        if (autoCloseDelay > 0) {
            setTimeout(() => hideLoadingModal(), autoCloseDelay);
        }
    } else {
        // Fallback if modal elements are not found
        console.warn("Loading modal elements not found. Using alert as fallback.");
        alert(message || 'Processing... Please wait.'); // THIS ALERT IS THE FALLBACK
    }
}

/**
 * Hides the styled loading/message modal.
 */
function hideLoadingModal() {
    const modal = document.getElementById('loadingMessageModal');
    if (modal) {
        modal.classList.remove('is-visible');
        setTimeout(() => {
            if (!modal.classList.contains('is-visible')) {
                modal.style.display = 'none';
            }
        }, 300); // Match CSS transition-duration
    }
}

/**
 * Handles the "Take Me Here" button click to open Google Maps for directions.
 */
function navigateToLocation(latitude, longitude, placeName) {
    const lat = parseFloat(latitude);
    const lng = parseFloat(longitude);
	
	
	    console.log("navigateToLocation called. Modal elements check:"); // DEBUG
    console.log("Modal div:", document.getElementById('loadingMessageModal')); // DEBUG
    console.log("Modal text div:", document.getElementById('loadingMessageText')); // DEBUG

    if (isNaN(lat) || isNaN(lng)) {
        // Use modal for error message
        showLoadingModal("Location coordinates for this business are invalid or not available.", true, 3000);
        console.error("Invalid coordinates passed to navigateToLocation:", latitude, longitude);
        return;
    }

    const destinationQuery = `${lat},${lng}`;
    const destinationUrl = `https://www.google.com/maps/search/?api=1&query=${destinationQuery}`;
    const directionsUrlBase = `https://www.google.com/maps/dir/?api=1&destination=${destinationQuery}&travelmode=driving`;

    if (navigator.geolocation) {
        // Use modal for "getting location" message
        showLoadingModal("Getting your location to provide directions to " + placeName + "...");
        navigator.geolocation.getCurrentPosition(
            function(position) { // Success
                const userLat = position.coords.latitude;
                const userLng = position.coords.longitude;
                hideLoadingModal(); // Hide "getting location"
                showLoadingModal("Opening Google Maps with directions...", false, 1500); // Brief "opening" message, with spinner
                setTimeout(() => { window.open(`${directionsUrlBase}&origin=${userLat},${userLng}`, '_blank'); }, 500); // Short delay before opening
            },
            function(error) { // Error
                hideLoadingModal(); // Hide "getting location"
                let errorMsg = `Could not get your current location (Error: ${error.message}). Opening Google Maps with just the destination: ${placeName}.`;
                showLoadingModal(errorMsg, true, 4000); // Show error in modal, no spinner, auto-close
                setTimeout(() => { window.open(destinationUrl, '_blank'); }, 500);
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    } else {
        // Use modal if geolocation not supported
        showLoadingModal("Geolocation not supported by this browser. Opening Google Maps with the destination: " + placeName + "...", true, 3000);
        setTimeout(() => { window.open(destinationUrl, '_blank'); }, 500);
    }
}

/**
 * Toggles the disabled state of schedule time/notes inputs based on a checkbox.
 * Called by onchange="toggleTimeInputs(this, 'DayName')"
 */
function toggleTimeInputs(checkbox, day) {
    const openInput = document.querySelector(`input[name="schedule[${day}][open_time]"]`);
    const closeInput = document.querySelector(`input[name="schedule[${day}][close_time]"]`);
    const notesInput = document.querySelector(`input[name="schedule[${day}][notes]"]`);

    [openInput, closeInput, notesInput].forEach(input => {
        if (input) {
            input.disabled = checkbox.checked;
            if (checkbox.checked) {
                input.value = '';
            }
        }
    });
}

// ==========================================================================
// DOMContentLoaded - Code that runs after the page is fully loaded
// Initializations and event listeners that don't need to be global.
// ==========================================================================
document.addEventListener('DOMContentLoaded', function() {
	
	// In public/js/script.js, inside DOMContentLoaded listener

const visualActivitiesScroller = document.getElementById('visualActivitiesScroller');
const activitiesScrollPrev = document.getElementById('activitiesScrollPrev');
const activitiesScrollNext = document.getElementById('activitiesScrollNext');
const activitiesScrollerWrapper = document.querySelector('.activities-scroller-wrapper');

if (visualActivitiesScroller && activitiesScrollPrev && activitiesScrollNext && activitiesScrollerWrapper) {
    const itemWidth = visualActivitiesScroller.querySelector('.category-item')?.offsetWidth || 170; // Approx width + gap
    const scrollAmount = itemWidth * 3; // Scroll 3 items at a time

    function checkScrollability() {
        if (visualActivitiesScroller.scrollWidth <= visualActivitiesScroller.clientWidth) {
            activitiesScrollerWrapper.classList.add('no-scroll');
        } else {
            activitiesScrollerWrapper.classList.remove('no-scroll');
        }
        // Disable/enable buttons based on scroll position
        activitiesScrollPrev.disabled = visualActivitiesScroller.scrollLeft <= 0;
        activitiesScrollNext.disabled = visualActivitiesScroller.scrollLeft + visualActivitiesScroller.clientWidth >= visualActivitiesScroller.scrollWidth - 5; // -5 for tolerance
    }

    activitiesScrollPrev.addEventListener('click', () => {
        visualActivitiesScroller.scrollLeft -= scrollAmount;
    });
    activitiesScrollNext.addEventListener('click', () => {
        visualActivitiesScroller.scrollLeft += scrollAmount;
    });

    visualActivitiesScroller.addEventListener('scroll', checkScrollability);
    window.addEventListener('resize', checkScrollability); // Recheck on resize
    checkScrollability(); // Initial check
}

    // --- CURRENT YEAR (for footers) ---
    const yearSpans = ['current-year', 'current-year-layout', 'current-year-details', 'current-year-listings'];
    yearSpans.forEach(id => {
        const span = document.getElementById(id);
        if (span) {
            span.textContent = new Date().getFullYear();
        }
    });

    // --- SEARCHABLE DROPDOWNS (Homepage Sticky Search) ---
    function initializeSearchableDropdown(inputId, listContainerSelector) {
        const searchInput = document.getElementById(inputId);
        if (!searchInput) return;

        const dropdownGroup = searchInput.closest('.searchable-dropdown-group');
        if (!dropdownGroup) return;
        const dropdownListContainer = dropdownGroup.querySelector(listContainerSelector);
        if (!dropdownListContainer) return;

        const listItems = Array.from(dropdownListContainer.querySelectorAll('div[data-value]'));

        function filterAndShowList(showAll = false) {
            const filterValue = searchInput.value.toLowerCase();
            let hasVisibleItems = false;
            listItems.forEach(item => {
                const itemText = item.textContent.toLowerCase();
                const isMatch = itemText.includes(filterValue);
                item.style.display = (showAll || isMatch) ? '' : 'none';
                if (showAll || isMatch) hasVisibleItems = true;
            });

            const isFocused = (document.activeElement === searchInput);
            dropdownListContainer.style.display = (hasVisibleItems || isFocused) ? 'block' : 'none';
        }

        searchInput.addEventListener('focus', () => filterAndShowList(true)); // Show all on focus if input is empty
        searchInput.addEventListener('input', () => filterAndShowList(false));

        listItems.forEach(item => {
            item.addEventListener('click', function() {
                searchInput.value = this.textContent;
                dropdownListContainer.style.display = 'none';
            });
        });

        // Close dropdown if clicked outside THIS specific dropdown group
        document.addEventListener('click', function(event) {
            if (!dropdownGroup.contains(event.target)) {
                dropdownListContainer.style.display = 'none';
            }
        });

        searchInput.addEventListener('keydown', function(event) {
            if (event.key === "Escape") {
                dropdownListContainer.style.display = 'none';
                searchInput.blur();
            }
        });
    }

    if (document.getElementById('county-search-input')) {
        initializeSearchableDropdown('county-search-input', '.county-dropdown-list');
    }
    if (document.getElementById('category-search-input')) {
        initializeSearchableDropdown('category-search-input', '.category-dropdown-list');
    }

    // --- HOMEPAGE SPECIFIC: Top Categories Scroller Pause ---
    const scroller = document.getElementById('topCategoriesGrid');
    const scrollerWrapper = document.querySelector('.top-categories-scroller-wrapper');
    if (scroller && scrollerWrapper) {
        scrollerWrapper.addEventListener('mouseenter', () => { scroller.style.animationPlayState = 'paused'; });
        scrollerWrapper.addEventListener('mouseleave', () => { scroller.style.animationPlayState = 'running'; });
    }

    // --- BUSINESS OWNER FORMS: Geolocation Button ---
    const getLocBtnForm = document.getElementById('getGeolocationBtn');
    const latInputForm = document.getElementById('latitude');
    const lngInputForm = document.getElementById('longitude');
    const geoMsgForm = document.getElementById('geolocationMessage');
    const geoHelpNoticeForm = document.getElementById('geolocationHelpNotice');

    if (getLocBtnForm && latInputForm && lngInputForm && geoMsgForm && geoHelpNoticeForm) {
        // Set initial readonly state based on whether fields have values (for edit page)
        const initiallyHasCoords = latInputForm.value || lngInputForm.value;
        latInputForm.readOnly = !initiallyHasCoords;
        lngInputForm.readOnly = !initiallyHasCoords;
        if (!initiallyHasCoords && !latInputForm.value && !lngInputForm.value) { // Clearer logic for create
             latInputForm.readOnly = true;
             lngInputForm.readOnly = true;
        }

		getLocBtnForm.addEventListener('click', function() {
            if(geoHelpNoticeForm) geoHelpNoticeForm.style.display = 'none';
            showLoadingModal('Fetching your current location...');
            latInputForm.readOnly = true; lngInputForm.readOnly = true;
            latInputForm.value = ''; lngInputForm.value = '';

            if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) { // Success
                    latInputForm.value = position.coords.latitude.toFixed(8);
                    lngInputForm.value = position.coords.longitude.toFixed(8);

                    // Now call Laravel backend for reverse geocoding
                    fetch(window.reverseGeocodeUrl, {         // <<< CORRECTED: Use the global variable
								method: 'POST',
								headers: {
									'Content-Type': 'application/json',
									'X-CSRF-TOKEN': window.csrfToken // Use the global CSRF token
								},
								body: JSON.stringify({
									latitude: latInputForm.value,
									longitude: lngInputForm.value
								})
							})
											.then(response => response.json())
                    .then(data => {
								hideLoadingModal();
								if (data.formatted_address) {
									let displayMessage = '<strong>Location Identified:</strong><br>';
									// Prefer place_name if it's different and more concise than formatted_address,
									// otherwise, use formatted_address.
									if (data.place_name && data.place_name !== data.formatted_address) {
										displayMessage += `${data.place_name} <br><small>(${data.formatted_address})</small>`;
									} else {
										displayMessage += data.formatted_address;
									}
									displayMessage += `<br><small>Coords: Lat: ${data.coordinates.lat}, Lng: ${data.coordinates.lng}. Please verify.</small>`;
									geoMsgForm.innerHTML = displayMessage;
									geoMsgForm.style.color = 'green';
								} else {
									// Error from our Laravel backend (e.g., API key issue relayed)
									let errorDetail = data.error || 'Unknown error from server.';
									if (data.google_error_message) { // If Google provided a specific error
										errorDetail = data.google_error_message;
									}
									geoMsgForm.innerHTML = `<strong>Location Fetched (Coords only):</strong> Lat: ${latInputForm.value}, Lng: ${lngInputForm.value}.<br><small>Could not get address: ${errorDetail}. Please verify.</small>`;
									geoMsgForm.style.color = 'darkorange';
								}
								latInputForm.readOnly = false;
								lngInputForm.readOnly = false;
							})
                    .catch(error => {
                        hideLoadingModal();
                        console.error('Reverse geocoding error:', error);
                        geoMsgForm.innerHTML = `<strong>Location Fetched (Coords only):</strong> Lat: ${latInputForm.value}, Lng: ${lngInputForm.value}.<br><small>Address lookup failed. Please verify.</small>`;
                        geoMsgForm.style.color = 'darkorange';
                        latInputForm.readOnly = false;
                        lngInputForm.readOnly = false;
                    });
                },
                function(error) { // Geolocation API error
                    hideLoadingModal();
                    // ... (your existing geolocation error handling to geoMsgForm and geoHelpNoticeForm) ...
                    latInputForm.readOnly = false;
                    lngInputForm.readOnly = false;
                    latInputForm.focus();
                },
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
            );
        } else {
                hideLoadingModal();
                if(geoMsgForm){
                    geoMsgForm.innerHTML = "<strong>Notice:</strong> Geolocation not supported. Please enter coordinates manually.";
                    geoMsgForm.style.color = '#fd7e14'; // Orange
                    geoMsgForm.style.display = 'block';
                }
                if(geoHelpNoticeForm) geoHelpNoticeForm.style.display = 'block';
                latInputForm.readOnly = false; lngInputForm.readOnly = false;
            }
        });
    }

    // --- BUSINESS OWNER FORMS: Image Upload Main Image Selector ---
    const imagesInputBO = document.getElementById('images');
    const newMainImageSelectBO = document.getElementById('new_main_image_index');
    if (imagesInputBO && newMainImageSelectBO) {
        imagesInputBO.addEventListener('change', function (event) {
            newMainImageSelectBO.innerHTML = '<option value="">-- None (first new/current main) --</option>';
            if (event.target.files && event.target.files.length > 0) {
                for (let i = 0; i < event.target.files.length; i++) {
                    const option = document.createElement('option');
                    option.value = i;
                    let fileName = event.target.files[i].name;
                    option.textContent = `New Image ${i + 1}: ${fileName.length > 30 ? fileName.substring(0,27) + '...' : fileName}`;
                    newMainImageSelectBO.appendChild(option);
                }
            }
        });
    }

    // --- BUSINESS OWNER FORMS: Schedule Time Inputs Toggle Initialization ---
    document.querySelectorAll('input[type="checkbox"][id^="schedule_closed_"]').forEach(cb => {
        const day = cb.id.replace('schedule_closed_', '');
        // Ensure toggleTimeInputs is globally defined or defined before this call.
        if (typeof toggleTimeInputs === 'function') {
            toggleTimeInputs(cb, day); // Call to set initial state
        }
    });

    // --- LISTINGS PAGE (county/category/search): Price range & Sort ---
    const priceSliderListing = document.getElementById('price_slider_input_listing');
    const priceValueDisplayListing = document.getElementById('priceValueDisplayListing');
    if (priceSliderListing && priceValueDisplayListing) {
        priceSliderListing.oninput = function() { priceValueDisplayListing.textContent = "Ksh " + this.value; }
        // Set initial display value on page load
        if (priceSliderListing.value) {
            priceValueDisplayListing.textContent = "Ksh " + priceSliderListing.value;
        }
    }
    const sortSelectListing = document.getElementById('sort-by-select-listing');
    const sortInputHiddenListing = document.getElementById('sort_input_listing_filter');
    const filterFormListing = document.getElementById('filterSortForm');
    if (sortSelectListing && sortInputHiddenListing && filterFormListing) {
        sortSelectListing.addEventListener('change', function() {
            sortInputHiddenListing.value = this.value;
            filterFormListing.submit();
        });
    }

    // --- LISTING DETAIL PAGE: Wishlist Button (Placeholder for client-side UI updates if needed) ---
    const wishlistBtnDetail = document.querySelector('.listing-detail-page .wishlist-btn');
    if(wishlistBtnDetail) {
        // Currently, wishlist logic is handled by form submission and page reload.
        // If you implement AJAX for wishlist, you'd add event listeners here.
    }

}); // END DOMContentLoaded