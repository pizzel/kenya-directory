<script>
(function() {
    // 1. Measure Time to First Byte (Server Speed)
    // Navigation Timing API Level 2
    const navEntry = performance.getEntriesByType("navigation")[0];
    if (navEntry) {
        console.group("🚀 Performance Diagnostics");
        console.log(`📡 TTFB (Server Response): ${Math.round(navEntry.responseStart - navEntry.requestStart)}ms`);
        console.log(`📦 Content Download: ${Math.round(navEntry.responseEnd - navEntry.responseStart)}ms`);
        console.log(`🏗️ DOM Processing: ${Math.round(navEntry.domComplete - navEntry.responseEnd)}ms`);
    }

    // 2. Measure LCP (Largest Contentful Paint)
    // This observer waits until the page is fully visually loaded to report the final LCP.
    new PerformanceObserver((entryList) => {
        for (const entry of entryList.getEntries()) {
            console.log(`🎨 LCP Candidate: ${Math.round(entry.startTime)}ms`);
            console.log("   Element:", entry.element);
            
            // Check if it's an image and if it had a load delay
            if (entry.url) {
                console.log(`   URL: ${entry.url}`);
                // Determine if it was the load delay or render delay
                if (entry.loadTime) {
                   console.log(`   ⬇️ Load Delay (Waiting for download): ${Math.round(entry.loadTime - entry.startTime)}ms`); 
                }
            }
            
            // Visual Indicator for YOU (The Admin)
            if (window.location.search.includes('debug=true')) {
                const badge = document.createElement('div');
                badge.style.position = 'fixed';
                badge.style.bottom = '10px';
                badge.style.right = '10px';
                badge.style.background = entry.startTime < 2500 ? 'green' : 'red';
                badge.style.color = 'white';
                badge.style.padding = '10px';
                badge.style.zIndex = 9999;
                badge.style.borderRadius = '5px';
                badge.style.fontFamily = 'monospace';
                badge.innerText = `LCP: ${Math.round(entry.startTime)}ms`;
                document.body.appendChild(badge);
            }
        }
    }).observe({type: 'largest-contentful-paint', buffered: true});

    console.groupEnd();
})();
</script>
