# Homepage Migration Verification Checklist

## 1. Technical Foundation ✓ or ✗

### Server-Side Rendering (SSR)
- [ ] SSR bundle builds without errors (`npm run build:ssr`)
- [ ] SSR server starts successfully
- [ ] Initial HTML includes rendered content (View Page Source)

### Data Integrity
- [ ] No console errors in browser DevTools
- [ ] No PHP errors in `storage/logs/laravel.log`
- [ ] All Inertia props are present in initial page load
- [ ] Hero slider data includes images and business info
- [ ] Discovery cards have images and correct counts
- [ ] Popular counties show correct business counts

### Asset Loading
- [ ] All CSS files load (check Network tab)
- [ ] All JS files load without 404 errors
- [ ] Font Awesome icons display correctly
- [ ] Google Fonts load properly

---

## 2. Visual Parity (vs. Original Blade Homepage)

### Layout Structure
- [ ] Header navigation matches exactly
- [ ] Main content sections in correct order
- [ ] Footer layout is identical
- [ ] Mobile menu works the same way

### Hero Slider Section
- [ ] Images load at correct size/quality
- [ ] Text overlay positioning matches
- [ ] "Discover More" button styling matches
- [ ] "Add to Bucket List" button works (auth users)
- [ ] Swiper navigation arrows appear
- [ ] Swiper pagination dots show
- [ ] Auto-play works (7 second interval)
- [ ] Fade transition effect works

### Explore Collections Section
- [ ] Section title "Explore Collections" present
- [ ] "View All" link positioned correctly
- [ ] Collection cards show images
- [ ] Collection titles display correctly
- [ ] Business counts show (e.g., "18 curated places")
- [ ] Horizontal scroll works smoothly
- [ ] Scroll arrows appear/hide correctly
- [ ] Card hover effects work (zoom on image)

### Places Near You Section
- [ ] Section title matches
- [ ] Permission request UI displays correctly
- [ ] "Enable Location" button styled correctly (#2563eb blue)
- [ ] Geolocation request triggers on button click
- [ ] Radius slider appears after permission granted
- [ ] "Update Search" button works
- [ ] Results grid displays correctly
- [ ] Business cards in results match style

### Popular Destinations Section
- [ ] Section title "Popular Destinations" present
- [ ] County cards display in grid
- [ ] County images load
- [ ] County names and listing counts show
- [ ] "Load More Destinations" button styled correctly
- [ ] Load more functionality works
- [ ] Pagination doesn't duplicate counties

### Trending Right Now Section
- [ ] Section appears with skeleton cards initially
- [ ] Lazy loads when scrolled into view
- [ ] Skeleton cards replaced with actual business cards
- [ ] Grid layout matches original (auto-fill, minmax 280px)

### New Arrivals Section
- [ ] Lazy loads correctly
- [ ] Business cards display properly
- [ ] Background color is white (#fff)

### Hidden Gems Section
- [ ] Section title and subtitle present
- [ ] Subtitle: "Unearth unique spots..."
- [ ] Lazy loads when visible
- [ ] Background color is #f8fafc

---

## 3. Functional Testing

### Navigation
- [ ] All header links work
- [ ] Active page highlighting works
- [ ] Logo click returns to home
- [ ] Mobile menu toggles correctly

### Interactive Elements
- [ ] Hero slider auto-advances
- [ ] Slider navigation buttons work
- [ ] Collection scroller scrolls smoothly
- [ ] "Load More" button loads next page
- [ ] Wishlist toggle works (if logged in)

### Data Loading
- [ ] Hero slider businesses load
- [ ] Collections load with images
- [ ] Counties load on initial render
- [ ] Lazy sections load when scrolled to
- [ ] No infinite loading spinners
- [ ] No "undefined" or "null" displayed

### Error Handling
- [ ] Empty states handled gracefully
- [ ] Failed AJAX requests don't crash page
- [ ] Missing images show placeholders

---

## 4. Performance Metrics

### Page Load
- [ ] First Contentful Paint (FCP) < 1.5s
- [ ] Largest Contentful Paint (LCP) < 2.5s
- [ ] Time to Interactive (TTI) < 3.5s
- [ ] No layout shifts (CLS < 0.1)

### Network
- [ ] Total page size reasonable (< 2MB)
- [ ] Images optimized (WebP format)
- [ ] No redundant requests
- [ ] Proper caching headers

### Bundle Size
- [ ] Main JS bundle < 500KB (gzipped)
- [ ] Main CSS bundle < 120KB (gzipped)
- [ ] No unused dependencies

---

## 5. SEO & Accessibility

### Meta Tags
- [ ] Title tag present and correct
- [ ] Meta description present
- [ ] Meta keywords present
- [ ] Canonical URL set
- [ ] Open Graph tags (if needed)

### Semantic HTML
- [ ] Proper heading hierarchy (h1 → h2 → h3)
- [ ] Meaningful alt text on images
- [ ] ARIA labels where needed

### Schema Markup
- [ ] JSON-LD schema present
- [ ] Website schema correct
- [ ] SearchAction schema correct

---

## 6. Cross-Browser Testing

- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (if on Mac)
- [ ] Edge (latest)
- [ ] Mobile Safari (iOS)
- [ ] Chrome Mobile (Android)

---

## 7. Responsive Design

### Mobile (< 768px)
- [ ] Hero slider shows mobile images
- [ ] Collections scroll horizontally
- [ ] County grid adjusts to single column
- [ ] Text sizing appropriate
- [ ] Touch targets large enough (44x44px min)

### Tablet (768px - 1024px)
- [ ] Grid layouts adjust correctly
- [ ] Navigation remains usable
- [ ] Images scale properly

### Desktop (> 1024px)
- [ ] Full layout displays correctly
- [ ] Max-width containers respected
- [ ] No excessive whitespace

---

## 8. Code Quality

### React Components
- [ ] No React warnings in console
- [ ] PropTypes/TypeScript validation (if used)
- [ ] Proper key props on lists
- [ ] No memory leaks (event listeners cleaned up)

### Performance
- [ ] No unnecessary re-renders
- [ ] Lazy loading implemented correctly
- [ ] Debouncing/throttling where needed

---

## Quick Test Commands

```bash
# Check for console errors
Open DevTools → Console tab → Look for red errors

# Check network requests
Open DevTools → Network tab → Reload page → Check for failed requests (red)

# Check initial HTML
Right-click page → View Page Source → Verify content is rendered

# Check Laravel logs
tail -f storage/logs/laravel.log

# Check build
npm run build
npm run build:ssr

# Performance test
DevTools → Lighthouse → Run audit
```

---

## Critical Issues That MUST Be Fixed

1. **Any console errors** - Zero tolerance
2. **Missing images** - All images must load or show placeholder
3. **Broken navigation** - All links must work
4. **Failed lazy loading** - Sections must load when scrolled to
5. **Layout shifts** - Content shouldn't jump during load
6. **Non-functional buttons** - All interactive elements must work

---

## Sign-Off

- [ ] All critical tests passed
- [ ] Visual comparison with original passed
- [ ] Performance metrics acceptable
- [ ] No console errors
- [ ] Ready for production deployment

**Tested By:** _________________  
**Date:** _________________  
**Notes:** _________________
