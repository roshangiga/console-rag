@echo off
setlocal enabledelayedexpansion

REM Console Project Stop Script
REM This script gracefully stops all running services

echo 🛑 Stopping Console Project...
echo ===============================

echo Stopping Laravel Backend (port 8000)...
for /f "tokens=5" %%a in ('netstat -ano ^| findstr :8000 ^| findstr LISTENING') do (
    if not "%%a"=="" (
        echo    Stopping process %%a
        taskkill /PID %%a /T >nul 2>&1
    )
)

echo Stopping Vue.js Frontend (port 3000)...
for /f "tokens=5" %%a in ('netstat -ano ^| findstr :3000 ^| findstr LISTENING') do (
    if not "%%a"=="" (
        echo    Stopping process %%a
        taskkill /PID %%a /T >nul 2>&1
    )
)

echo Stopping phpMyAdmin (port 8080)...
for /f "tokens=5" %%a in ('netstat -ano ^| findstr :8080 ^| findstr LISTENING') do (
    if not "%%a"=="" (
        echo    Stopping process %%a
        taskkill /PID %%a /T >nul 2>&1
    )
)

echo.
echo ⏳ Final cleanup...
timeout /t 2 /nobreak >nul

REM Force stop any remaining PHP and Node processes
taskkill /IM php.exe /F >nul 2>&1
taskkill /IM node.exe /F >nul 2>&1

echo.
echo ✅ Console Project stopped successfully!
echo.
echo To start again, run: start.bat
echo To restart, run: restart.bat

pause