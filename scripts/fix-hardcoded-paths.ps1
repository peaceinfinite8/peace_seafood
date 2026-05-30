# Fix Hardcoded Paths Script
# Replaces all /peace_seafood/ hardcoded paths with dynamic base URL

Write-Host "Fixing Hardcoded Paths in View Files..." -ForegroundColor Cyan
Write-Host ""

$viewsPath = "src/views"
$filesChanged = 0
$totalReplacements = 0

# Get all PHP files in views directory
$phpFiles = Get-ChildItem -Path $viewsPath -Filter "*.php" -Recurse

foreach ($file in $phpFiles) {
    $content = Get-Content $file.FullName -Raw
    $originalContent = $content
    $fileReplacements = 0
    
    # Replace API calls in single quotes
    if ($content -match "'/peace_seafood/api/") {
        $content = $content -replace "'/peace_seafood/api/", "'`${window.API_BASE_URL}/"
        $fileReplacements++
    }
    
    # Replace page paths in single quotes
    if ($content -match "'/peace_seafood/") {
        $content = $content -replace "'/peace_seafood/", "'`${window.APP_BASE_URL}/"
        $fileReplacements++
    }
    
    # Replace href attributes
    if ($content -match 'href="/peace_seafood/') {
        $content = $content -replace 'href="/peace_seafood/', 'href="`${window.APP_BASE_URL}/'
        $fileReplacements++
    }
    
    # Replace API calls in double quotes
    if ($content -match '"/peace_seafood/api/') {
        $content = $content -replace '"/peace_seafood/api/', '"`${window.API_BASE_URL}/'
        $fileReplacements++
    }
    
    # Replace page paths in double quotes
    if ($content -match '"/peace_seafood/') {
        $content = $content -replace '"/peace_seafood/', '"`${window.APP_BASE_URL}/'
        $fileReplacements++
    }
    
    # If content changed, write it back
    if ($content -ne $originalContent) {
        Set-Content -Path $file.FullName -Value $content -NoNewline
        $filesChanged++
        $totalReplacements += $fileReplacements
        Write-Host "  Updated: $($file.Name)" -ForegroundColor Green
    }
}

Write-Host ""
Write-Host "Complete!" -ForegroundColor Green
Write-Host "Files changed: $filesChanged" -ForegroundColor Yellow
Write-Host "Total replacements: $totalReplacements" -ForegroundColor Yellow
