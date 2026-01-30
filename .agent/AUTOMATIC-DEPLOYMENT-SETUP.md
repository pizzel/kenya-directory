# 🚀 Automatic Deployment Setup Guide

## What You're Getting

After this one-time setup, deployments will work like this:

```
You make changes locally
         ↓
git add . && git commit -m "your changes"
         ↓
git push origin main
         ↓
🤖 GitHub automatically deploys to discoverkenya.co.ke
         ↓
✅ Site is live in ~2 minutes!
```

---

## One-Time Setup (Do This Once)

### Step 1: Generate SSH Key for GitHub Actions

SSH into your server and run:

```bash
ssh pizzelke@discoverkenya.co.ke
cd ~
ssh-keygen -t ed25519 -C "github-actions-deploy" -f ~/.ssh/github_deploy

# Display the private key (you'll add this to GitHub)
cat ~/.ssh/github_deploy

# Add the public key to authorized_keys
cat ~/.ssh/github_deploy.pub >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
```

**Copy the entire private key output** (including `-----BEGIN` and `-----END` lines)

---

### Step 2: Add Secrets to GitHub Repository

1. Go to your GitHub repository: https://github.com/pizzel/kenya-directory

2. Click **Settings** → **Secrets and variables** → **Actions**

3. Click **New repository secret** and add these 7 secrets:

| Secret Name | Value | Example |
|------------|-------|---------|
| `SSH_PRIVATE_KEY` | The private key you copied above | `-----BEGIN OPENSSH PRIVATE KEY-----...` |
| `SSH_USER` | Your cPanel username | `pizzelke` |
| `SERVER_IP` | Your server IP or hostname | `discoverkenya.co.ke` or `server1.hostinger.com` |
| `SITE_PATH` | Full path to your website | `/home/pizzelke/public_html` |
| `DB_USERNAME` | MySQL username | `pizzelke_kenya` |
| `DB_PASSWORD` | MySQL password | Your database password |
| `DB_DATABASE` | Database name | `pizzelke_kenya_db` |

---

### Step 3: Upload Deployment Scripts to Server

From your local machine, upload the deployment scripts:

```bash
# You can do this via FTP or SCP
scp .agent/scripts/deploy.sh pizzelke@discoverkenya.co.ke:/home/pizzelke/
scp .agent/scripts/rollback.sh pizzelke@discoverkenya.co.ke:/home/pizzelke/

# Then SSH in and make them executable
ssh pizzelke@discoverkenya.co.ke
chmod +x ~/deploy.sh ~/rollback.sh

# Create backup directory
mkdir -p ~/backups
```

---

### Step 4: Update Deploy Script with Your Credentials

SSH into your server and edit the deploy script:

```bash
ssh pizzelke@discoverkenya.co.ke
nano ~/deploy.sh
```

At the top of the file, update these environment variables:

```bash
# Add these lines after the configuration section (line ~13)
export DB_USERNAME="pizzelke_kenya"    # Your actual DB username
export DB_PASSWORD="your_password"     # Your actual DB password
export DB_DATABASE="pizzelke_kenya_db" # Your actual DB name
```

Save and exit (Ctrl+X, then Y, then Enter)

---

### Step 5: Test Deployment Manually (First Time)

Before relying on automation, test once manually:

```bash
ssh pizzelke@discoverkenya.co.ke
cd ~/public_html
~/deploy.sh
```

If this works successfully, you're ready for automatic deployments! 🎉

---

## How to Use (After Setup)

### Automatic Deployment

Just push to GitHub - that's it!

```bash
# Make your changes
git add .
git commit -m "fix: update homepage styling"
git push origin main

# 🤖 GitHub Actions will automatically:
# 1. Create backup
# 2. Pull code
# 3. Install dependencies
# 4. Build assets
# 5. Deploy to live site
# 6. Run health check
```

**Watch the deployment:**
1. Go to your GitHub repo
2. Click **Actions** tab
3. You'll see the deployment progress in real-time

---

### Manual Deployment (Backup Option)

If GitHub Actions is down, you can still deploy manually:

```bash
ssh pizzelke@discoverkenya.co.ke
cd ~/public_html
~/deploy.sh
```

---

### Emergency Rollback

If something goes wrong:

```bash
ssh pizzelke@discoverkenya.co.ke
~/rollback.sh

# This will:
# 1. Show last 10 backups
# 2. Let you choose which to restore
# 3. Restore files and optionally database
# 4. Bring site back online
```

---

## Deployment Workflow Examples

### Example 1: Quick Fix
```bash
# Fix a typo in HomeController.php
git add app/Http/Controllers/HomeController.php
git commit -m "fix: correct typo in hero section"
git push

# ✅ Live in 2 minutes
```

### Example 2: New Feature
```bash
# Add a new React component
git add resources/js/Components/NewFeature.jsx
git commit -m "feat: add new feature component"
git push

# ✅ Automatically built and deployed
```

### Example 3: Multiple Changes
```bash
# Update several files
git add .
git commit -m "refactor: improve performance"
git push

# ✅ All changes deployed together
```

---

## What Gets Backed Up Automatically

Every deployment creates a backup of:
- ✅ All PHP files (app/, routes/, config/, etc.)
- ✅ All React components
- ✅ Database (full SQL dump)
- ✅ .env file
- ✅ Configuration files
- ❌ Media files (too large, not needed)
- ❌ node_modules (regenerated)
- ❌ vendor (regenerated)

**Backup retention:** Last 10 backups are kept, older ones auto-deleted.

---

## Monitoring Deployments

### GitHub Actions Dashboard
- **URL:** https://github.com/pizzel/kenya-directory/actions
- See deployment status, logs, and history
- Get email notifications on failures

### Server Logs
```bash
# Watch deployment in real-time
ssh pizzelke@discoverkenya.co.ke
tail -f ~/public_html/storage/logs/laravel.log
```

### Check Last Deployment
```bash
ssh pizzelke@discoverkenya.co.ke
cd ~/public_html
git log -1 --pretty=format:"%h - %s (%cr)" # Show last commit
ls -lh ~/backups | tail -5  # Show recent backups
```

---

## Troubleshooting

### Deployment Failed in GitHub Actions

1. **Check Actions tab** for error details
2. **Common issues:**
   - SSH key incorrect → Re-add `SSH_PRIVATE_KEY` secret
   - Server unreachable → Check `SERVER_IP` secret
   - Permission denied → Check SSH key is in `authorized_keys`

### Site Shows 500 Error After Deployment

```bash
# Quick fix: clear caches
ssh pizzelke@discoverkenya.co.ke
cd ~/public_html
php artisan cache:clear
php artisan config:clear

# If still broken: rollback
~/rollback.sh
```

### Need to Disable Auto-Deploy Temporarily

**Option 1:** Disable the workflow in GitHub
1. Go to **Actions** tab
2. Click on "Deploy to Production"
3. Click "..." → Disable workflow

**Option 2:** Deploy to a different branch
```bash
# Work on a feature branch without triggering deploy
git checkout -b my-feature
git push origin my-feature  # Won't trigger deployment
```

---

## Security Notes

✅ **SSH key** is encrypted in GitHub secrets  
✅ **Database password** never exposed in logs  
✅ **Backups** exclude media to save space  
✅ **Maintenance mode** shown during deployment  
✅ **Health check** runs before marking deployment successful  

---

## Next Steps

1. ✅ Complete the One-Time Setup above
2. ✅ Test manual deployment once
3. ✅ Make a small test commit and push
4. ✅ Watch it deploy automatically
5. ✅ Celebrate! 🎉

---

**Questions or Issues?**
Just ask and I can help troubleshoot or adjust the automation!
