// ==========================================================================
// 1. GLOBAL HELPER FUNCTIONS
// (Accessible from inline HTML event attributes like onclick)
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
        // Fallback for critical errors if modal is somehow missing
        console.warn("Loading modal elements not found. Using alert as fallback for message:", message);
        alert(message || 'Processing... Please wait.');
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
            if (!modal.classList.contains('is-visible')) { // Check again in case re-shown
                modal.style.display = 'none';
            }
        }, 300); // Match CSS transition-duration
    }
}

/**
 * Handles changing the main image in the listing detail gallery.
 * Called by: onclick="setMainGalleryImageFinal(this.src)"
 */
function setMainGalleryImageFinal(src) {
    const mainImg = document.getElementById('galleryMainImageFinal');
    if (mainImg && src) {
        mainImg.src = src;
    } else if (!mainImg) {
        console.error("Element with ID 'galleryMainImageFinal' not found for gallery display.");
    }
}

/**
 * Handles the "Take Me Here" button click to open Google Maps for directions.
 * Called by: onclick="navigateToLocation(...)"
 */
function navigateToLocation(latitude, longitude, placeName) {
    const lat = parseFloat(latitude);
    const lng = parseFloat(longitude);

    if (isNaN(lat) || isNaN(lng)) {
        showLoadingModal("Location coordinates for this business are invalid or not available.", true, 3500);
        console.error("Invalid coordinates passed to navigateToLocation:", latitude, longitude);
        return;
    }

    const destinationQuery = `${lat},${lng}`;
    const destinationUrl = `https://www.google.com/maps/search/?api=1&query=${destinationQuery}`;
    const directionsUrlBase = `https://www.google.com/maps/dir/?api=1&destination=${destinationQuery}&travelmode=driving`;

    if (navigator.geolocation) {
        showLoadingModal("Getting your location to provide directions to " + (placeName || 'the destination') + "...");
        navigator.geolocation.getCurrentPosition(
            function (position) { // Success
                hideLoadingModal();
                showLoadingModal("Opening Google Maps with directions...", false, 2000);
                setTimeout(() => window.open(`${directionsUrlBase}&origin=${position.coords.latitude},${position.coords.longitude}`, '_blank'), 500);
            },
            function (error) { // Error
                hideLoadingModal();
                let errorMsg = `Could not get your current location (Error: ${error.message}). Opening Google Maps with just the destination: ${placeName || 'the destination'}.`;
                showLoadingModal(errorMsg, true, 4500);
                setTimeout(() => window.open(destinationUrl, '_blank'), 500);
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    } else {
        showLoadingModal("Geolocation not supported by this browser. Opening Google Maps with the destination: " + (placeName || 'the destination') + "...", true, 3500);
        setTimeout(() => window.open(destinationUrl, '_blank'), 500);
    }
}

/**
 * Toggles the disabled state of schedule time/notes inputs based on a "Closed All Day" checkbox.
 * Called by: onchange="toggleTimeInputs(this, 'DayName')"
 */
function toggleTimeInputs(checkbox, day) {
    const openInput = document.querySelector(`input[name="schedule[${day}][open_time]"]`);
    const closeInput = document.querySelector(`input[name="schedule[${day}][close_time]"]`);
    const notesInput = document.querySelector(`input[name="schedule[${day}][notes]"]`);

    [openInput, closeInput, notesInput].forEach(input => {
        if (input) {
            input.disabled = checkbox.checked;
            if (checkbox.checked) {
                input.value = ''; // Clear value when disabled
            }
        }
    });
}


// ==========================================================================
// DOMContentLoaded - Code that runs after the page is fully loaded
// Initializations and event listeners for elements that don't use inline onclick.
// ==========================================================================
document.addEventListener('DOMContentLoaded', function () {
	
	
	
	
	
	


    // --- CURRENT YEAR (for footers) ---
    const yearSpanIds = ['current-year', 'current-year-layout', 'current-year-details', 'current-year-listings'];
    yearSpanIds.forEach(id => {
        const span = document.getElementById(id);
        if (span) {
            span.textContent = new Date().getFullYear();
        }
    });

    // --- Mobile Navigation Toggle ---
    const hamburgerButton = document.getElementById('hamburgerButton');
    const mobileNavPanel = document.getElementById('mobileNavPanel');
    const closeMobileNavButton = document.getElementById('closeMobileNavButton');
    const siteBodyForMobileNav = document.body;

    if (hamburgerButton && mobileNavPanel && closeMobileNavButton) {
        hamburgerButton.addEventListener('click', function () {
            mobileNavPanel.classList.add('is-open');
            siteBodyForMobileNav.classList.add('mobile-nav-is-open'); // For overlay
            this.setAttribute('aria-expanded', 'true');
        });

        closeMobileNavButton.addEventListener('click', function () {
            mobileNavPanel.classList.remove('is-open');
            siteBodyForMobileNav.classList.remove('mobile-nav-is-open');
            hamburgerButton.setAttribute('aria-expanded', 'false');
        });

        // Optional: Close mobile nav if user clicks on the overlay
        siteBodyForMobileNav.addEventListener('click', function (event) {
            if (siteBodyForMobileNav.classList.contains('mobile-nav-is-open') &&
                event.target === siteBodyForMobileNav) { // Clicked on body itself (overlay)
                mobileNavPanel.classList.remove('is-open');
                siteBodyForMobileNav.classList.remove('mobile-nav-is-open');
                hamburgerButton.setAttribute('aria-expanded', 'false');
            }
        });

        // Optional: Close mobile nav if user presses Escape key
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && mobileNavPanel.classList.contains('is-open')) {
                mobileNavPanel.classList.remove('is-open');
                siteBodyForMobileNav.classList.remove('mobile-nav-is-open');
                hamburgerButton.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // Find places near me
    const locationPermissionMessage = document.getElementById('locationPermissionMessage');
    const enableLocationBtn = document.getElementById('enableLocationBtn');
    const nearbyControls = document.getElementById('nearbyControls');
    const radiusSlider = document.getElementById('radiusSlider');
    const radiusValueDisplay = document.getElementById('radiusValue');
    const findNearbyBtn = document.getElementById('findNearbyBtn');
    const nearbyPlacesResults = document.getElementById('nearbyPlacesResults');
    const nearbyLoadingSpinner = document.getElementById('nearbyLoadingSpinner');
    const visualActivitiesScroller = document.getElementById('visualActivitiesScroller');
    const activitiesScrollPrev = document.getElementById('activitiesScrollPrev');
    const activitiesScrollNext = document.getElementById('activitiesScrollNext');
    const visualActivitiesWrapper = document.querySelector('.visual-activities-section .activities-scroller-wrapper');

    let currentUserLatitude = null;
    let currentUserLongitude = null;

    function checkVisualActivitiesScrollability() {
        if (!visualActivitiesScroller || !visualActivitiesWrapper) return; // Guard clause

        const firstItem = visualActivitiesScroller.querySelector('.category-item');
        if (!firstItem) { // No items, hide arrows
            if (activitiesScrollPrev) activitiesScrollPrev.style.display = 'none';
            if (activitiesScrollNext) activitiesScrollNext.style.display = 'none';
            visualActivitiesWrapper.classList.add('no-scroll');
            return;
        }

        const canScrollLeft = visualActivitiesScroller.scrollLeft > 5;
        const canScrollRight = visualActivitiesScroller.scrollLeft < (visualActivitiesScroller.scrollWidth - visualActivitiesScroller.clientWidth - 5);

        if (activitiesScrollPrev) activitiesScrollPrev.style.display = canScrollLeft ? 'flex' : 'none';
        if (activitiesScrollNext) activitiesScrollNext.style.display = canScrollRight ? 'flex' : 'none';
        visualActivitiesWrapper.classList.toggle('no-scroll', !canScrollLeft && !canScrollRight);

        // Also re-evaluate disable state of buttons (optional if display:none handles it)
        if (activitiesScrollPrev) activitiesScrollPrev.disabled = !canScrollLeft;
        if (activitiesScrollNext) activitiesScrollNext.disabled = !canScrollRight;
    }

    if (visualActivitiesScroller && activitiesScrollPrev && activitiesScrollNext && visualActivitiesWrapper) {
        const firstItemForWidth = visualActivitiesScroller.querySelector('.category-item');
        // Recalculate itemWidthWithGap inside this block if needed, or pass it to checkVisualActivitiesScrollability if it uses it.
        // For now, checkVisualActivitiesScrollability determines based on current scroll state.
        const itemWidthWithGap = firstItemForWidth ? firstItemForWidth.offsetWidth + parseInt(getComputedStyle(firstItemForWidth.parentElement).gap || '20') : 170;
        const scrollAmount = itemWidthWithGap * 2;

        activitiesScrollPrev.addEventListener('click', () => { visualActivitiesScroller.scrollLeft -= scrollAmount; });
        activitiesScrollNext.addEventListener('click', () => { visualActivitiesScroller.scrollLeft += scrollAmount; });

        visualActivitiesScroller.addEventListener('scroll', checkVisualActivitiesScrollability);
        window.addEventListener('resize', checkVisualActivitiesScrollability);

        const visualActivityItemsContainer = visualActivitiesScroller.querySelector('.activity-grid-items');
        if (visualActivityItemsContainer) {
            const observer = new MutationObserver(checkVisualActivitiesScrollability); // Now function is in scope
            observer.observe(visualActivityItemsContainer, { childList: true, subtree: true });
        }
        checkVisualActivitiesScrollability(); // Initial check, function is now in scope
    }


    function fetchNearbyPlaces() {
        if (!currentUserLatitude || !currentUserLongitude) {
            if (nearbyPlacesResults) nearbyPlacesResults.innerHTML = '<p class="text-center text-red-500">Could not get your current location to find nearby places.</p>';
            if (locationPermissionMessage) locationPermissionMessage.style.display = 'block';
            if (nearbyControls) nearbyControls.style.display = 'none';
            hideLoadingModal();
            return;
        }

        const radius = radiusSlider ? radiusSlider.value : 25;
        if (nearbyLoadingSpinner) nearbyLoadingSpinner.style.display = 'block';
        if (nearbyPlacesResults) nearbyPlacesResults.innerHTML = '';

        // --- THIS IS THE KEY CHANGE ---
        // Use the globally defined window.nearbyListingsUrl
        const baseNearbyUrl = typeof window.nearbyListingsUrl !== 'undefined' ?
            window.nearbyListingsUrl :
            '/listings/nearby'; // Fallback to hardcoded relative path if global var is missing
        const fetchUrl = `${baseNearbyUrl}?latitude=${currentUserLatitude}&longitude=${currentUserLongitude}&radius=${radius}`;
        // --- END OF KEY CHANGE ---

        //console.log("Fetching nearby places with URL:", fetchUrl); // DEBUG

        showLoadingModal("Searching for nearby places...");

        fetch(fetchUrl, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.statusText);
                }
                return response.json();
            })
            .then(data => {
                if (nearbyLoadingSpinner) nearbyLoadingSpinner.style.display = 'none';
                if (data.businesses && data.businesses.length > 0) {
                    data.businesses.forEach(business => {
                        // Build your listing card HTML dynamically here
                        // This should match the structure of .listing-card from your other pages
                        const cardHtml = `
                <div class="listing-card">
                    <a href="/listing/${business.slug}" class="listing-card-link-wrapper">
                        <div class="card-image-container">
                            <img src="${business.main_image_url || window.placeholderCardImageUrl}" alt="${business.name}">
                        </div>
                        <div class="card-content-area">
                            <h3>${business.name.length > 30 ? business.name.substring(0, 27) + '...' : business.name}</h3>
                            <p class="listing-location"><i class="fas fa-map-marker-alt"></i> ${business.county ? business.county.name : ''}</p>
                            ${business.distance ? `<p class="text-xs text-gray-500">Approx. ${parseFloat(business.distance).toFixed(1)} km away</p>` : ''}
                        </div>
                    </a>
                </div>
            `;
                        if (nearbyPlacesResults) nearbyPlacesResults.insertAdjacentHTML('beforeend', cardHtml);
                    });
                } else {
                    if (nearbyPlacesResults) nearbyPlacesResults.innerHTML = '<p class="text-center text-gray-500 col-span-full">No places found within ' + radius + 'km. Try increasing the radius.</p>';
                }
            })
            .catch(error => {
                hideLoadingModal(); // Ensure modal is hidden on error too
                if (nearbyPlacesResults) nearbyPlacesResults.innerHTML = `<p class="text-center text-red-500 col-span-full">Error fetching nearby places: ${error.message}</p>`;
                console.error('Error fetching nearby places:', error);
            })
            .finally(() => {
                hideLoadingModal(); // <<<< THIS IS THE CALL
                if (nearbyLoadingSpinner) nearbyLoadingSpinner.style.display = 'none';
                // Consider if modal should *always* be hidden here,
                // but if .catch shows an error in the modal, you might want it to persist for a bit.
                // The current hideLoadingModal has a setTimeout for auto-close if set.
            });
    }

    function requestLocationAndLoad() {
        if (nearbyLoadingSpinner) nearbyLoadingSpinner.style.display = 'block';
        if (locationPermissionMessage) locationPermissionMessage.style.display = 'none'; // Hide prompt

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function (position) { // Success
                    currentUserLatitude = position.coords.latitude;
                    currentUserLongitude = position.coords.longitude;
                    if (nearbyControls) nearbyControls.style.display = 'block'; // Show slider and button
                    if (nearbyLoadingSpinner) nearbyLoadingSpinner.style.display = 'none';
                    fetchNearbyPlaces(); // Fetch with default radius
                },
                function (error) { // Error or Denied
                    if (nearbyLoadingSpinner) nearbyLoadingSpinner.style.display = 'none';
                    if (locationPermissionMessage) {
                        locationPermissionMessage.innerHTML = `<p>Could not get your location (Error: ${error.message}). Please enable location services and try again, or ensure you are on HTTPS.</p><button id="enableLocationBtnRetry" class="btn btn-primary mt-2">Try Again</button>`;
                        locationPermissionMessage.style.display = 'block';
                        const retryBtn = document.getElementById('enableLocationBtnRetry');
                        if (retryBtn) retryBtn.addEventListener('click', requestLocationAndLoad);
                    }
                    if (nearbyControls) nearbyControls.style.display = 'none';
                    if (nearbyPlacesResults) nearbyPlacesResults.innerHTML = '<p class="text-center text-red-500 col-span-full">Location access is needed to find places near you.</p>';

                },
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
            );
        } else {
            if (nearbyLoadingSpinner) nearbyLoadingSpinner.style.display = 'none';
            if (locationPermissionMessage) {
                locationPermissionMessage.innerHTML = '<p>Geolocation is not supported by this browser.</p>';
                locationPermissionMessage.style.display = 'block';
            }
            if (nearbyControls) nearbyControls.style.display = 'none';
        }
    }

    //SHARE VIA WHATSAPP
    const whatsappShareBtn = document.getElementById('whatsappShareBtn');
    if (whatsappShareBtn) {

        whatsappShareBtn.addEventListener('click', function (event) {
            event.preventDefault(); // Prevent default <a> tag behavior

            // Get URL and Title
            let pageUrl = this.dataset.url || window.location.href;
            let pageTitle = this.dataset.title || document.title; // Or a specific element's text content

            // Construct the WhatsApp share URL
            // The text parameter is pre-filled in the user's WhatsApp message
            let whatsappUrl = `https://api.whatsapp.com/send?text=` +
                encodeURIComponent(`Check out this listing: ${pageTitle} - ${pageUrl}`);

            // For mobile, try the wa.me link which often works better
            if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
                whatsappUrl = `https://wa.me/?text=` +
                    encodeURIComponent(`Check out this listing: ${pageTitle} - ${pageUrl}`);
            }
            window.open(whatsappUrl, '_blank');
        });
    }


    //END SHARE
	
// =============================================================
    // === REPORT ITEM MODAL LOGIC (Generic for Business or Event) ===
    // =============================================================
    const reportItemButtons = document.querySelectorAll('[id^="reportBusinessBtn"], [id^="reportEventBtn"]'); // Selects all report buttons by ID pattern
    const reportModal = document.getElementById('reportModal'); // From layouts.site.blade.php

    // Elements within the modal (ensure these IDs are in your modal HTML)
    const reportItemNameSpan = document.getElementById('reportBusinessName'); // ID in modal for item name (can be kept generic)
    const reportItemBusinessIdInput = document.getElementById('report_item_business_id'); // Hidden input in modal
    const reportItemEventIdInput = document.getElementById('report_item_event_id');   // Hidden input in modal
    const reportItemForm = document.getElementById('reportItemForm');                 // ID of <form> in modal
    const reportDetailsTextarea = document.getElementById('report_details');          // Textarea in modal
    const reportDetailsCharCount = document.getElementById('reportDetailsCharCount'); // Char count display in modal
    const reportFormMessage = document.getElementById('reportFormMessage');           // Message div in modal

    // Generic function to close report modal by data-dismiss attribute
    // This can be part of your global modal helpers if you have many modals
    document.querySelectorAll('[data-dismiss="reportModal"]').forEach(button => {
        button.addEventListener('click', () => {
            if (reportModal) {
                reportModal.classList.remove('is-visible');
                setTimeout(() => {
                    // Check again in case it was re-shown by another action quickly
                    if (!reportModal.classList.contains('is-visible')) {
                        reportModal.style.display = 'none';
                    }
                }, 300); // Match your CSS transition duration for opacity/visibility
            }
        });
    });

    // Ensure all modal elements are actually found before attaching listeners
    if (reportItemButtons.length > 0 && reportModal && reportItemNameSpan &&
        reportItemBusinessIdInput && reportItemEventIdInput && reportItemForm &&
        reportDetailsTextarea && reportDetailsCharCount && reportFormMessage) {

        reportItemButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Get data from the clicked button's data-* attributes
                const itemName = this.dataset.itemName || "this item";
                const itemId = this.dataset.itemId;
                const itemType = this.dataset.itemType; // Expected: 'business' or 'event'

                if (!itemId || !itemType) {
                    console.error("Report Button Error: Item ID or Type not found. Button:", this);
                    showLoadingModal("Could not initiate report. Item identifier missing.", true, 3500); // Use global modal
                    return;
                }

                // Populate modal title and hidden inputs
                reportItemNameSpan.textContent = itemName;
                reportItemBusinessIdInput.value = ''; // Clear both first
                reportItemEventIdInput.value = '';

                if (itemType === 'business') {
                    reportItemBusinessIdInput.value = itemId;
                } else if (itemType === 'event') {
                    reportItemEventIdInput.value = itemId;
                } else {
                    console.error("Report Button Error: Unknown itemType:", itemType);
                    showLoadingModal("Cannot report this item type.", true, 3000);
                    return;
                }

                // Reset form state
                reportFormMessage.textContent = '';
                reportFormMessage.className = ''; // Clear previous success/error classes
                reportItemForm.reset(); // Reset form fields (reason dropdown, details textarea)
                if (reportDetailsTextarea) reportDetailsTextarea.value = ''; // Ensure textarea is cleared by reset
                if (typeof updateReportCharCount === 'function') { // Check if function exists
                    updateReportCharCount(); // Call the char count function if available
                }


                // Show the modal
                reportModal.style.display = 'flex';
                void reportModal.offsetWidth; // Trigger reflow for transition
                reportModal.classList.add('is-visible');
            });
        });

        // Handle form submission via AJAX
        reportItemForm.addEventListener('submit', function(event) {
            event.preventDefault(); // Stop default HTML form submission
            showLoadingModal("Submitting your report...", false); // Use global loading modal (no auto-close)

            const formData = new FormData(this); // 'this' is the form
            const actionUrl = this.action;       // Get action URL from form tag (should be {{ route('listings.report.submit') }})

            fetch(actionUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json', // We expect a JSON response from Laravel
                },
                body: formData
            })
            .then(response => {
                // Regardless of ok status, try to parse as JSON because Laravel validation errors are JSON
                return response.json().then(data => ({ status: response.status, ok: response.ok, data }));
            })
            .then(({ status, ok, data }) => { // Destructure the response object
                hideLoadingModal();
                if (ok && data.success) { // Check for success flag from your controller
                    reportFormMessage.textContent = data.message || 'Report submitted successfully. Thank you!';
                    reportFormMessage.className = 'form-message success'; // For styling success
                    setTimeout(() => { // Auto-close report modal on success
                        if (reportModal.classList.contains('is-visible')) {
                            reportModal.classList.remove('is-visible');
                            setTimeout(() => { reportModal.style.display = 'none'; }, 300);
                        }
                    }, 3000); // Close after 3 seconds
                } else { // Handle business logic errors or validation errors
                    let errorHtml = 'Error: ' + (data.message || 'Could not submit report.');
                    if (data.errors) {
                        errorHtml += '<br><small style="text-align:left; display:block; margin-top:5px;">Details:<br>';
                        for (const field in data.errors) {
                            errorHtml += data.errors[field].join('<br>') + '<br>';
                        }
                        errorHtml += '</small>';
                    }
                    reportFormMessage.innerHTML = errorHtml;
                    reportFormMessage.className = 'form-message error'; // For styling error
                }
            })
            .catch(error => { // Network errors or truly unexpected issues
                hideLoadingModal();
                reportFormMessage.textContent = 'An unexpected network error occurred. Please try again.';
                reportFormMessage.className = 'form-message error';
                console.error('Report submission fetch/network error:', error);
            });
        });
    } else {
        console.warn("One or more report modal elements are missing from the DOM. Report functionality may not work.");
    }

    // Character counter for report details textarea
    // Moved function definition here to ensure it's always available when listener is set
    function updateReportCharCount() {
        if (reportDetailsTextarea && reportDetailsCharCount) {
            const currentLength = reportDetailsTextarea.value.length;
            const maxLength = parseInt(reportDetailsTextarea.getAttribute('maxlength')) || 150;
            reportDetailsCharCount.textContent = `${currentLength} / ${maxLength} characters`;
        }
    }
    if (reportDetailsTextarea) { // Check if element exists on page before adding listener
        reportDetailsTextarea.addEventListener('input', updateReportCharCount);
        updateReportCharCount(); // Initial count if textarea has pre-filled content (e.g. old input)
    }
    // =============================================================
    // === END OF REPORT MODAL LOGIC INTEGRATION ===
    // =============================================================

	
	
	
	
	
	
	
    // Only setup if the section exists (i.e., user is logged in and on homepage)
    if (locationPermissionMessage && enableLocationBtn && nearbyControls) {
        // Check if we already have permission or stored location (more advanced)
        // For now, always start by showing the permission prompt/button
        locationPermissionMessage.style.display = 'block';
        nearbyControls.style.display = 'none';

        enableLocationBtn.addEventListener('click', requestLocationAndLoad);

        if (radiusSlider && radiusValueDisplay) {
            radiusSlider.oninput = function () {
                radiusValueDisplay.textContent = this.value;
            }
        }
        if (findNearbyBtn) {
            findNearbyBtn.addEventListener('click', fetchNearbyPlaces);
        }
    }

    // --- HOMEPAGE: STICKY SEARCHABLE DROPDOWNS ---
    function initializeSearchableDropdown(visibleInputId, listContainerSelector, hiddenInputIdForSlug) {
        const searchInput = document.getElementById(visibleInputId);
        if (!searchInput) return; // Element not on this page

        const hiddenQueryInput = document.getElementById(hiddenInputIdForSlug);
        if (!hiddenQueryInput) {
            console.error(`Hidden input #${hiddenInputIdForSlug} not found for dropdown: ${visibleInputId}`);
            return;
        }

        const dropdownGroup = searchInput.closest('.searchable-dropdown-group');
        if (!dropdownGroup) {
            console.error(`Dropdown group not found for input: ${visibleInputId}`);
            return;
        }
        const dropdownListContainer = dropdownGroup.querySelector(listContainerSelector);
        if (!dropdownListContainer) {
            console.error(`Dropdown list container '${listContainerSelector}' not found for input: ${visibleInputId}`);
            return;
        }

        const listItems = Array.from(dropdownListContainer.querySelectorAll('div[data-value]'));

        function filterAndDisplayList(showAll = false) {
            const filterVal = searchInput.value.toLowerCase();
            let hasVisibleItems = false;
            listItems.forEach(item => {
                const itemText = item.textContent.toLowerCase();
                const isMatch = itemText.includes(filterVal);
                item.style.display = (showAll || isMatch) ? '' : 'none';
                if (showAll || isMatch) hasVisibleItems = true;
            });
            dropdownListContainer.style.display = (hasVisibleItems && (searchInput.value.length > 0 || showAll)) || (document.activeElement === searchInput && searchInput.value.length === 0 && showAll) ? 'block' : 'none';
        }

        searchInput.addEventListener('focus', () => {
            hiddenQueryInput.value = ''; // Clear hidden slug when user focuses to type/reselect
            filterAndDisplayList(true);
        });
        searchInput.addEventListener('input', () => {
            hiddenQueryInput.value = ''; // Clear hidden slug as user is typing new text
            filterAndDisplayList(false);
        });

        listItems.forEach(item => {
            item.addEventListener('click', function () {
                searchInput.value = this.textContent;        // Set visible input to text
                hiddenQueryInput.value = this.dataset.value; // Set hidden input to slug (data-value)
                dropdownListContainer.style.display = 'none';
                // searchInput.blur(); // Optional: remove focus
            });
        });

        // Close dropdown if clicked outside THIS specific dropdown group
        document.addEventListener('click', function (event) {
            if (!dropdownGroup.contains(event.target)) {
                dropdownListContainer.style.display = 'none';
            }
        });
        searchInput.addEventListener('keydown', function (event) {
            if (event.key === "Escape") {
                dropdownListContainer.style.display = 'none';
                searchInput.blur();
            }
        });
    }
    // Initialize for homepage search bar
    if (document.getElementById('county-search-input')) {
        initializeSearchableDropdown('county-search-input', '.county-dropdown-list', 'hidden_county_query');
    }
    if (document.getElementById('category-search-input')) {
        initializeSearchableDropdown('category-search-input', '.category-dropdown-list', 'hidden_category_query');
    }



    // --- HOMEPAGE: TOP CATEGORIES/ACTIVITIES SCROLLER PAUSE (CSS Animation) ---
    const topCategoriesGridEl = document.getElementById('topCategoriesGrid');
    const topCategoriesScrollerWrapperEl = document.querySelector('.top-categories-list .top-categories-scroller-wrapper');
    if (topCategoriesGridEl && topCategoriesScrollerWrapperEl) {
        topCategoriesScrollerWrapperEl.addEventListener('mouseenter', () => { topCategoriesGridEl.style.animationPlayState = 'paused'; });
        topCategoriesScrollerWrapperEl.addEventListener('mouseleave', () => { topCategoriesGridEl.style.animationPlayState = 'running'; });
    }

    // --- LISTING DETAIL PAGE: SIMPLELIGHTBOX GALLERY ---
    const lightboxGalleryLinks = document.querySelectorAll('.business-lightbox-gallery a');
    if (lightboxGalleryLinks.length > 0 && typeof SimpleLightbox !== 'undefined') {
        try {
            let lightboxInstance = new SimpleLightbox('.business-lightbox-gallery a', {
                captionDelay: 250, captionsData: 'title', loop: true,
                navText: ['‹', '›'], closeText: '×'
            });
        } catch (e) { console.error("Error initializing SimpleLightbox:", e); }

        const viewAllTrigger = document.querySelector('.small-thumbnail-item-final.view-all-trigger');
        if (viewAllTrigger) {
            viewAllTrigger.addEventListener('click', function (event) {
                event.preventDefault();
                const firstLightboxImageLink = document.querySelector('.business-lightbox-gallery a:first-child');
                if (firstLightboxImageLink) {
                    // SimpleLightbox typically enhances links, so clicking one directly works.
                    // If direct click doesn't work, and 'lightboxInstance' is accessible:
                    // lightboxInstance.open(firstLightboxImageLink);
                    firstLightboxImageLink.click();
                } else { console.warn("No images found for lightbox to open."); }
            });
        }
    } else if (document.querySelector('.business-lightbox-gallery')) {
        console.warn("SimpleLightbox library not found, but gallery elements are present.");
    }


    // --- LISTING PAGES (county, category, facility, tag, search): OFF-CANVAS FILTER SIDEBAR ---
    const filterToggleButton = document.getElementById('filterToggleButton');
    const filtersSidebar = document.getElementById('filtersSidebar');
    const closeFiltersButton = document.getElementById('closeFiltersButton');
    const siteBodyForFilters = document.body;

    if (filterToggleButton && filtersSidebar && closeFiltersButton) {
        filterToggleButton.addEventListener('click', function () {
            filtersSidebar.classList.add('is-open');
            siteBodyForFilters.classList.add('filters-sidebar-open');
        });
        closeFiltersButton.addEventListener('click', function () {
            filtersSidebar.classList.remove('is-open');
            siteBodyForFilters.classList.remove('filters-sidebar-open');
        });
        // Click on overlay (body when sidebar is open) to close
        siteBodyForFilters.addEventListener('click', function (event) {
            if (siteBodyForFilters.classList.contains('filters-sidebar-open') &&
                event.target === siteBodyForFilters && // Clicked on body itself
                !filtersSidebar.contains(event.target) && // Not inside sidebar
                !filterToggleButton.contains(event.target) // Not on the toggle button
            ) {
                filtersSidebar.classList.remove('is-open');
                siteBodyForFilters.classList.remove('filters-sidebar-open');
            }
        });
    }

    // --- LISTING PAGES: Price Slider and Sort Dropdown ---
    // Helper to initialize price slider and sort for different listing pages
    function initializeListingPageInteractions(pageTypeSuffix = 'listing') {
        const priceSlider = document.getElementById(`price_slider_input_${pageTypeSuffix}`);
        const priceValueDisplay = document.getElementById(`priceValueDisplay${pageTypeSuffix.charAt(0).toUpperCase() + pageTypeSuffix.slice(1)}`); // e.g., priceValueDisplayListing
        if (priceSlider && priceValueDisplay) {
            function updateDisplay() { priceValueDisplay.textContent = "Ksh " + Number(priceSlider.value).toLocaleString(); }
            updateDisplay();
            priceSlider.addEventListener('input', updateDisplay);
        }

        const sortSelect = document.getElementById(`sort-by-select-${pageTypeSuffix}`);
        const sortInputHidden = document.getElementById(`sort_input_${pageTypeSuffix}_filter`);
        const filterForm = document.getElementById('filterSortForm'); // Assuming one form ID for all filter pages
        if (sortSelect && sortInputHidden && filterForm) {
            sortSelect.addEventListener('change', function () {
                sortInputHidden.value = this.value;
                filterForm.submit();
            });
        }
    }
    // Call for different listing page types if their element IDs are unique
    if (document.getElementById('price_slider_input_listing')) initializeListingPageInteractions('listing'); // For county.blade.php
    if (document.getElementById('price_slider_input_category')) initializeListingPageInteractions('category'); // For category.blade.php
    if (document.getElementById('price_slider_input_facility')) initializeListingPageInteractions('facility'); // For facility.blade.php
    if (document.getElementById('price_slider_input_tag')) initializeListingPageInteractions('tag');       // For tag.blade.php


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

        getLocBtnForm.addEventListener('click', function () {
            if (geoHelpNoticeForm) geoHelpNoticeForm.style.display = 'none';
            showLoadingModal('Fetching your current location...');
            latInputForm.readOnly = true; lngInputForm.readOnly = true;
            latInputForm.value = ''; lngInputForm.value = '';

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function (position) { // Success
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
                    function (error) { // Geolocation API error
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
                if (geoMsgForm) {
                    geoMsgForm.innerHTML = "<strong>Notice:</strong> Geolocation not supported. Please enter coordinates manually.";
                    geoMsgForm.style.color = '#fd7e14'; // Orange
                    geoMsgForm.style.display = 'block';
                }
                if (geoHelpNoticeForm) geoHelpNoticeForm.style.display = 'block';
                latInputForm.readOnly = false; lngInputForm.readOnly = false;
            }
        });
    }

    // --- BUSINESS OWNER FORMS: Image Upload Main Image Selector ---
    const imagesInputBOForms = document.getElementById('images'); // This ID is from business owner forms
    const newMainImageSelectBOForms = document.getElementById('new_main_image_index');
    if (imagesInputBOForms && newMainImageSelectBOForms) {
        imagesInputBOForms.addEventListener('change', function (event) {
            newMainImageSelectBOForms.innerHTML = '<option value="">-- Designate new main (optional) --</option>';
            if (event.target.files && event.target.files.length > 0) {
                for (let i = 0; i < event.target.files.length; i++) {
                    const option = document.createElement('option');
                    option.value = i;
                    let fileName = event.target.files[i].name;
                    option.textContent = `New Image ${i + 1}: ${fileName.length > 30 ? fileName.substring(0, 27) + '...' : fileName}`;
                    newMainImageSelectBOForms.appendChild(option);
                }
            }
        });
    }

    // --- BUSINESS OWNER FORMS: Schedule Time Inputs Toggle Initialization ---
    // This uses the global toggleTimeInputs function
    document.querySelectorAll('input[type="checkbox"][id^="schedule_closed_"]').forEach(cb => {
        const day = cb.id.replace('schedule_closed_', ''); // Extracts 'Monday', 'Tuesday', etc.
        if (typeof toggleTimeInputs === 'function') {
            toggleTimeInputs(cb, day); // Call to set initial state based on 'checked'
        }
    });
	
	const eventWhatsappShareBtn = document.getElementById('eventWhatsappShareBtn');
if (eventWhatsappShareBtn) {
    eventWhatsappShareBtn.addEventListener('click', function(event) {
        event.preventDefault();

        let pageUrl = this.dataset.url || window.location.href;
        let pageTitle = this.dataset.title || document.title;

        let whatsappUrl = `https://api.whatsapp.com/send?text=` +
                          encodeURIComponent(`Check out this event: ${pageTitle} - ${pageUrl}`);

        if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
            whatsappUrl = `https://wa.me/?text=` +
                          encodeURIComponent(`Check out this event: ${pageTitle} - ${pageUrl}`);
        }
        // showLoadingModal("Opening WhatsApp...", true, 1500); // Optional modal
        window.open(whatsappUrl, '_blank');
    });
}


}); // END DOMContentLoaded