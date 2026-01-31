---
description: How to deploy the custom admin panel to the live site (discoverkenya.co.ke)
---

### 🚀 Custom Admin Deployment Workflow

This guide helps you move all the premium upgrades we've built from your local environment to **discoverkenya.co.ke**.

---

### ✅ Project Progress & Completed Updates
*Last Updated: 2026-01-31*

- **Admin Dashboard Stabilization**: Fixed access issues caused by Vite manifest mismatches.
- **Dedicated Admin Logic**: Created `resources/js/admin.js` to handle admin-specific AlpineJS and UI logic independently from the site frontend.
- **Frontend Restoration**: Reverted Site Layout to the original simplified style while preserving critical performance and asset loading fixes.
- **Navigation Repairs**: 
    - Restored missing **"Journeys"** link in mobile navigation.
    - Unified navigation labels: **"Travel Blog"** and **"Contact Us"** across all viewports.
    - Fixed mobile dashboard links based on user roles (Admin vs Business Owner).
- **Branding**: Rebranded footer and site labels to **"Discover Kenya Travel Guide"**.
- **Asset Consolidation**: Optimized `vite.config.js` and layout headers to load scripts efficiently without render-blocking.

---

#### Method 1: GitHub Actions (Live)
The site is configured with **GitHub Actions**. Pushing to `main` automatically triggers a deployment to the live server.
```powershell
git add .
git commit -m "Progress: Fixed navigation, stabilized admin scripts, and unified branding"
git push origin main
```

---

#### Method 2: Rsync (Manual Override)
To sync specific manual changes:

```powershell
# Sync core files
rsync -avz --progress routes/web.php user@host:/path/to/site/routes/
rsync -avz --progress app/Http/Controllers/Admin/ user@host:/path/to/site/app/Http/Controllers/Admin/
rsync -avz --progress resources/views/admin/ user@host:/path/to/site/resources/views/admin/
rsync -avz --progress resources/views/layouts/site.blade.php user@host:/path/to/site/resources/views/layouts/
rsync -avz --progress resources/js/admin.js user@host:/path/to/site/resources/js/
rsync -avz --progress resources/js/app.js user@host:/path/to/site/resources/js/
rsync -avz --progress resources/css/admin.css user@host:/path/to/site/resources/css/
rsync -avz --progress vite.config.js user@host:/path/to/site/
```

---

#### ⚠️ Critical Steps After Moving Files:
1. **Clear Cache:** Run `php artisan optimize:clear` on the server.
2. **Rebuild Assets:** Since we added/modified JS/CSS, you **MUST** run `npm run build` on the live server if GitHub Actions didn't do it automatically.
3. **Permissions:** Ensure `storage` and `bootstrap/cache` remain writable.

