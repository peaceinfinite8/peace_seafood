# Remove Duplicate View Files Script
# Backs up and removes old index.php files, keeping .view.php versions

Write-Host "Removing Duplicate View Files..." -ForegroundColor Cyan
Write-Host ""

# Create backup directory
$backupDir = ".backup/views/$(Get-Date -Format 'yyyyMMdd')"
New-Item -ItemType Directory -Path $backupDir -Force | Out-Null
Write-Host "Created backup directory: $backupDir" -ForegroundColor Green
Write-Host ""

# List of duplicate files to remove
$duplicateFiles = @(
    "src/views/keuangan/index.php",
    "src/views/penitipan/index.php",
    "src/views/penjualan/index.php",
    "src/views/retur/index.php",
    "src/views/stok/index.php"
)

$backedUp = 0
$removed = 0

foreach ($file in $duplicateFiles) {
    if (Test-Path $file) {
        # Backup the file
        $fileName = Split-Path $file -Leaf
        $moduleName = Split-Path (Split-Path $file -Parent) -Leaf
        $backupPath = Join-Path $backupDir "$moduleName-$fileName"
        
        Copy-Item $file $backupPath -Force
        Write-Host "  Backed up: $file -> $backupPath" -ForegroundColor Yellow
        $backedUp++
        
        # Remove the file
        Remove-Item $file -Force
        Write-Host "  Removed: $file" -ForegroundColor Red
        $removed++
    } else {
        Write-Host "  Not found: $file" -ForegroundColor Gray
    }
}

Write-Host ""
Write-Host "Complete!" -ForegroundColor Green
Write-Host "Files backed up: $backedUp" -ForegroundColor Yellow
Write-Host "Files removed: $removed" -ForegroundColor Yellow
Write-Host ""
Write-Host "Backup location: $backupDir" -ForegroundColor Cyan
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Cyan
Write-Host "  1. Test all affected routes" -ForegroundColor White
Write-Host "  2. Verify no 404 errors" -ForegroundColor White
Write-Host "  3. Check that .view.php versions work correctly" -ForegroundColor White
