# Fix XAMPP MySQL Corruption Script
# Run this via PowerShell as Administrator if possible

Write-Host "Attempting to fix XAMPP MySQL..." -ForegroundColor Cyan

# 1. Stop MySQL Process
Write-Host "Stopping any running MySQL processes..."
Stop-Process -Name "mysqld" -Force -ErrorAction SilentlyContinue
Start-Sleep -Seconds 2

# 2. Define Paths
$dataDir = "C:\xampp\mysql\data"
$ariaLog = "$dataDir\aria_log.00000001"
$ariaCtrl = "$dataDir\aria_log_control"
$pidFile = "$dataDir\mysql.pid"

# 3. Rename Corrupted Logs
if (Test-Path $ariaLog) {
    $timestamp = Get-Date -Format "yyyyMMdd-HHmmss"
    Rename-Item -Path $ariaLog -NewName "aria_log.00000001.$timestamp.bak" -Force
    Write-Host "Backed up aria_log.00000001" -ForegroundColor Green
}

if (Test-Path $ariaCtrl) {
    $timestamp = Get-Date -Format "yyyyMMdd-HHmmss"
    Rename-Item -Path $ariaCtrl -NewName "aria_log_control.$timestamp.bak" -Force
    Write-Host "Backed up aria_log_control" -ForegroundColor Green
}

# 4. Remove PID File
if (Test-Path $pidFile) {
    Remove-Item -Path $pidFile -Force
    Write-Host "Removed stale mysql.pid file" -ForegroundColor Green
}

Write-Host "---------------------------------------------------"
Write-Host "Fix Complete!" -ForegroundColor Cyan
Write-Host "Please open XAMPP Control Panel and click 'Start' on MySQL."
Write-Host "---------------------------------------------------"
