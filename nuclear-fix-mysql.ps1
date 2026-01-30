# Fix XAMPP MySQL - NUCLEAR OPTION
# ONLY RUN THIS IF THE STANDARD FIX FAILS
# This resets the MySQL configuration but preserves your database files.

Write-Host "⚠️  INITIATING NUCLEAR REPAIR FOR XAMPP MYSQL ⚠️" -ForegroundColor Yellow
Write-Host "This will replace core MySQL files while keeping your databases."

# 1. Stop MySQL Process
Write-Host "1. Stopping MySQL..."
Stop-Process -Name "mysqld" -Force -ErrorAction SilentlyContinue
Start-Sleep -Seconds 2

# 2. Paths
$mysqlPath = "C:\xampp\mysql"
$dataPath = "$mysqlPath\data"
$backupPath = "$mysqlPath\backup"
$timestamp = Get-Date -Format "yyyyMMdd-HHmmss"
$oldDataPath = "$mysqlPath\data_old_$timestamp"

# 3. Rename current corrupted data folder
if (Test-Path $dataPath) {
    Write-Host "2. Renaming current 'data' folder to 'data_old_$timestamp'..."
    Rename-Item $dataPath $oldDataPath -Force
} else {
    Write-Host "Error: Data folder not found!" -ForegroundColor Red
    Exit
}

# 4. Create fresh data folder from backup
Write-Host "3. Creating fresh 'data' folder from 'backup'..."
Copy-Item -Path $backupPath -Destination $dataPath -Recurse

# 5. Restore User Databases & Critical Files
Write-Host "4. Restoring your databases (kenya_directory, laravel, etc)..."

# Exclude default system folders that we want to remain FRESH
$exclude = @("mysql", "performance_schema", "phpmyadmin", "test")

# Copy Database Folders (Subdirectories)
Get-ChildItem $oldDataPath -Directory | Where-Object { $_.Name -notin $exclude } | ForEach-Object {
    Write-Host "   Restoring database: $($_.Name)"
    Copy-Item $_.FullName -Destination "$dataPath\$($_.Name)" -Recurse -Force
}

# 6. Restore ibdata1 (The most critical file for InnoDB tables)
Write-Host "5. Restoring ibdata1 (Critical Data File)..."
if (Test-Path "$oldDataPath\ibdata1") {
    Copy-Item "$oldDataPath\ibdata1" "$dataPath\ibdata1" -Force
} else {
    Write-Host "WARNING: ibdata1 not found in old data! Data might be lost." -ForegroundColor Red
}

Write-Host "---------------------------------------------------"
Write-Host "✅ NUCLEAR REPAIR COMPLETE!" -ForegroundColor Green
Write-Host "Go to XAMPP Control Panel and click 'Start' on MySQL."
Write-Host "---------------------------------------------------"
