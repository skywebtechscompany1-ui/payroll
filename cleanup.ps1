# Cleanup Script for Deployment
# Run this before pushing to GitHub

Write-Host "🧹 Cleaning up project for deployment..." -ForegroundColor Cyan

# Navigate to backend
Set-Location backend

# Remove Python cache
Write-Host "Removing Python cache..." -ForegroundColor Yellow
if (Test-Path "__pycache__") {
    Remove-Item -Recurse -Force "__pycache__"
}
Get-ChildItem -Recurse -Filter "__pycache__" | Remove-Item -Recurse -Force

# Remove .pyc files
Write-Host "Removing .pyc files..." -ForegroundColor Yellow
Get-ChildItem -Recurse -Filter "*.pyc" | Remove-Item -Force

# Remove virtual environment (will be recreated on Render)
Write-Host "Removing virtual environment..." -ForegroundColor Yellow
if (Test-Path "venv") {
    Remove-Item -Recurse -Force "venv"
}

# Remove local .env (don't commit secrets)
Write-Host "Removing local .env..." -ForegroundColor Yellow
if (Test-Path ".env") {
    Remove-Item -Force ".env"
}

# Remove old seed file
Write-Host "Removing old seed files..." -ForegroundColor Yellow
if (Test-Path "seed_data.py") {
    Remove-Item -Force "seed_data.py"
}
if (Test-Path "reset_and_seed.py") {
    Remove-Item -Force "reset_and_seed.py"
}

# Remove requirements folder (using requirements.txt)
Write-Host "Removing requirements folder..." -ForegroundColor Yellow
if (Test-Path "requirements") {
    Remove-Item -Recurse -Force "requirements"
}

# Back to root
Set-Location ..

# Frontend cleanup
Write-Host "Cleaning frontend..." -ForegroundColor Yellow
Set-Location frontend

# Remove node_modules (will be reinstalled)
if (Test-Path "node_modules") {
    Write-Host "Removing node_modules (large folder)..." -ForegroundColor Yellow
    Remove-Item -Recurse -Force "node_modules"
}

# Remove .nuxt cache
if (Test-Path ".nuxt") {
    Remove-Item -Recurse -Force ".nuxt"
}

# Remove .output (build artifacts)
if (Test-Path ".output") {
    Remove-Item -Recurse -Force ".output"
}

# Remove local .env
if (Test-Path ".env") {
    Remove-Item -Force ".env"
}

# Back to root
Set-Location ..

Write-Host ""
Write-Host "✅ Cleanup complete!" -ForegroundColor Green
Write-Host ""
Write-Host "📋 Next steps:" -ForegroundColor Cyan
Write-Host "1. Review changes: git status" -ForegroundColor White
Write-Host "2. Add files: git add ." -ForegroundColor White
Write-Host "3. Commit: git commit -m 'Production ready'" -ForegroundColor White
Write-Host "4. Push: git push origin main" -ForegroundColor White
Write-Host ""
Write-Host "📚 See DEPLOYMENT_READY.md for full deployment guide" -ForegroundColor Cyan
