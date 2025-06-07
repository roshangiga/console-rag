@echo off
setlocal enabledelayedexpansion

REM Console Project Restart Script
REM This script stops all running services and restarts them

echo 🔄 Restarting Console Project...
echo =================================

echo 🛑 Stopping all Console services...
echo.

REM Stop processes by port using taskkill and netstat
echo Stopping Laravel Backend (port 8000)...
for /f "tokens=5" %%a in ('netstat -ano ^| findstr :8000 ^| findstr LISTENING') do (
    if not "%%a"=="" (
        echo    Killing process %%a
        taskkill /PID %%a /F >nul 2>&1
    )
)

echo Stopping Vue.js Frontend (port 3000)...
for /f "tokens=5" %%a in ('netstat -ano ^| findstr :3000 ^| findstr LISTENING') do (
    if not "%%a"=="" (
        echo    Killing process %%a
        taskkill /PID %%a /F >nul 2>&1
    )
)

echo Stopping phpMyAdmin (port 8080)...
for /f "tokens=5" %%a in ('netstat -ano ^| findstr :8080 ^| findstr LISTENING') do (
    if not "%%a"=="" (
        echo    Killing process %%a
        taskkill /PID %%a /F >nul 2>&1
    )
)

REM Stop processes by name (backup method)
echo Stopping PHP processes...
taskkill /IM php.exe /F >nul 2>&1
echo Stopping Node.js processes...
taskkill /IM node.exe /F >nul 2>&1

echo.
echo ⏳ Waiting for services to fully stop...
timeout /t 3 /nobreak >nul

echo ✅ All services stopped.
echo.

REM Start services again
echo 🚀 Starting Console Project...
echo ==============================

REM Check if start.bat exists
if not exist "start.bat" (
    echo ❌ start.bat not found
    echo    Make sure you're in the project root directory
    pause
    exit /b 1
)

REM Start the application
call start.bat