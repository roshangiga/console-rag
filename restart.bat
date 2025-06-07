@echo off
setlocal enabledelayedexpansion

REM Console Project Restart Script
REM This script calls stop.bat and then start.bat for clean restart

echo 🔄 Restarting Console Project...
echo =================================

REM Check if stop.bat exists
if not exist "stop.bat" (
    echo ❌ stop.bat not found
    echo    Make sure you're in the project root directory
    pause
    exit /b 1
)

REM Check if start.bat exists
if not exist "start.bat" (
    echo ❌ start.bat not found
    echo    Make sure you're in the project root directory
    pause
    exit /b 1
)

REM Stop all services first
echo 📍 Step 1: Stopping all services...
call stop.bat

REM Wait a moment for complete shutdown
echo.
echo ⏳ Waiting for complete shutdown...
timeout /t 2 /nobreak >nul

REM Start all services
echo.
echo 📍 Step 2: Starting all services...
call start.bat