# Fix Template Literals - Convert to proper JavaScript template literal syntax
# This fixes the issue where we have backticks inside quotes

Write-Host "Fixing Template Literal Syntax..." -ForegroundColor Cyan
Write-Host ""

$viewsPath = "src/views"
$filesChanged = 0

# Get all PHP files in views directory
$phpFiles = Get-ChildItem -Path $viewsPath -Filter "*.php" -Recurse

foreach ($file in $phpFiles) {
    $content = Get-Content $file.FullName -Raw
    $originalContent = $content
    
    # Fix href with backticks inside double quotes
    # href="`${window.APP_BASE_URL}/ -> href="${window.APP_BASE_URL}/
    $content = $content -replace 'href="`\$\{window\.APP_BASE_URL\}/', 'href="${window.APP_BASE_URL}/'
    $content = $content -replace 'href="`\$\{window\.API_BASE_URL\}/', 'href="${window.API_BASE_URL}/'
    
    # Fix single quotes with backticks - these should stay as template literals in JavaScript
    # '`${window.APP_BASE_URL}/ -> `${window.APP_BASE_URL}/
    $content = $content -replace "'`\$\{window\.APP_BASE_URL\}/", '`${window.APP_BASE_URL}/'
    $content = $content -replace "'`\$\{window\.API_BASE_URL\}/", '`${window.API_BASE_URL}/'
    
    # Fix double quotes with backticks - these should stay as template literals in JavaScript  
    # "`${window.APP_BASE_URL}/ -> `${window.APP_BASE_URL}/
    $content = $content -replace '"`\$\{window\.APP_BASE_URL\}/', '`${window.APP_BASE_URL}/'
    $content = $content -replace '"`\$\{window\.API_BASE_URL\}/', '`${window.API_BASE_URL}/'
    
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
