# 🎯 QUICK DEPLOYMENT REFERENCE

## Files to Upload (3)
```
✅ app/Models/Business.php
✅ resources/views/home.blade.php
✅ resources/views/partials/_performance-logger.blade.php
```

## Commands to Run (SSH)
```bash
# 1. Regenerate all images (5-15 min)
php artisan media-library:regenerate --force

# 2. Clear caches
php artisan view:clear
php artisan cache:clear
php artisan config:clear
```

## Verify Success
```
1. Visit: https://discoverkenya.co.ke/?debug=true
2. Press F12 → Check console for "🚀 Performance Diagnostics"
3. Look for green LCP badge (bottom-right, showing <2500ms)
4. Right-click card image → "Copy image address" → Should end in .webp
```

## Expected Results
```
Mobile Score:  67 → 75-85  (+10-15 points)
Desktop Score: 84 → 90-95  (+6-11 points)
Mobile LCP:    5.2s → 2.0-2.5s  (60% faster)
Page Weight:   8.3MB → 6-7MB  (20-25% lighter)
```

## Troubleshooting
```
❌ Performance logger not showing?
   → php artisan view:clear
   → Add ?debug=true to URL

❌ Images still JPG?
   → Re-run: php artisan media-library:regenerate --force
   → Check: ls public/storage/businesses/*/conversions/*card*.webp

❌ LCP still slow?
   → Check TTFB in console (should be <500ms)
   → Verify hero image is WebP (~150-300 KB)
   → Clear browser cache (Ctrl+Shift+Del)
```

## Rollback (If Needed)
```bash
# Revert files via FTP, then:
php artisan media-library:regenerate --force
php artisan cache:clear
```

---
📖 **Full Guide:** See DEPLOYMENT_GUIDE.md  
📊 **Summary:** See OPTIMIZATION_SUMMARY.md
