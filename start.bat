@echo off
setlocal enabledelayedexpansion

REM Console Project Startup Script
REM This script starts both the Laravel backend and Vue.js frontend

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

echo ✅ All dependencies are available.
echo.

REM Setup backend dependencies
echo 📦 Setting up backend dependencies...
cd backend

if not exist "vendor" (
    echo Installing Composer dependencies...
    composer install --no-interaction --prefer-dist --optimize-autoloader
)

REM Check if .env exists
if not exist ".env" (
    echo Creating .env file...
    copy .env.example .env >nul
    php artisan key:generate
)

REM Run migrations and seeders
echo Setting up database...
php artisan migrate --force
php artisan db:seed --force

echo ✅ Backend setup complete.
echo.

REM Setup frontend dependencies
echo 📦 Setting up frontend dependencies...
cd ..\frontend

if not exist "node_modules" (
    echo Installing npm dependencies...
    npm install
)

echo ✅ Frontend setup complete.
echo.

REM Create a temporary batch file to start Laravel backend
echo @echo off > start_backend.bat
echo cd backend >> start_backend.bat
echo php artisan serve --host=0.0.0.0 --port=8000 >> start_backend.bat

REM Create a temporary batch file to start Vue.js frontend
echo @echo off > start_frontend.bat
echo cd frontend >> start_frontend.bat
echo npm run dev >> start_frontend.bat

echo 🔧 Starting Laravel backend on http://localhost:8000...
start "Laravel Backend" start_backend.bat

echo 🎨 Starting Vue.js frontend on http://localhost:3000...
start "Vue.js Frontend" start_frontend.bat

echo.
echo 🎉 Console Project is now running!
echo ==================================
echo 🌐 Frontend: http://localhost:3000
echo 🔗 Backend API: http://localhost:8000/api
echo 📧 Demo Login: admin@maurtiustelecom.mu / password
echo.
echo Both servers are running in separate windows.
echo Close the console windows to stop the servers.
echo.

REM Wait for user input before closing
echo Press any key to continue...
pause >nul

REM Cleanup temporary files
if exist "start_backend.bat" del start_backend.bat
if exist "start_frontend.bat" del start_frontend.bat