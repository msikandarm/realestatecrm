# Real Estate CRM - Quick Setup Script for Windows (PowerShell)
# Run this script in PowerShell to quickly set up the project

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Real Estate CRM - Quick Setup" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check if Composer is installed
Write-Host "Checking for Composer..." -ForegroundColor Yellow
$composerExists = Get-Command composer -ErrorAction SilentlyContinue
if (-not $composerExists) {
    Write-Host "ERROR: Composer is not installed or not in PATH" -ForegroundColor Red
    Write-Host "Please install Composer from https://getcomposer.org" -ForegroundColor Red
    exit 1
}
Write-Host "✓ Composer found" -ForegroundColor Green

# Check if PHP is installed
Write-Host "Checking for PHP..." -ForegroundColor Yellow
$phpExists = Get-Command php -ErrorAction SilentlyContinue
if (-not $phpExists) {
    Write-Host "ERROR: PHP is not installed or not in PATH" -ForegroundColor Red
    exit 1
}
$phpVersion = php -v
Write-Host "✓ PHP found" -ForegroundColor Green
Write-Host $phpVersion[0] -ForegroundColor Gray

# Install Composer dependencies
Write-Host ""
Write-Host "Installing Composer dependencies..." -ForegroundColor Yellow
composer install --no-interaction
if ($LASTEXITCODE -ne 0) {
    Write-Host "ERROR: Composer install failed" -ForegroundColor Red
    exit 1
}
Write-Host "✓ Composer dependencies installed" -ForegroundColor Green

# Install NPM dependencies
Write-Host ""
Write-Host "Checking for NPM..." -ForegroundColor Yellow
$npmExists = Get-Command npm -ErrorAction SilentlyContinue
if ($npmExists) {
    Write-Host "✓ NPM found" -ForegroundColor Green
    Write-Host "Installing NPM dependencies..." -ForegroundColor Yellow
    npm install
    Write-Host "✓ NPM dependencies installed" -ForegroundColor Green
} else {
    Write-Host "⚠ NPM not found. Skipping frontend dependencies." -ForegroundColor Yellow
    Write-Host "Install Node.js from https://nodejs.org to enable frontend assets" -ForegroundColor Yellow
}

# Copy .env file if it doesn't exist
Write-Host ""
Write-Host "Setting up environment file..." -ForegroundColor Yellow
if (-not (Test-Path ".env")) {
    Copy-Item ".env.example" ".env"
    Write-Host "✓ .env file created" -ForegroundColor Green
} else {
    Write-Host "✓ .env file already exists" -ForegroundColor Green
}

# Generate application key
Write-Host ""
Write-Host "Generating application key..." -ForegroundColor Yellow
php artisan key:generate --ansi
Write-Host "✓ Application key generated" -ForegroundColor Green

# Ask about database setup
Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Database Configuration" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Before running migrations, please configure your database in .env file:" -ForegroundColor Yellow
Write-Host ""
Write-Host "DB_CONNECTION=mysql" -ForegroundColor White
Write-Host "DB_HOST=127.0.0.1" -ForegroundColor White
Write-Host "DB_PORT=3306" -ForegroundColor White
Write-Host "DB_DATABASE=realestatecrm" -ForegroundColor White
Write-Host "DB_USERNAME=your_username" -ForegroundColor White
Write-Host "DB_PASSWORD=your_password" -ForegroundColor White
Write-Host ""

$runMigrations = Read-Host "Have you configured the database? Run migrations now? (y/n)"
if ($runMigrations -eq "y" -or $runMigrations -eq "Y") {
    Write-Host ""
    Write-Host "Running database migrations..." -ForegroundColor Yellow
    php artisan migrate --force

    if ($LASTEXITCODE -eq 0) {
        Write-Host "✓ Database migrations completed" -ForegroundColor Green

        Write-Host ""
        $runSeeders = Read-Host "Seed initial data (roles, permissions, users)? (y/n)"
        if ($runSeeders -eq "y" -or $runSeeders -eq "Y") {
            Write-Host "Running database seeders..." -ForegroundColor Yellow
            php artisan db:seed --force
            Write-Host "✓ Database seeded successfully" -ForegroundColor Green
        }
    } else {
        Write-Host "ERROR: Database migration failed" -ForegroundColor Red
        Write-Host "Please check your database configuration in .env" -ForegroundColor Red
    }
} else {
    Write-Host "⚠ Skipping migrations. Run 'php artisan migrate' when ready." -ForegroundColor Yellow
}

# Create storage link
Write-Host ""
Write-Host "Creating storage link..." -ForegroundColor Yellow
php artisan storage:link
Write-Host "✓ Storage link created" -ForegroundColor Green

# Summary
Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Setup Complete!" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Next Steps:" -ForegroundColor Yellow
Write-Host ""
Write-Host "1. Start the development server:" -ForegroundColor White
Write-Host "   php artisan serve" -ForegroundColor Cyan
Write-Host ""
Write-Host "2. In a new terminal, compile frontend assets:" -ForegroundColor White
Write-Host "   npm run dev" -ForegroundColor Cyan
Write-Host ""
Write-Host "3. Access the application:" -ForegroundColor White
Write-Host "   http://localhost:8000" -ForegroundColor Cyan
Write-Host ""
Write-Host "4. Login with default credentials:" -ForegroundColor White
Write-Host "   Email: admin@realestatecrm.com" -ForegroundColor Cyan
Write-Host "   Password: password" -ForegroundColor Cyan
Write-Host ""
Write-Host "5. Read the documentation:" -ForegroundColor White
Write-Host "   DOCUMENTATION.md - Complete system documentation" -ForegroundColor Cyan
Write-Host "   SYSTEM-SUMMARY.md - Quick overview and checklist" -ForegroundColor Cyan
Write-Host "   README-PROJECT.md - Quick start guide" -ForegroundColor Cyan
Write-Host ""
Write-Host "⚠ IMPORTANT: Change default passwords after first login!" -ForegroundColor Red
Write-Host ""
Write-Host "For issues or questions, refer to DOCUMENTATION.md" -ForegroundColor Gray
Write-Host ""
