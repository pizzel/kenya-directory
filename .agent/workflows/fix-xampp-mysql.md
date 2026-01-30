---
description: Fix XAMPP MySQL shutdown errors
---

# How to Fix XAMPP MySQL Shutdown Errors

If MySQL fails to start in XAMPP (usually due to an unexpected shutdown), follow these steps to repair the `aria_log` corruption.

## 1. Stop MySQL
Ensure MySQL is completely stopped in the XAMPP Control Panel. If it's stuck on "Attempting to start...", kill the `mysqld.exe` task in Task Manager.

## 2. Navigate to Data Directory
Open File Explorer and go to:
`C:\xampp\mysql\data`

## Option 1: One-Click Fix (Recommended)
We have created a script in your project root. Run this command in your terminal:

```powershell
.\fix-mysql.ps1
```

## Option 2: Manual PowerShell Commands
If you prefer to run the commands manually, copy and paste this block into PowerShell:

```powershell
# Stop MySQL
Stop-Process -Name "mysqld" -Force -ErrorAction SilentlyContinue

# Navigate to Data
cd C:\xampp\mysql\data

# Rename Corrupted Files
Rename-Item "aria_log.00000001" "aria_log.00000001.bak" -ErrorAction SilentlyContinue
Rename-Item "aria_log_control" "aria_log_control.bak" -ErrorAction SilentlyContinue

# Remove PID
Remove-Item "mysql.pid" -ErrorAction SilentlyContinue

echo "Done! You can now start MySQL in XAMPP."
```

## Option 3: Nuclear Fix (Reset from Backup)
If Option 1 & 2 fail, this script resets the MySQL core files but keeps your databases.

```powershell
.\nuclear-fix-mysql.ps1
```

**What this does:**
1.  Backs up your current `data` folder.
2.  Creates a new `data` folder from XAMPP's internal `backup`.
3.  Copies your databases and the critical `ibdata1` file into the new folder.
