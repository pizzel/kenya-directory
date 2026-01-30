# React Homepage Migration - Production Deployment Guide

## 📋 Pre-Deployment Checklist

- [ ] All local tests passed (no console errors)
- [ ] Images loading correctly
- [ ] "Load More" functionality working
- [ ] All sections lazy-loading properly
- [ ] Database backup taken on live server
- [ ] `.env` file backed up

---

## 📦 Files to Upload

### 1. Core Application Files

**Backend (PHP/Laravel):**
```
app/Http/Controllers/HomeController.php
app/Http/Middleware/HandleInertiaRequests.php
routes/web.php
```

**Frontend (React/Inertia):**
```
resources/js/app.jsx
resources/js/ssr.jsx
resources/js/Pages/Home.jsx
resources/js/Layouts/Layout.jsx
resources/js/Components/BusinessCard.jsx
resources/js/Components/CountyCard.jsx
resources/js/Components/DiscoveryCard.jsx
resources/js/Components/DiscoveryScroller.jsx
resources/js/Components/HeroSlider.jsx
resources/js/Components/ListingSection.jsx
resources/js/Components/NearbySection.jsx
resources/js/Components/PopularDestinations.jsx
resources/js/Components/SkeletonCard.jsx
```

**Configuration:**
```
vite.config.js
package.json
config/inertia.php
```

**Views (Root Template):**
```
resources/views/app.blade.php
```

### 2. Dependencies (package.json changes)

Your `package.json` now includes:
- `@inertiajs/react`
- `react`
- `react-dom`
- Swiper.js
- Other dependencies listed in your package.json

---

## 🚀 Deployment Steps

### Option A: Manual Upload via FTP/SFTP

1. **Upload Modified Files**
   ```
   Upload all files listed above to their respective locations on live server
   ```

2. **SSH into Live Server**
   ```bash
   ssh your-username@discoverkenya.co.ke
   cd /path/to/your/website
   ```

3. **Install Node Dependencies**
   ```bash
   npm install
   ```

4. **Build Production Assets**
   ```bash
   npm run build
   npm run build:ssr
   ```

5. **Clear Laravel Caches**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```

6. **Restart Queue Workers (if using)**
   ```bash
   php artisan queue:restart
   ```

7. **Verify Permissions**
   ```bash
   chmod -R 755 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

### Option B: Git Deployment (Recommended)

1. **Commit Changes Locally**
   ```bash
   git add .
   git commit -m "Migrate homepage to React with Inertia.js"
   git push origin main
   ```

2. **SSH into Live Server**
   ```bash
   ssh your-username@discoverkenya.co.ke
   cd /path/to/your/website
   ```

3. **Pull Changes**
   ```bash
   git pull origin main
   ```

4. **Install Dependencies & Build**
   ```bash
   composer install --no-dev --optimize-autoloader
   npm install --production
   npm run build
   npm run build:ssr
   ```

5. **Clear Caches**
   ```bash
   php artisan cache:clear
   php artisan config:cache
   php artisan route:cache
   php artisan view:clear
   ```

6. **Set Permissions**
   ```bash
   chmod -R 755 storage bootstrap/cache
   ```

---

## 🔍 Post-Deployment Verification

### 1. Check Homepage
- [ ] Visit https://discoverkenya.co.ke/
- [ ] Verify home page loads without errors
- [ ] Open browser DevTools (F12) → Console tab
- [ ] Confirm no JavaScript errors (should be clean)

### 2. Test Sections
- [ ] Hero slider displays and auto-plays
- [ ] Explore Collections shows images
- [ ] Places Near You permission request works
- [ ] Popular Destinations displays
- [ ] "Load More Destinations" works without errors
- [ ] Trending section lazy-loads
- [ ] New Arrivals section lazy-loads
- [ ] Hidden Gems section lazy-loads

### 3. Check Network
- [ ] Open DevTools → Network tab
- [ ] Reload page
- [ ] Verify all assets load (no 404s)
- [ ] Check that `app-*.js` and `Home-*.js` load

### 4. Performance Check
- [ ] Run Lighthouse audit (DevTools → Lighthouse)
- [ ] Verify LCP < 2.5s
- [ ] Check for layout shifts

---

## 🎯 Production Environment Variables

Ensure these are set in your live `.env` file:

```env
# Inertia SSR (if using SSR in production)
INERTIA_SSR_ENABLED=true
INERTIA_SSR_URL=http://127.0.0.1:13714

# Cache Driver (recommended for production)
CACHE_DRIVER=redis  # or memcached

# Session Driver
SESSION_DRIVER=redis  # or database

# App Environment
APP_ENV=production
APP_DEBUG=false
```

**Note:** If you want SSR in production, you'll need to ensure Node.js is installed and the SSR server can run. For simplicity, you can disable SSR in production by setting `INERTIA_SSR_ENABLED=false`.

---

## 🔄 Rollback Plan (If Needed)

If something goes wrong, you can quickly revert:

### Quick Rollback (Restore Blade Homepage)

1. **Edit HomeController.php**
   
   Change line ~184 from:
   ```php
   return Inertia::render('Home', [
       'heroSliderBusinesses' => $heroSliderBusinesses,
       'discoveryCards'       => $discoveryCards,
       'popularCounties'      => $popularCounties,
       'seoKeywords'          => $seoKeywords,
   ]);
   ```
   
   To:
   ```php
   return view('home', compact(
       'counties', 
       'searchableCategories', 
       'heroSliderBusinesses', 
       'discoveryCards', 
       'popularCounties', 
       'lcpImageUrl', 
       'lcpImageUrlMobile', 
       'firstHeroBusiness',
       'seoKeywords'
   ));
   ```

2. **Clear Cache**
   ```bash
   php artisan cache:clear
   php artisan route:clear
   ```

3. **Verify Blade Homepage Restored**
   - Visit https://discoverkenya.co.ke/
   - Old homepage should be back

---

## 📊 Monitoring After Deployment

### Day 1 Checks
- [ ] Monitor error logs: `tail -f storage/logs/laravel.log`
- [ ] Check analytics for bounce rate
- [ ] Verify Google Search Console (no crawl errors)
- [ ] Test on mobile devices
- [ ] Test on different browsers

### Week 1 Checks
- [ ] Monitor page load times
- [ ] Check conversion rates
- [ ] Review user feedback
- [ ] Verify SEO rankings maintained

---

## 🛠️ Troubleshooting Common Issues

### Issue: White Screen
**Solution:**
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Rebuild assets
npm run build
```

### Issue: Missing Images
**Solution:**
```bash
# Clear cache to regenerate image URLs
php artisan cache:clear

# Verify image disk permissions
chmod -R 755 storage/app/public
```

### Issue: 500 Server Error
**Solution:**
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Enable debug temporarily
# Edit .env: APP_DEBUG=true
# Then check page for detailed error
# REMEMBER: Set APP_DEBUG=false afterwards!
```

### Issue: SSR Not Working
**Solution:**
```bash
# Disable SSR in production if causing issues
# Edit .env: INERTIA_SSR_ENABLED=false

# Clear config cache
php artisan config:clear
```

---

## 📝 Deployment Checklist Summary

**Before Deployment:**
- [ ] Backup database
- [ ] Backup files
- [ ] Test locally one final time

**During Deployment:**
- [ ] Upload/pull all changed files
- [ ] Run `npm install`
- [ ] Run `npm run build`
- [ ] Run `npm run build:ssr`
- [ ] Clear Laravel caches
- [ ] Set correct permissions

**After Deployment:**
- [ ] Test homepage loads
- [ ] Check console for errors
- [ ] Test all interactive elements
- [ ] Verify mobile responsiveness
- [ ] Monitor logs for 24 hours

---

## 🎉 Success Criteria

Your deployment is successful when:
✅ Homepage loads without console errors  
✅ All images display correctly  
✅ All sections lazy-load properly  
✅ "Load More" functionality works  
✅ Mobile version displays correctly  
✅ Page load time < 3 seconds  
✅ No error spikes in logs  

---

## 📞 Support Contacts

- **Laravel Logs:** `storage/logs/laravel.log`
- **Browser Console:** Press F12 → Console tab
- **Network Issues:** F12 → Network tab

**Deployment Date:** _________________  
**Deployed By:** _________________  
**Status:** _________________
