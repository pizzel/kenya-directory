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

// =============================================================
// === BEGIN LIKES FOR BLOG ===
// =============================================================

const likeButton = document.getElementById('likeButton');
if (likeButton) {
    likeButton.addEventListener('click', function() {
        // Prevent multiple rapid clicks
        if (this.classList.contains('is-processing')) {
            return;
        }
        this.classList.add('is-processing');

        const postId = this.dataset.postId;
        const url = this.dataset.likeUrl;

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (response.status === 401) { // Not authenticated
                window.location.href = '/login';
                throw new Error('User not authenticated.');
            }
            if (!response.ok) {
                throw new Error('Network response was not ok.');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const likeCountSpan = document.getElementById('likeCount');
                likeCountSpan.textContent = data.likes_count;
                
                if (data.is_liked) {
                    this.classList.add('is-liked');
                } else {
                    this.classList.remove('is-liked');
                }
            }
        })
        .catch(error => {
            console.error('Error toggling like:', error);
        })
        .finally(() => {
            this.classList.remove('is-processing');
        });
    });
}	
	
	
// =============================================================
// === END LIKES FOR BLOG ===
// =============================================================
// =============================================================
// === BEGIN LIKES FOR BUSINESS ===
// =============================================================


// --- Business Listing Like Button ---
const businessLikeButton = document.getElementById('likeButtonBusiness');
if (businessLikeButton) {
    businessLikeButton.addEventListener('click', function() {
        if (this.classList.contains('is-processing')) return;
        this.classList.add('is-processing');

        const url = this.dataset.likeUrl;

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (response.status === 401) {
                window.location.href = '/login';
                throw new Error('User not authenticated.');
            }
            if (!response.ok) throw new Error('Network response was not ok.');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const likeCountSpan = document.getElementById('likeCountBusiness');
                likeCountSpan.textContent = data.likes_count;
                this.classList.toggle('is-liked', data.is_liked); // Simpler way to add/remove class
            }
        })
        .catch(error => console.error('Error toggling business like:', error))
        .finally(() => this.classList.remove('is-processing'));
    });
}	

// =============================================================
// === END LIKES FOR BUSINESS ===
// =============================================================
// =============================================================
// === BEGIN WISHLIST CONTROLLER FOR BUSINESS ===
// =============================================================		
const wishlistContainer = document.getElementById('wishlistContainer');
if (wishlistContainer) {
    wishlistContainer.addEventListener('click', function(event) {
        const button = event.target.closest('button[data-action]');
        if (!button) return;

        if (button.classList.contains('is-processing')) return;
        button.classList.add('is-processing');

        const url = this.dataset.toggleUrl;
        const action = button.dataset.action;
        const formData = new FormData();
        formData.append('action', action);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
        showLoadingModal('Updating Wishlist...');

        fetch(url, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(response => {
            if (response.status === 401) { window.location.href = '/login'; throw new Error('Unauthenticated.'); }
            if (!response.ok) { throw new Error('Server returned an error.'); }
            return response.json();
        })
        .then(data => {
            hideLoadingModal();
            if (data.success) {
                // <<< THIS IS THE DEFINITIVE UI UPDATE LOGIC >>>

                // Find all buttons using their data-action attributes. This is robust.
                const allButtons = {
                    add: wishlistContainer.querySelector('button[data-action="add"]'),
                    remove: wishlistContainer.querySelector('button[data-action="remove"]'),
                    markDone: wishlistContainer.querySelector('button[data-action="toggle_done"][data-status-target="done"]'),
                    unmarkDone: wishlistContainer.querySelector('button[data-action="toggle_done"][data-status-target="wished"]')
                };

                // Hide all buttons first to ensure a clean state, checking if they exist.
                Object.values(allButtons).forEach(btn => {
                    if (btn) btn.style.display = 'none';
                });

                // Now, selectively show the correct buttons based on the new state from the server.
                if (data.is_in_wishlist) {
                    if (allButtons.remove) allButtons.remove.style.display = 'block';
                    
                    if (data.is_done) {
                        if (allButtons.unmarkDone) allButtons.unmarkDone.style.display = 'block';
                    } else {
                        if (allButtons.markDone) allButtons.markDone.style.display = 'block';
                    }
                } else {
                    if (allButtons.add) allButtons.add.style.display = 'block';
                }
            }
        })
        .catch(error => {
            hideLoadingModal();
            console.error('Error updating wishlist:', error);
            showLoadingModal('Could not update wishlist. Please try again.', true, 3000);
        })
        .finally(() => {
            button.classList.remove('is-processing');
        });
    });
}
// =============================================================
// === END WISHLIST CONTROLLER FOR BUSINESS ===
// =============================================================	
// =============================================================
// === BEGIN WISHLIST CONTROLLER FOR EVENTS ===
// =============================================================
// In script.js, inside DOMContentLoaded

// --- Event Page Wishlist Button (AJAX) ---
const eventWishlistContainer = document.getElementById('eventWishlistContainer');
if (eventWishlistContainer) {
    eventWishlistContainer.addEventListener('click', function(event) {
        const button = event.target.closest('button[data-action]');
        if (!button) return;

        if (button.classList.contains('is-processing')) return;
        button.classList.add('is-processing');

        const url = this.dataset.toggleUrl;
        const action = button.dataset.action;
        const formData = new FormData();
        formData.append('action', action);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
        showLoadingModal('Updating Event List...');

        fetch(url, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(response => {
            if (response.status === 401) { window.location.href = '/login'; throw new Error('Unauthenticated.'); }
            if (!response.ok) { throw new Error('Server returned an error.'); }
            return response.json();
        })
        .then(data => {
            hideLoadingModal();
            if (data.success) {
                // Find all possible buttons within the container once.
                const allButtons = {
                    add: eventWishlistContainer.querySelector('button[data-action="add"]'),
                    remove: eventWishlistContainer.querySelector('button[data-action="remove"]'),
                    markDone: eventWishlistContainer.querySelector('button[data-action="toggle_done"][data-status-target="done"]'),
                    unmarkDone: eventWishlistContainer.querySelector('button[data-action="toggle_done"][data-status-target="wished"]')
                };

                // Hide all buttons first to ensure a clean state
                Object.values(allButtons).forEach(btn => {
                    if (btn) btn.style.display = 'none';
                });

                // Selectively show the correct buttons based on the new state
                if (data.is_in_wishlist) {
                    if (allButtons.remove) {
                        allButtons.remove.style.display = 'block';
                        // Update the text of the remove button
                        allButtons.remove.innerHTML = `<i class="fas fa-heart mr-2"></i> ${data.is_done ? 'In My List (Attended)' : 'Remove from My Events'}`;
                    }
                    
                    if (data.is_done) {
                        if (allButtons.unmarkDone) allButtons.unmarkDone.style.display = 'block';
                    } else {
                        if (allButtons.markDone) allButtons.markDone.style.display = 'block';
                    }
                } else {
                    if (allButtons.add) allButtons.add.style.display = 'block';
                }
            }
        })
        .catch(error => {
            hideLoadingModal();
            console.error('Error updating event wishlist:', error);
            showLoadingModal('Could not update event list. Please try again.', true, 3000);
        })
        .finally(() => {
            button.classList.remove('is-processing');
        });
    });
}
// =============================================================
// === END WISHLIST CONTROLLER FOR EVENTS ===
// =============================================================
// =============================================================
// === BEGIN EVENT COMMENT CONTROLLER ===
// =============================================================

const eventReviewForm = document.getElementById('eventReviewForm');
if (eventReviewForm) {
    eventReviewForm.addEventListener('submit', function(event) {
        event.preventDefault();
        
        const form = this;
        const submitButton = form.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.textContent = 'Submitting...';

        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                // CSRF token is already sent with FormData if you have a hidden input
            },
            body: formData
        })
        .then(response => response.json().then(data => ({ ok: response.ok, status: response.status, data })))
        .then(({ ok, status, data }) => {
            if (ok && data.success) {
                // Success!
						const commentsList = document.getElementById('event-comments-list');
						const noReviewsMessage = document.getElementById('no-event-reviews-message');
						const loggedInUserId = window.AUTH_USER_ID;

						// 1. Find ALL comments by the current user using the data-attribute
						if (loggedInUserId && commentsList) {
							const userComments = commentsList.querySelectorAll(`[data-user-id="${loggedInUserId}"]`);
							// 2. Loop through and remove every single one of them
							userComments.forEach(comment => comment.remove());
						}

						// 3. Add the new comment HTML to the top of the list
						if (commentsList && data.html) {
							commentsList.insertAdjacentHTML('afterbegin', data.html);
						}
						
						// 4. If the "no reviews" message exists, remove it
						if (noReviewsMessage) {
							noReviewsMessage.remove();
						}

						// <<< END OF DEFINITIVE LOGIC >>>

						form.reset();
						showLoadingModal(data.message || 'Feedback submitted successfully!', true, 3000);

					} else {
                // Handle validation errors
                const errorMessage = Object.values(data.errors).map(e => e.join('<br>')).join('<br>');
                showLoadingModal(`Error: <br><small>${errorMessage}</small>`, true, 5000);
            }
        })
        .catch(error => {
            console.error('Error submitting review:', error);
            showLoadingModal('An unexpected network error occurred.', true, 4000);
        })
        .finally(() => {
            submitButton.disabled = false;
            submitButton.textContent = 'Submit Feedback';
        });
    });
}
// =============================================================
// === END EVENT COMMENT CONTROLLER ===
// =============================================================
// =============================================================
// === BEGIN BUSINESS COMMENT CONTROLLER ===
// =============================================================

const businessReviewForm = document.getElementById('businessReviewForm');
if (businessReviewForm) {
    businessReviewForm.addEventListener('submit', function(event) {
        event.preventDefault();
        
        const form = this;
        const submitButton = form.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.textContent = 'Submitting...';

        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })
        .then(response => response.json().then(data => ({ ok: response.ok, status: response.status, data })))
        .then(({ ok, status, data }) => {
            if (ok && data.success) {
				
						const commentsList = document.getElementById('business-comments-list');
						const noReviewsMessage = document.getElementById('no-business-reviews-message');
						const loggedInUserId = window.AUTH_USER_ID;

						// 1. Find ALL comments by the current user using the data-attribute
						if (loggedInUserId && commentsList) {
							const userComments = commentsList.querySelectorAll(`[data-user-id="${loggedInUserId}"]`);
							// 2. Loop through and remove every single one of them
							userComments.forEach(comment => comment.remove());
						}

						// 3. Add the new comment HTML to the top of the list
						if (commentsList && data.html) {
							commentsList.insertAdjacentHTML('afterbegin', data.html);
						}
						
						// 4. If the "no reviews" message exists, remove it
						if (noReviewsMessage) {
							noReviewsMessage.remove();
						}

						// <<< END OF DEFINITIVE LOGIC >>>

						form.reset();
						showLoadingModal(data.message || 'Review submitted successfully!', true, 3000);

					} else {
				
				
                const errorMessage = Object.values(data.errors || {'error': [data.message || 'An error occurred.']}).map(e => e.join('<br>')).join('<br>');
                showLoadingModal(`Error: <br><small>${errorMessage}</small>`, true, 5000);
            }
        })
        .catch(error => {
            console.error('Error submitting business review:', error);
            showLoadingModal('An unexpected network error occurred.', true, 4000);
        })
        .finally(() => {
            submitButton.disabled = false;
            submitButton.textContent = 'Submit Review';
        });
    });
}
// =============================================================
// === END BUSINESS COMMENT CONTROLLER ===
// =============================================================
// Only setup if the section exists (i.e., user is logged in and on homepage)

// =====================================================================================
// <<< BEGIN PLACES NEAR ME LOGIC >>>
// =====================================================================================
const NearbyPlacesFinder = {
    currentUserLatitude: null,
    currentUserLongitude: null,
    elements: {},
    locationStorageKey: 'discoverkenya_location_preference', // Key for permission
    visibilityStorageKey: 'discoverkenya_visibility_preference', // <<< NEW: Key for visibility

    init: function() {
        // Gather all necessary DOM elements once.
        this.elements = {
            permissionMessage: document.getElementById('locationPermissionMessage'),
            enableLocationBtn: document.getElementById('enableLocationBtn'),
            controls: document.getElementById('nearbyControls'),
            slider: document.getElementById('radiusSlider'),
            radiusDisplay: document.getElementById('radiusValue'),
            findBtn: document.getElementById('findNearbyBtn'),
            hideBtn: document.getElementById('hideNearbyBtn'),
            showContainer: document.getElementById('showNearbyContainer'),
            showBtn: document.getElementById('showNearbyBtn'),
            resultsContainer: document.getElementById('nearbyResultsContainer'),
            resultsGrid: document.getElementById('nearbyPlacesResults'),
            loadingSpinner: document.getElementById('nearbyLoadingSpinner'),
        };

        // If the main container for this feature doesn't exist on the page, do nothing.
        if (!this.elements.permissionMessage) {
            return;
        }
        
        this.addEventListeners();
        this.checkInitialState();
    },
    
    // <<< NEW: Central function to check state on page load >>>
    checkInitialState: function() {
        const locationPref = localStorage.getItem(this.locationStorageKey);
        const visibilityPref = localStorage.getItem(this.visibilityStorageKey);

        if (locationPref === 'granted') {
            // User has given permission before. Now check if they hid the section.
            if (visibilityPref === 'hidden') {
                // They hid it, so just show the "Show" button.
                this.elements.showContainer.style.display = 'block';
            } else {
                // They haven't hidden it, so auto-load.
                this.requestLocation();
            }
        } else {
            // User has denied or never been asked. Show the initial prompt.
            this.elements.permissionMessage.style.display = 'block';
        }
    },

    addEventListeners: function() {
        this.elements.enableLocationBtn?.addEventListener('click', () => this.requestLocation());
        this.elements.findBtn?.addEventListener('click', () => this.fetchPlaces());
        this.elements.hideBtn?.addEventListener('click', () => this.hideResults());
        this.elements.showBtn?.addEventListener('click', () => this.showResults());
        if (this.elements.slider && this.elements.radiusDisplay) {
            this.elements.slider.oninput = () => {
                if (this.elements.radiusDisplay) this.elements.radiusDisplay.textContent = this.elements.slider.value;
            };
        }
    },

    requestLocation: function() {
        // When requesting location, we assume the user wants to see the results.
        localStorage.setItem(this.visibilityStorageKey, 'shown'); // <<< NEW

        this.elements.permissionMessage.style.display = 'none';
        this.elements.showContainer.style.display = 'none';
        this.elements.resultsContainer.style.display = 'block';
        if (this.elements.loadingSpinner) this.elements.loadingSpinner.style.display = 'block';
        if (this.elements.resultsGrid) this.elements.resultsGrid.innerHTML = '';

        navigator.geolocation.getCurrentPosition(
            position => { // Success
                localStorage.setItem(this.locationStorageKey, 'granted');
                this.currentUserLatitude = position.coords.latitude;
                this.currentUserLongitude = position.coords.longitude;
                if (this.elements.controls) this.elements.controls.style.display = 'block';
                if (this.elements.hideBtn) this.elements.hideBtn.style.display = 'inline-block';
                this.fetchPlaces();
            },
            error => { // Failure or Denied
                localStorage.setItem(this.locationStorageKey, 'denied');
                if (this.elements.loadingSpinner) this.elements.loadingSpinner.style.display = 'none';
                if (this.elements.permissionMessage) {
                    this.elements.permissionMessage.innerHTML = `<p class="text-red-500">Location access was denied. You can re-enable it in your browser settings and refresh the page.</p>`;
                    this.elements.permissionMessage.style.display = 'block';
                }
                if (this.elements.controls) this.elements.controls.style.display = 'none';
                if (this.elements.resultsContainer) this.elements.resultsContainer.style.display = 'none';
            },
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
        );
    },

    fetchPlaces: function() {
        // ... This method is unchanged and correct ...
        if (!this.currentUserLatitude) return;
        const radius = this.elements.slider ? this.elements.slider.value : 25;
        if (this.elements.loadingSpinner) this.elements.loadingSpinner.style.display = 'block';
        if (this.elements.resultsGrid) this.elements.resultsGrid.innerHTML = '';
        showLoadingModal("Searching for nearby places...");
        const fetchUrl = `${window.nearbyListingsUrl}?latitude=${this.currentUserLatitude}&longitude=${this.currentUserLongitude}&radius=${radius}`;
        fetch(fetchUrl, { method: 'GET', headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'} })
        .then(response => { if (!response.ok) throw new Error('Network response not OK'); return response.json(); })
        .then(data => {
            hideLoadingModal();
            if (data.businesses && data.businesses.length > 0) {
                let cardsHtml = '';
                data.businesses.forEach(business => {
                    const businessName = business.name.length > 30 ? business.name.substring(0, 27) + '...' : business.name;
                    const countyName = business.county ? business.county.name : '';
                    const distanceHtml = business.distance ? `<p class="text-xs text-gray-500">Approx. ${parseFloat(business.distance).toFixed(1)} km away</p>` : '';
                                            cardsHtml += `
                            <div class="listing-card">
                                <a href="/listing/${business.slug}" class="listing-card-link-wrapper">
                                    <div class="card-image-container">
                                        <img src="${business.main_image_url || window.placeholderCardImageUrl}" alt="${business.name}">
                                    </div>
                                    <div class="card-content-area">
                                        <h3>${businessName}</h3>
                                        <p class="listing-location"><i class="fas fa-map-marker-alt"></i> ${countyName}</p>
                                        ${distanceHtml}
                                    </div>
                                </a>
                            </div>
                        `;
                });
                if (this.elements.resultsGrid) this.elements.resultsGrid.innerHTML = cardsHtml;
            } else {
                if (this.elements.resultsGrid) this.elements.resultsGrid.innerHTML = '<p class="text-center text-gray-500 col-span-full">No places found. Try increasing the radius.</p>';
            }
        })
        .catch(error => { /* ... */ })
        .finally(() => { if (this.elements.loadingSpinner) this.elements.loadingSpinner.style.display = 'none'; });
    },

    hideResults: function() {
        localStorage.setItem(this.visibilityStorageKey, 'hidden'); // <<< NEW: Remember the hidden state
        if (this.elements.controls) this.elements.controls.style.display = 'none';
        if (this.elements.resultsContainer) this.elements.resultsContainer.style.display = 'none';
        if (this.elements.showContainer) this.elements.showContainer.style.display = 'block';
    },

    showResults: function() {
        localStorage.setItem(this.visibilityStorageKey, 'shown'); // <<< NEW: Remember the shown state
        this.requestLocation(); // The requestLocation function already handles showing the right elements
    }
};

// Don't forget to initialize it at the end of your DOMContentLoaded listener.
NearbyPlacesFinder.init();

// =====================================================================================
// <<< END PLACES NEAR ME LOGIC >>>
// =====================================================================================


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