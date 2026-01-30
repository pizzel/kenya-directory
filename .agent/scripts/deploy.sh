#!/bin/bash

#############################################
# Automated Production Deployment Script
# Kenya Directory - discoverkenya.co.ke
#############################################

set -e  # Exit on any error

# Configuration (Updated for discoverkenya.co.ke)
SITE_PATH="/home/discove6/Discover_Kenya"
BACKUP_PATH="/home/discove6/backups"
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="$BACKUP_PATH/backup_$DATE"

echo "🚀 Starting deployment for Kenya Directory..."
echo "📅 Timestamp: $DATE"

#############################################
# Step 1: Create Backup
#############################################
echo ""
echo "📦 Step 1/7: Creating backup..."

mkdir -p "$BACKUP_DIR"

# Backup critical files (excluding images/media)
echo "  → Backing up application files..."
rsync -a --exclude='storage/app/public/media' \
         --exclude='storage/app/public/businesses' \
         --exclude='node_modules' \
         --exclude='vendor' \
         "$SITE_PATH/" "$BACKUP_DIR/"

# Backup database
echo "  → Backing up database..."
mysqldump -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" > "$BACKUP_DIR/database_$DATE.sql"

# Backup .env
echo "  → Backing up .env file..."
cp "$SITE_PATH/.env" "$BACKUP_DIR/.env.backup"

echo "✅ Backup created at: $BACKUP_DIR"

#############################################
# Step 2: Enable Maintenance Mode
#############################################
echo ""
echo "🔧 Step 2/7: Enabling maintenance mode..."
cd "$SITE_PATH"
php artisan down --message="Upgrading to React homepage. Back in 2 minutes!" --retry=60

#############################################
# Step 3: Pull Latest Code
#############################################
echo ""
echo "📥 Step 3/7: Pulling latest code from GitHub..."
git fetch origin main
git reset --hard origin/main

#############################################
# Step 4: Install Dependencies
#############################################
echo ""
echo "📦 Step 4/7: Installing dependencies..."

# Composer (production optimized)
echo "  → Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# NPM (production mode)
echo "  → Installing Node dependencies..."
npm ci --production

#############################################
# Step 5: Build Assets
#############################################
echo ""
echo "🏗️  Step 5/7: Building production assets..."

echo "  → Building client bundle..."
npm run build

echo "  → Building SSR bundle..."
npm run build:ssr

#############################################
# Step 6: Optimize & Clear Caches
#############################################
echo ""
echo "🧹 Step 6/7: Optimizing application..."

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Cache config and routes for production
php artisan config:cache
php artisan route:cache

# Generate Ziggy routes
php artisan ziggy:generate

# Optimize autoloader
composer dump-autoload --optimize

#############################################
# Step 7: Restore Site & Verify
#############################################
echo ""
echo "🌐 Step 7/7: Bringing site back online..."

# Disable maintenance mode
php artisan up

# Quick health check
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" https://discoverkenya.co.ke/)

if [ "$HTTP_CODE" = "200" ]; then
    echo "✅ Deployment successful!"
    echo "🎉 Site is live and responding with HTTP $HTTP_CODE"
    echo ""
    echo "📊 Deployment Summary:"
    echo "  → Backup location: $BACKUP_DIR"
    echo "  → Git commit: $(git rev-parse --short HEAD)"
    echo "  → Deployment time: $(date)"
    echo ""
    echo "🔍 Next Steps:"
    echo "  1. Visit https://discoverkenya.co.ke/ and verify homepage"
    echo "  2. Check browser console for errors (F12)"
    echo "  3. Test 'Load More' functionality"
    echo "  4. Monitor logs: tail -f storage/logs/laravel.log"
else
    echo "⚠️  WARNING: Site returned HTTP $HTTP_CODE"
    echo "🔄 Consider running rollback if issues persist"
    echo "   Backup available at: $BACKUP_DIR"
    exit 1
fi

#############################################
# Cleanup Old Backups (keep last 10)
#############################################
echo ""
echo "🧹 Cleaning old backups (keeping last 10)..."
cd "$BACKUP_PATH"
ls -t | tail -n +11 | xargs -r rm -rf
echo "✅ Cleanup complete"

echo ""
echo "═══════════════════════════════════════"
echo "🎊 Deployment completed successfully!"
echo "═══════════════════════════════════════"
