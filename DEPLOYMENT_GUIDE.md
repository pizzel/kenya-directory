# 🚀 DISCOVER KENYA - MASTER DEPLOYMENT & OPTIMIZATION GUIDE

**Last Updated:** 2026-01-31
**Objective:** The Single Source of Truth for deploying, optimizing, and maintaining **discoverkenya.co.ke**.

---

## 🤖 PART 1: DEPLOYMENT WORKFLOWS

### **Method 1: Automatic Deployment (GitHub → FTP)**
The site is configured with **GitHub Actions**. Pushing to the `main` branch automatically triggers a deployment.

*   **Rule:** This is a strictly FTP-based workflow (`SamKirkland/FTP-Deploy-Action`).
*   **Path:** Server directory is hardcoded to `/Discover_Kenya/`.
*   **Process:**
    1.  Make changes locally.
    2.  `git add . && git commit -m "Progress: Description of changes"`
    3.  `git push origin main`
    4.  GitHub builds CSS/JS assets and uploads via FTP (takes ~2-5 mins).

### **Method 2: Manual Sync (Rsync Override)**
For quick manual updates of specific files via SSH/Rsync:
```powershell
# Sync core files
rsync -avz --progress routes/web.php user@host:/path/to/site/routes/
rsync -avz --progress app/Http/Controllers/Admin/ user@host:/path/to/site/app/Http/Controllers/Admin/
rsync -avz --progress resources/views/layouts/site.blade.php user@host:/path/to/site/resources/views/layouts/
rsync -avz --progress resources/js/admin.js user@host:/path/to/site/resources/js/
rsync -avz --progress resources/js/app.js user@host:/path/to/site/resources/js/
rsync -avz --progress vite.config.js user@host:/path/to/site/
```

### **Method 3: Manual Fallback (FileZilla)**
1.  Run `npm run build` locally.
2.  Upload the contents of `public/build` and modified PHP/Blade files to the server.

---

## 🧠 PART 2: THE "BRAIN" - SEMANTIC SEO & CONTENT

**Objective:** Align site structure with Google's Semantic Model to rank as a Travel Authority.

### **1. AI-Driven SEO Services**
*   **`ReviewAnalyzerService.php`**: Scans descriptions for entity keywords (Big Five, Luxury, etc.) to assign categories.
*   **`Sentiment Architect`**: Injects real Google Review sentiment into "Expert Experience Narratives".
*   **`SemanticSEOService.php`**: Generates high-quality JSON-LD Schema.

### **2. Manual Sync Protocol (Scorched Earth)**
After major pushes, visit the secret URL to trigger AI regeneration and cache purging:
👉 `https://discoverkenya.co.ke/deploy-seo-v2026?token=pizzel-seo-magic`

---

## ⚡ PART 3: PERFORMANCE ARCHITECTURE

**Objective:** Maintain Mobile LCP < 2.5s and PageSpeed > 90.

### **1. Image Optimization Strategy**
| Conversion | Format | Quality | Rationale |
| :--- | :--- | :--- | :--- |
| **Thumbnail** | **JPG** | 90 | **Sharpness priority**. JPG preserves clarity for small admin icons. |
| **Card** | **WebP** | 85 | **Speed priority**. 50-70% size reduction for listing grids. |
| **Hero** | **WebP** | 78 | **Balanced**. High impact visuals with optimized byte size. |

### **2. Frontend Performance Hacks**
*   **Loop Fix**: Swiper `loop: false` ensures the LCP image preloaded in HTML is the *actual* first slide shown.
*   **Async Fonts**: Critical fonts preload; non-critical weights load via `media="print"` to avoid render-blocking.
*   **GA Deferral**: Google Analytics loads only after the `window.load` event to prioritize content painting.
*   **AJAX Similarity**: Similar listings load via AJAX with skeleton loaders after the user scrolls down.

---

## 📝 PART 4: CRITICAL DECISION LOG

| Date | Decision | Rationale |
| :--- | :--- | :--- |
| 2026-01-16 | **Keep JPG Thumbnails** | WebP thumbnails were too pixelated at 4KB; reverted to 8KB high-quality JPG for professional UI. |
| 2026-01-17 | **Maintain 80vh Hero** | Reverted 70vh test. Aesthetics & brand impact outweigh the 200ms LCP gain. |
| 2026-01-31 | **Dedicated admin.js** | Created `admin.js` to decouple admin Alpine logic from frontend React/Site assets to fix Vite manifest errors. |

---

## 📈 PART 5: PROJECT PROGRESS LOG

### **Jan 2026: Mobile & Speed Foundation**
- ✅ Implemented WebP conversion for listing cards (-60% page weight).
- ✅ Moved Similarity Algorithm to AJAX to fix TTFB bottlenecks.
- ✅ Optimized Google Font and Analytics loading (async/defer).
- ✅ Fixed Swiper Loop mismatch (3.4s LCP improvement).

### **Jan 31, 2026: UI Restoration & Stabilization**
- ✅ **Navigation Fix**: Restored the missing **"Journeys"** link in the mobile drawer.
- ✅ **Branding Consistency**: Unified labels to **"Travel Blog"** and **"Contact Us"** across all viewports to ensure professional branding.
- ✅ **Admin Stabilization**: Documented and resolved the **"Vite manifest missing app.js"** error by successfully decoupling Admin JS logic from the site frontend into a dedicated `admin.js`.
- ✅ **Functionality Restore**: Fixed non-responsive dropdowns and mobile menu toggles by correctly ordering AlpineJS and Vite script loading in the layout headers.
- ✅ **Mobile UI**: Fixed dashboard buttons to correctly routing users between Admin and Business Owner panels.

---

## 🔧 PART 6: TROUBLESHOOTING & MAINTENANCE

### **Issue: Vite Manifest Error (File not found)**
**Fix:** Run `npm run build` locally and push to GitHub. Ensure the layout file (`site.blade.php` or `admin/layouts/app.blade.php`) points to the correct entry point (`resources/js/app.js` or `resources/js/admin.js`).

### **Issue: LCP remains > 2.5s**
**Checklist:**
1.  Check TTFB in browser console (should be < 500ms).
2.  Verify the hero image is served as `.webp`.
3.  Ensure `?debug=true` is used to see the Performance Logger badge.

### **Issue: XAMPP MySQL Failed to Start (Unexpected Shutdown)**
**Fix:** Rename corrupted log files by running the following in PowerShell:
```powershell
Stop-Process -Name "mysqld" -Force -ErrorAction SilentlyContinue
cd C:\xampp\mysql\data
Rename-Item "aria_log.00000001" "aria_log.00000001.bak" -ErrorAction SilentlyContinue
Rename-Item "aria_log_control" "aria_log_control.bak" -ErrorAction SilentlyContinue
Remove-Item "mysql.pid" -ErrorAction SilentlyContinue
```
Or run the dedicated repair scripts: `.\fix-mysql.ps1` or `.\nuclear-fix-mysql.ps1`.

### **Common Commands (Remote fallback):**
```bash
php artisan optimize:clear
php artisan media-library:regenerate --force
```

---
*Maintained by Antigravity AI - 2026*
