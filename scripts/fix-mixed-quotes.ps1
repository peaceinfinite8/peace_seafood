# Fix Mixed Quotes - Ensure all template literals use consistent backticks

Write-Host "Fixing Mixed Quote Syntax..." -ForegroundColor Cyan
Write-Host ""

$viewsPath = "src/views"
$filesChanged = 0

# Get all PHP files in views directory
$phpFiles = Get-ChildItem -Path $viewsPath -Filter "*.php" -Recurse

foreach ($file in $phpFiles) {
    $content = Get-Content $file.FullName -Raw
    $originalContent = $content
    
    # Fix: `${window.APP_BASE_URL}/something' -> `${window.APP_BASE_URL}/something`
    $content = $content -replace '`\$\{window\.APP_BASE_URL\}/([^`''"]*)''', '`${window.APP_BASE_URL}/$1`'
    $content = $content -replace '`\$\{window\.API_BASE_URL\}/([^`''"]*)''', '`${window.API_BASE_URL}/$1`'
    
    # Fix: `${window.APP_BASE_URL}/something" -> `${window.APP_BASE_URL}/something`
    $content = $content -replace '`\$\{window\.APP_BASE_URL\}/([^`''"]*)"', '`${window.APP_BASE_URL}/$1`'
    $content = $content -replace '`\$\{window\.API_BASE_URL\}/([^`''"]*)"', '`${window.API_BASE_URL}/$1`'
    
    # If content changed, write it back
    if ($content -ne $originalContent) {
        Set-Content -Path $file.FullName -Value $content -NoNewline
        $filesChanged++
        Write-Host "  Fixed: $($file.Name)" -ForegroundColor Green
    }
}

Write-Host ""
Write-Host "Complete!" -ForegroundColor Green
Write-Host "Files fixed: $filesChanged" -ForegroundColor Yellow
