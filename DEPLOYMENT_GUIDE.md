# 🚀 FTP-ONLY DEPLOYMENT & SEO MASTER GUIDE

**Last Updated:** 2026-01-31
**Objective:** The Single Source of Truth for deploying, optimizing, and ranking discoverkenya.co.ke using an FTP-only workflow.

---

## 🤖 PART 1: AUTOMATIC DEPLOYMENT (GitHub → FTP)

**CRITICAL NOTE:** This workflow is strictly FTP-based. We do NOT use SSH or remote terminal commands because the hosting environment does not support them. All file transfers are handled by `SamKirkland/FTP-Deploy-Action`.

After this one-time setup, deployments work like this:
```
You make changes locally
         ↓
git add . && git commit -m "your changes"
         ↓
git push origin main
         ↓
🤖 GitHub builds your CSS/JS assets
         ↓
📤 GitHub uploads everything via FTP
         ↓
✅ Site is live in ~2-5 minutes!
```

### ✅ Workflow Rules
1. **No SSH Actions:** The `deploy.yml` must NOT contain `appleboy/ssh-action` or similar terminal-dependent steps.
2. **Path Mapping:** The `server-dir` is hardcoded to `/Discover_Kenya/` as per your hosting configuration.
3. **Exclusions:** We exclude `node_modules`, `.env`, and `storage` to prevent overwriting server-specific data and slowing down transfers.

---

## 🧠 PART 2: SEMANTIC SEO STRATEGY (Content & Ranking)

**Objective:** Align site structure with Google's Semantic Model (Entities & Intent).

### **1. The "Semantic Brain"**
- We now use a "Semantic Brain" (`SemanticSEOService.php`) to automatically generate Schema.org data and "Discover Kenya Verdicts."
- This moves the site from being a "Business Directory" to a "Travel Authority."

### **2. How to Trigger Database Updates (Browser Sync)**
Since we cannot run `php artisan` commands on the server via terminal, we use a secret **Browser Trigger** to update titles and descriptions.
1. Deploy your code via GitHub (or FTP).
2. Visit this secret URL in your browser:
   `https://discoverkenya.co.ke/deploy-seo-v2026?token=pizzel-seo-magic`
3. You will see a success message. This script also attempts to clear necessary application caches where possible.

---

## ⚡ PART 3: PERFORMANCE OPTIMIZATION (LCP & Speed)

**Objective:** Maintain Mobile LCP < 2.5s and PageSpeed > 90.

### **Key Optimizations Implemented:**
1. **WebP Conversion:** Images are now 70% smaller without losing quality.
2. **First-Slide Priority:** The hero slider no longer "loops," ensuring the first image is preloaded instantly.
3. **Performance Logger:** Visit `/?debug=true` on your live site to see real-time speed metrics in your browser console.

---

## 🔧 PART 4: MANUAL DEPLOYMENT (Fallback)

**Use this ONLY if GitHub Actions is down.**

1. **Build Locally:** Run `npm run build` on your computer.
2. **Upload Folder:** Upload the contents of your local folder to `/Discover_Kenya/` via FileZilla.
3. **Important:** Ensure you upload the `public/build` folder to update CSS/JS.

---

## ⚠️ TROUBLESHOOTING

### **Issue: New SEO Titles Not Showing**
**Solution:** Visit the trigger URL:
`https://discoverkenya.co.ke/deploy-seo-v2026?token=pizzel-seo-magic`

### **Issue: 500 Error or Page Not Loading**
**Solution:** Check the `storage/logs/laravel.log` via FTP. If the error is Blade-related, ensure `resources/views/listings/show.blade.php` was uploaded completely.

---
*Maintained by Antigravity AI - 2026*
