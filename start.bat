@echo off
setlocal enabledelayedexpansion

REM Console Project Startup Script
REM This script starts the Laravel backend, Vue.js frontend, and phpMyAdmin

echo 🚀 Starting Console Project...
echo ==================================

REM Check if PHP is installed
php --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ PHP is not installed. Please install PHP 8.1 or higher.
    pause
    exit /b 1
)

REM Check if Composer is installed
composer --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Composer is not installed. Please install Composer.
    pause
    exit /b 1
)

REM Check if Node.js is installed
node --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Node.js is not installed. Please install Node.js 18 or higher.
    pause
    exit /b 1
)

REM Check if npm is installed
npm --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ npm is not installed. Please install npm.
    pause
    exit /b 1
)

REM Check if MySQL is installed
mysql --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ MySQL is not installed. Please install MySQL server.
    pause
    exit /b 1
)

echo ✅ All dependencies are available.
echo.

REM Setup backend dependencies
echo 📦 Setting up backend dependencies...
cd backend

if not exist "vendor" (
    echo Installing Composer dependencies...
    composer install --no-interaction --prefer-dist --optimize-autoloader
    if %errorlevel% neq 0 (
        echo ❌ Composer install failed.
        pause
        exit /b 1
    )
)

echo ✅ Composer dependencies ready.

REM Check if .env exists
if not exist ".env" (
    echo Creating .env file...
    copy .env.example .env >nul
    php artisan key:generate
)

REM Create MySQL database if it doesn't exist
echo Setting up MySQL database...
mysql -u root -e "CREATE DATABASE IF NOT EXISTS console_rag;" 2>nul
if %errorlevel% neq 0 (
    echo ⚠️  Could not create database. Please ensure MySQL is running and accessible.
    echo    You may need to start MySQL service manually.
)

REM Run migrations and seeders
echo Setting up database...
php artisan migrate --force
if %errorlevel% neq 0 (
    echo ❌ Database migration failed.
    echo    Please ensure MySQL is running and the database 'console_rag' exists.
    pause
    exit /b 1
)

php artisan db:seed --force
if %errorlevel% neq 0 (
    echo ❌ Database seeding failed.
    pause
    exit /b 1
)

echo ✅ Backend setup complete.
echo.

REM Setup frontend dependencies
echo 📦 Setting up frontend dependencies...
cd ..\frontend

if not exist "node_modules" (
    echo Installing npm dependencies...
    npm install
    if %errorlevel% neq 0 (
        echo ❌ npm install failed.
        pause
        exit /b 1
    )
)

echo ✅ Frontend setup complete.
echo.

REM Create temporary batch files for servers
echo @echo off > start_backend.bat
echo cd backend >> start_backend.bat
echo php artisan serve --host=0.0.0.0 --port=8000 >> start_backend.bat

echo @echo off > start_frontend.bat
echo cd frontend >> start_frontend.bat
echo npm run dev >> start_frontend.bat

echo @echo off > start_phpmyadmin.bat
echo cd phpmyadmin >> start_phpmyadmin.bat
echo php -S localhost:8080 >> start_phpmyadmin.bat

echo 🔧 Starting Laravel backend on http://localhost:8000...
start "Laravel Backend" start_backend.bat

echo 🎨 Starting Vue.js frontend on http://localhost:3000...
start "Vue.js Frontend" start_frontend.bat

echo 🗄️  Starting phpMyAdmin on http://localhost:8080...
start "phpMyAdmin" start_phpmyadmin.bat

echo.
echo 🎉 Console Project is now running!
echo ==================================
echo 🌐 Frontend: http://localhost:3000
echo 🔗 Backend API: http://localhost:8000/api
echo 📚 API Documentation: http://localhost:8000/api/documentation
echo 🗄️  phpMyAdmin: http://localhost:8080
echo 📧 Demo Login: admin@maurtiustelecom.mu / password
echo.
echo All servers are running in separate windows.
echo Close the console windows to stop the servers.
echo.

REM Wait for user input before closing
echo Press any key to continue...
pause >nul

REM Cleanup temporary files
if exist "start_backend.bat" del start_backend.bat
if exist "start_frontend.bat" del start_frontend.bat
if exist "start_phpmyadmin.bat" del start_phpmyadmin.bat