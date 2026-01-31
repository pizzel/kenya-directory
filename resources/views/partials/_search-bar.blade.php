@php
    // Detect if we are on a Category, Tag, or Facility page
    $currentCategorySlug = isset($currentCategory) ? $currentCategory->slug : null;
    $currentTagSlug = isset($currentTag) ? $currentTag->slug : null;
    $currentFacilitySlug = isset($currentFacility) ? $currentFacility->slug : null;
    
    // NOTE: Data is now fetched via AJAX to improve performance
@endphp

<section class="sticky-search-section">
    <div class="container">
        <form class="search-form" action="{{ route('listings.search') }}" method="GET" autocomplete="off">
            
            {{-- 1. THE "WHAT" FIELD --}}
            <div class="form-group searchable-dropdown-group what-group" style="flex-grow: 2;">
                <label for="general-search-input">What?</label>
                <input type="text" id="general-search-input" name="query" 
                       value="{{ request('query') ?? ($currentCategory->name ?? ($currentTag->name ?? ($currentFacility->name ?? ''))) }}" 
                       placeholder="Ex: Safari, Java House, Camping..." autocomplete="off">
                
                {{-- Empty container populated by JS --}}
                <div class="dropdown-list general-dropdown-list" id="what-dropdown"></div>
            </div>

            {{-- 2. THE "WHERE" FIELD (County Filter) --}}
            <div class="form-group searchable-dropdown-group where-group" style="flex-grow: 1;">
                <label for="county-search-input">Where?</label>
                
                @php
                    $displayLocation = request('county_search_input');
                    if (!$displayLocation && isset($currentCounty) && isset($currentCounty->slug)) {
                        $isMockTitle = in_array($currentCounty->slug, [null, 'search', 'collection']);
                        $displayLocation = !$isMockTitle ? $currentCounty->name : '';
                    }
                @endphp

                <input type="text" id="county-search-input" name="county_search_input"
                       placeholder="County or Town..." autocomplete="off"
                       value="{{ $displayLocation }}"> 

                <input type="hidden" name="county_query" id="hidden_county_query" value="{{ request('county_query') ?? (isset($currentCounty) && isset($currentCounty->slug) && $currentCounty->slug !== 'search' ? $currentCounty->slug : '') }}">
                
                {{-- Empty container populated by JS --}}
                <div class="dropdown-list county-dropdown-list" id="where-dropdown"></div>
            </div>

            {{-- 3. HIDDEN CONTEXT --}}
            @if($currentCategorySlug) <input type="hidden" name="category_query" value="{{ $currentCategorySlug }}"> @endif
            @if($currentTagSlug) <input type="hidden" name="tag_query" value="{{ $currentTagSlug }}"> @endif
            @if($currentFacilitySlug) <input type="hidden" name="facility_query" value="{{ $currentFacilitySlug }}"> @endif

            <button type="submit" class="search-btn">
                <i class="fas fa-search"></i> <span class="btn-text">Search</span>
            </button>
        </form>
    </div>
</section>

{{-- STYLES --}}
<style>
    .searchable-dropdown-group { position: relative; }
    .dropdown-list {
        display: none; position: absolute; top: 100%; left: 0; right: 0;
        background: white; border: 1px solid #ddd; border-radius: 0 0 12px 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1); z-index: 1000;
        max-height: 300px; overflow-y: auto; margin-top: 5px;
    }
    .dropdown-header {
        padding: 8px 15px; font-size: 0.75rem; color: #475569; font-weight: 700;
        background: #f1f5f9; border-bottom: 1px solid #e2e8f0;
    }
    .suggestion-item {
        padding: 10px 15px; cursor: pointer; display: flex; align-items: center;
        color: #333; transition: background 0.2s;
    }
    .suggestion-item:hover { background-color: #f0f4ff; color: #000; }
    .suggestion-item i { margin-right: 10px; color: #666; width: 16px; text-align: center; }
</style>

{{-- JAVASCRIPT LOGIC --}}
<script>
    // Initialize empty, fetched on demand
    window.SearchData = null;
    let isFetchingSearchData = false;
    let fetchPromise = null;

    // Utility: Debounce to prevent sluggish UI
    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            const context = this;
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), wait);
        };
    }

    async function ensureSearchData() {
        if (window.SearchData) return window.SearchData;
        if (isFetchingSearchData) return fetchPromise;

        isFetchingSearchData = true;
        
        fetchPromise = fetch("{{ route('ajax.search-suggestions') }}")
            .then(res => {
                if (!res.ok) throw new Error('Network response icon');
                return res.json();
            })
            .then(data => {
                window.SearchData = data;
                isFetchingSearchData = false;
                return data;
            })
            .catch(e => {
                console.error("Failed to load search suggestions", e);
                isFetchingSearchData = false;
                fetchPromise = null; 
            });

        return fetchPromise;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const whatInput = document.getElementById('general-search-input');
        const whatDropdown = document.getElementById('what-dropdown');
        const whereInput = document.getElementById('county-search-input');
        const whereDropdown = document.getElementById('where-dropdown');
        const whereHidden = document.getElementById('hidden_county_query');

        // Preload on interactions (hover removed, click/focus only now)
        // const preloadData = () => { ensureSearchData(); }; // Removed hover preload logic

        // --- 1. RENDER WHAT DROPDOWN ---
        async function renderWhatSuggestions(filter) {
            // REMOVED: if (filter.length < 1) ... to allow default view

            // Ensure data is loaded
            if (!window.SearchData) {
                whatDropdown.innerHTML = '<div class="dropdown-header">Loading suggestions...</div>';
                whatDropdown.style.display = 'block';
                await ensureSearchData();
            }
            // If still no data (error), return
            if (!window.SearchData) return;

            let html = '';
            const addHeader = (title) => html += `<div class="dropdown-header">${title}</div>`;
            const addItem = (type, value, display, icon, url = null) => {
                const dataUrl = url ? `data-url="${url}"` : '';
                html += `<div class="suggestion-item" data-type="${type}" data-value="${value}" ${dataUrl}>
                           <i class="${icon}"></i> ${display}
                         </div>`;
            };

            // Limit results for performance
            let hasResults = false;
            // If filter is empty, show more initial Results
            const isDefaultView = filter.length === 0;

            // Businesses (NEW: Show business name suggestions)
            if(window.SearchData.businesses) {
                const matches = [];
                const limit = isDefaultView ? 8 : 5; 
                for (const b of window.SearchData.businesses) {
                    if (b.n.toLowerCase().includes(filter)) matches.push(b);
                    if (matches.length >= limit) break;
                }

                if (matches.length > 0) {
                    addHeader('Businesses');
                    matches.forEach(b => addItem('url', b.n, b.n, 'fas fa-store text-green-500', '/listings/' + b.s));
                    hasResults = true;
                }
            }

            // Activities
            if(window.SearchData.activities) {
                const matches = [];
                const limit = isDefaultView ? 10 : 5; 
                for (const a of window.SearchData.activities) {
                    if (a.n.toLowerCase().includes(filter)) matches.push(a);
                    if (matches.length >= limit) break;
                }

                if (matches.length > 0) {
                    addHeader('Activities');
                    matches.forEach(a => addItem('activity', a.n, a.n, a.i));
                    hasResults = true;
                }
            }

            // Collections
            if(window.SearchData.collections) {
                const matches = []; 
                const limit = isDefaultView ? 5 : 4;
                for (const c of window.SearchData.collections) {
                    if (c.t.toLowerCase().includes(filter)) matches.push(c);
                    if (matches.length >= limit) break;
                }

                if (matches.length > 0) {
                    addHeader('Curated Guides');
                    matches.forEach(c => addItem('url', c.t, c.t, 'fas fa-sparkles text-yellow-500', '/collections/' + c.s));
                    hasResults = true;
                }
            }

             // Posts
             if(window.SearchData.posts) {
                 const matches = [];
                 const limit = isDefaultView ? 5 : 4;
                 for (const p of window.SearchData.posts) {
                     if (p.t.toLowerCase().includes(filter)) matches.push(p);
                     if (matches.length >= limit) break;
                 }
                 
                 if (matches.length > 0) {
                    addHeader('Latest Stories');
                    matches.forEach(p => addItem('url', p.t, p.t, 'fas fa-newspaper text-blue-400', '/blog/' + p.s));
                    hasResults = true;
                }
            }

            if (!hasResults) {
                whatDropdown.style.display = 'none';
            } else {
                whatDropdown.innerHTML = html;
                whatDropdown.style.display = 'block';
            }
        }

        // --- 2. RENDER WHERE DROPDOWN ---
        async function renderWhereSuggestions(filter) {
            // REMOVED: if (filter.length < 1) ... to allow default view

            if (!window.SearchData) {
                 whereDropdown.innerHTML = '<div class="dropdown-header">Loading places...</div>';
                 whereDropdown.style.display = 'block';
                 await ensureSearchData();
            }
            if (!window.SearchData || !window.SearchData.counties) return;

            let html = '';
            // Counties optimization
            const matches = [];
            const isDefaultView = filter.length === 0;
            const limit = isDefaultView ? 15 : 8; // Show more when empty

            for (const c of window.SearchData.counties) {
                if (c.n.toLowerCase().includes(filter)) matches.push(c);
                if (matches.length >= limit) break; // Limit suggestions
            }
            
            if (matches.length > 0) {
                matches.forEach(c => {
                    html += `<div class="suggestion-item" data-value="${c.s}" data-name="${c.n}">
                                <i class="fas fa-map-marker-alt"></i> ${c.n}
                             </div>`;
                });
                whereDropdown.innerHTML = html;
                whereDropdown.style.display = 'block';
            } else {
                whereDropdown.style.display = 'none';
            }
        }

        // DEBOUNCED HANDLERS (Wait 300ms after typing stops)
        const debouncedWhat = debounce((val) => renderWhatSuggestions(val), 300);
        const debouncedWhere = debounce((val) => renderWhereSuggestions(val), 300);

        // --- BINDING EVENTS ---

        // 1. WHAT INPUT
        if(whatInput) {
            // instant on click/focus
            const showWhat = () => {
                ensureSearchData().then(() => {
                    renderWhatSuggestions(whatInput.value.toLowerCase());
                });
            };
            whatInput.addEventListener('focus', showWhat);
            whatInput.addEventListener('click', showWhat); // In case already focused
            
            // debounced on typing
            whatInput.addEventListener('input', () => debouncedWhat(whatInput.value.toLowerCase()));
        }

        // 2. WHERE INPUT
        if(whereInput) {
            const showWhere = () => {
                ensureSearchData().then(() => {
                    renderWhereSuggestions(whereInput.value.toLowerCase());
                });
            };
            whereInput.addEventListener('focus', showWhere);
            whereInput.addEventListener('click', showWhere);

            whereInput.addEventListener('input', function() {
                whereHidden.value = '';
                debouncedWhere(this.value.toLowerCase());
            });
        }

        if(whatDropdown) {
            whatDropdown.addEventListener('click', function(e) {
                const item = e.target.closest('.suggestion-item');
                if (item) {
                    const type = item.getAttribute('data-type');
                    const value = item.getAttribute('data-value');
                    const url = item.getAttribute('data-url');
                    
                    if (type === 'url' && url) {
                        window.location.href = url;
                    } else {
                        whatInput.value = value;
                        whatDropdown.style.display = 'none';
                    }
                }
            });
        }

        if(whereDropdown) {
            whereDropdown.addEventListener('click', function(e) {
                const item = e.target.closest('.suggestion-item');
                if (item) {
                    whereInput.value = item.getAttribute('data-name');
                    whereHidden.value = item.getAttribute('data-value');
                    whereDropdown.style.display = 'none';
                }
            });
        }

        // --- 3. CLOSE ON CLICK OUTSIDE ---
        document.addEventListener('click', function(e) {
            if (whatInput && !whatInput.contains(e.target) && !whatDropdown.contains(e.target)) {
                whatDropdown.style.display = 'none';
            }
            if (whereInput && !whereInput.contains(e.target) && !whereDropdown.contains(e.target)) {
                whereDropdown.style.display = 'none';
            }
        });
    });
</script>