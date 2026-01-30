#!/bin/bash

#############################################
# Automated Rollback Script
# Kenya Directory - discoverkenya.co.ke
#############################################

set -e

SITE_PATH="/home/discove6/Discover_Kenya"
BACKUP_PATH="/home/discove6/backups"


echo "🔄 Starting rollback process..."

#############################################
# Step 1: List Available Backups
#############################################
echo ""
echo "📋 Available backups:"
echo ""

cd "$BACKUP_PATH"
BACKUPS=($(ls -t | head -n 10))

if [ ${#BACKUPS[@]} -eq 0 ]; then
    echo "❌ No backups found in $BACKUP_PATH"
    exit 1
fi

for i in "${!BACKUPS[@]}"; do
    echo "  [$i] ${BACKUPS[$i]}"
done

echo ""
read -p "Enter backup number to restore (or 'q' to quit): " BACKUP_NUM

if [ "$BACKUP_NUM" = "q" ]; then
    echo "Rollback cancelled."
    exit 0
fi

if ! [[ "$BACKUP_NUM" =~ ^[0-9]+$ ]] || [ "$BACKUP_NUM" -ge "${#BACKUPS[@]}" ]; then
    echo "❌ Invalid backup number"
    exit 1
fi

SELECTED_BACKUP="${BACKUPS[$BACKUP_NUM]}"
BACKUP_DIR="$BACKUP_PATH/$SELECTED_BACKUP"

echo ""
echo "⚠️  You are about to rollback to: $SELECTED_BACKUP"
read -p "Are you sure? This will overwrite current files (y/n): " CONFIRM

if [ "$CONFIRM" != "y" ]; then
    echo "Rollback cancelled."
    exit 0
fi

#############################################
# Step 2: Enable Maintenance Mode
#############################################
echo ""
echo "🔧 Enabling maintenance mode..."
cd "$SITE_PATH"
php artisan down --message="Performing rollback. Back shortly!" --retry=60

#############################################
# Step 3: Restore Files
#############################################
echo ""
echo "📥 Restoring files from backup..."

# Create a safety backup of current state first
SAFETY_BACKUP="$BACKUP_PATH/pre_rollback_$(date +%Y%m%d_%H%M%S)"
echo "  → Creating safety backup at: $SAFETY_BACKUP"
mkdir -p "$SAFETY_BACKUP"
rsync -a --exclude='storage/app/public/media' \
         --exclude='storage/app/public/businesses' \
         --exclude='node_modules' \
         --exclude='vendor' \
         "$SITE_PATH/" "$SAFETY_BACKUP/"

# Restore application files
echo "  → Restoring application files..."
rsync -a --exclude='storage/app/public/media' \
         --exclude='storage/app/public/businesses' \
         --exclude='node_modules' \
         --exclude='vendor' \
         "$BACKUP_DIR/" "$SITE_PATH/"

#############################################
# Step 4: Restore Database
#############################################
echo ""
read -p "Restore database as well? (y/n): " RESTORE_DB

if [ "$RESTORE_DB" = "y" ]; then
    echo "  → Restoring database..."
    
    # Backup current database first
    echo "  → Backing up current database..."
    mysqldump -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" > "$SAFETY_BACKUP/database_current.sql"
    
    # Restore from backup
    DB_FILE=$(find "$BACKUP_DIR" -name "database_*.sql" | head -n 1)
    if [ -f "$DB_FILE" ]; then
        mysql -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" < "$DB_FILE"
        echo "✅ Database restored"
    else
        echo "⚠️  No database backup found in selected backup"
    fi
fi

#############################################
# Step 5: Reinstall Dependencies
#############################################
echo ""
echo "📦 Reinstalling dependencies..."
cd "$SITE_PATH"

composer install --no-dev --optimize-autoloader --no-interaction
npm ci --production

#############################################
# Step 6: Clear Caches
#############################################
echo ""
echo "🧹 Clearing caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

#############################################
# Step 7: Bring Site Back Online
#############################################
echo ""
echo "🌐 Bringing site back online..."
php artisan up

# Health check
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" https://discoverkenya.co.ke/)

if [ "$HTTP_CODE" = "200" ]; then
    echo ""
    echo "✅ Rollback successful!"
    echo "🎉 Site is responding with HTTP $HTTP_CODE"
    echo ""
    echo "📊 Rollback Summary:"
    echo "  → Restored from: $SELECTED_BACKUP"
    echo "  → Safety backup: $SAFETY_BACKUP"
    echo "  → Current time: $(date)"
else
    echo ""
    echo "⚠️  WARNING: Site returned HTTP $HTTP_CODE"
    echo "🔍 Check logs: tail -f storage/logs/laravel.log"
fi

echo ""
echo "═══════════════════════════════════════"
echo "🔄 Rollback process completed"
echo "═══════════════════════════════════════"
