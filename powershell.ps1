# Set source and destination paths
$sourceRoot = (Get-Location).Path
$destRoot   = "C:\Users\Admin\Desktop\Laravel"

# Remove destination if it exists, then recreate it
if (Test-Path $destRoot) {
    Remove-Item -Path $destRoot -Recurse -Force
}
New-Item -Path $destRoot -ItemType Directory -Force

# Define folders to search
$folders = @("app", "database", "routes", "resources")

# Find all .php files (exclude hidden files and symlinks)
$phpFiles = Get-ChildItem -Path $folders -Recurse -File -Filter *.php `
    | Where-Object {
        -not ($_.Attributes -band [IO.FileAttributes]::Hidden) -and
        -not ($_.Attributes -band [IO.FileAttributes]::ReparsePoint)
      }

foreach ($file in $phpFiles) {
    # Compute path relative to source root
    $relativePath = $file.FullName.Substring($sourceRoot.Length).TrimStart("\")
    $relativeDir  = [IO.Path]::GetDirectoryName($relativePath)
    $targetDir    = Join-Path $destRoot $relativeDir

    # Create target subdirectory if it doesn't exist
    if (-not (Test-Path $targetDir)) {
        New-Item -Path $targetDir -ItemType Directory -Force
    }

    # Copy file content to new .txt file in target location
    $destFile = Join-Path $targetDir ($file.BaseName + ".txt")
    Copy-Item -Path $file.FullName -Destination $destFile -Force
}
