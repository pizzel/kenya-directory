---
description: How to deploy the custom admin panel to the live site (discoverkenya.co.ke)
---

### 🚀 Custom Admin Deployment Workflow

This guide helps you move all the premium upgrades we've built from your local environment to **discoverkenya.co.ke**.

#### Method 1: Git (Recommended)
If you are using Git, follow these steps:

1. **Commit your changes locally:**
   ```powershell
   git add .
   git commit -m "feat: implement high-end custom admin panel with media management"
   ```
2. **Push to your repository (e.g., GitHub):**
   ```powershell
   git push origin main
   ```
3. **Deploy on Live Site (via SSH):**
   Connect to your server and pull the changes:
   ```bash
   ssh user@discoverkenya.co.ke
   cd /path/to/public_html
   git pull origin main
   php artisan migrate
   npm install && npm run build
   ```

---

#### Method 2: Rsync (Fastest for non-git)
If you want to sync specific files directly via SSH:

Run this command from your local terminal (replace `user@host` with your actual SSH details):

```powershell
# Sync core files
rsync -avz --progress routes/web.php user@host:/path/to/site/routes/
rsync -avz --progress app/Http/Controllers/Admin/ user@host:/path/to/site/app/Http/Controllers/Admin/
rsync -avz --progress app/Models/Business.php user@host:/path/to/site/app/Models/
rsync -avz --progress resources/views/admin/ user@host:/path/to/site/resources/views/admin/
rsync -avz --progress resources/views/components/admin/ user@host:/path/to/site/resources/views/components/admin/
rsync -avz --progress resources/css/admin.css user@host:/path/to/site/resources/css/
rsync -avz --progress vite.config.js user@host:/path/to/site/
rsync -avz --progress public/robots.txt user@host:/path/to/site/public/
```

---

#### ⚠️ Critical Steps After Moving Files:
1. **Clear Cache:** Run `php artisan optimize:clear` on the server.
2. **Rebuild Assets:** Since we added `admin.css`, you **MUST** run `npm run build` on the live server so Vite generates the production bundles.
3. **Permissions:** Ensure `storage` and `bootstrap/cache` remain writable.
