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

## 🧠 PART 2: SEMANTIC SEO STRATEGY (The Brain)

**Objective:** Align site structure with Google's Semantic Model (Entities & Intent) to rank as a Travel Authority.

### **1. The "Sentiment Architect" System**
We have implemented a dual-service system to automate your SEO:
- **`ReviewAnalyzerService.php` (The Brain):** Scans listing descriptions and reviews for expert keywords (*Big Five, Authentic Cuisine, etc.*) and automatically assigns the correct categories.
- **`Sentiment Architect` (The Content):** Integrated directly into the deployment route. It pulls real Google Reviews and weaves them into **Expert Experience Narratives**, solving the "Information Gain" problem.
- **`SemanticSEOService.php` (The SEO):** Logic for generating **JSON-LD Schema** and "Explorer Ken's Verdict" tags.

### **2. How to Trigger Semantic Synchronization**
Since you are on an FTP-only workflow, you must trigger the "Sync" via your browser after major pushes.
1. Visit this secret URL:
   👉 `https://discoverkenya.co.ke/deploy-seo-v2026?token=pizzel-seo-magic`
2. **What this does (Scorched Earth Protocol):**
   - **Grammar Fix:** Automatically renames collections with bad grammar (e.g., "Go-Kartings" → "Go-Karting Venues").
   - **Information Gain:** Injects real Google Sentiment into empty or boilerplate descriptions.
   - **Entity Focus:** Overwrites specific priority listings (like Whistling Morans) with manual, high-authority expert text.
   - **Cache Purge:** Clears the application cache so your new "Travel Authority" content shows up immediately.

### **3. Travel Authority Guardrails**
- **Tourism Filter:** Non-tourism categories (Dentists, Car Washes, Schools) are automatically filtered out from primary search bars and collection generators.
- **Branding Entities:** The site branding has been updated to **"Discover Kenya Travel Guide"** to signal expertise to Google's Helpful Content classifiers.

---

## ⚡ PART 3: PERFORMANCE OPTIMIZATION (LCP & Speed)

**Objective:** Maintain Mobile LCP < 2.5s and PageSpeed > 90.

### **The "WebP Mascot" Win:**
Explorer Ken (your Brand Entity) uses the **.webp** format for maximum performance without losing quality.

### **The "Semantic" Performance Win:**
By using the **`@json`** PHP helper in your Blade views, we have eliminated JavaScript parsing errors in your structured data.

---

## 🔧 PART 4: MANUAL DEPLOYMENT (Fallback)

1. **Build Locally:** Run `npm run build` on your computer.
2. **Upload Folder:** Upload the contents of your local folder to `/Discover_Kenya/` via FileZilla.
3. **Important:** Ensure you upload the `public/build` folder to update CSS/JS.

---

## ⚠️ TROUBLESHOOTING

### **Issue: New SEO Titles Not Showing**
**Solution:** Visit the trigger URL:
`https://discoverkenya.co.ke/deploy-seo-v2026?token=pizzel-seo-magic`

### **Issue: 500 Error After Update**
**Solution:** Check `laravel.log`. Common causes include unclosed braces in `web.php` or missing variables in `compact()` calls within controllers.

---
*Maintained by Antigravity AI - 2026*
