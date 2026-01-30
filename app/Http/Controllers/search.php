    public function search(Request $request)
    {
        $keyword = $request->input('keyword');
        $initialCountySlug = $request->input('county_query');
        $initialCategorySlug = $request->input('category_query');

        $businessesQuery = Business::query()->where('status', 'active')->withAllPublicRelations();

        // Initial filters from homepage search bar
        if ($keyword) { /* ... keyword logic ... */ } // Keep this

        // Apply county from homepage search (if any)
        if ($initialCountySlug) {
            $businessesQuery->whereHas('county', fn ($q) => $q->where('slug', $initialCountySlug));
        }

        // Apply category from homepage search (if any)
        if ($initialCategorySlug) {
            $businessesQuery->whereHas('categories', fn ($q) => $q->where('slug', $initialCategorySlug));
        }

        // Now apply filters from the sidebar of the results page
        // The `applyCommonFilters` should NOT re-apply keyword if it's already handled above
        // and should handle 'categories[]' array from sidebar.
        // Let's pass only the relevant request parts for sidebar filters to applyCommonFilters
        $sidebarFilterRequest = new Request($request->only(['categories', 'facilities', 'rating', 'price_max' /* add sidebar keyword if different name */]));
        $businessesQuery = $this->applyCommonFilters($sidebarFilterRequest, $businessesQuery);

        $businessesQuery = $this->applySorting($businessesQuery, $request->input('sort', 'default'));
        $businesses = $businessesQuery->paginate(15)->appends($request->query());

        // ... (rest of data for view and title generation) ...
        // Title generation should consider $initialCategorySlug and $request->input('categories')
        $searchPageTitle = 'Search Results'; /* ... build title ... */
        $currentContextObject = (object)['name' => $searchPageTitle, 'slug' => null];


         return view('listings.county', compact( // Or a dedicated listings.search-results view
            'currentCounty',
            'businesses',
            'categoriesForFilter',
            'countiesForFilter',
            'facilitiesForFilter',
            'request'
        ));
    }