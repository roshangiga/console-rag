#!/bin/bash

# Console Project Stop Script
# This script gracefully stops all running services

echo "🛑 Stopping Console Project..."
echo "==============================="

# Function to find and kill processes by port
kill_port() {
    local port=$1
    local name=$2
    
    echo "Stopping $name on port $port..."
    
    # Find PIDs using the port
    PIDS=$(lsof -ti :$port 2>/dev/null)
    
    if [ ! -z "$PIDS" ]; then
        echo "   Found processes: $PIDS"
        
        # Graceful shutdown first
        kill -TERM $PIDS 2>/dev/null
        sleep 3
        
        # Check if still running
        REMAINING=$(lsof -ti :$port 2>/dev/null)
        if [ ! -z "$REMAINING" ]; then
            echo "   Force killing remaining processes: $REMAINING"
            kill -9 $REMAINING 2>/dev/null
        fi
        
        echo "✅ $name stopped"
    else
        echo "   No processes found on port $port"
    fi
}

# Function to kill processes by name pattern
kill_by_name() {
    local pattern=$1
    local name=$2
    
    echo "Stopping $name processes..."
    
    PIDS=$(ps aux | grep "$pattern" | grep -v grep | awk '{print $2}')
    
    if [ ! -z "$PIDS" ]; then
        echo "   Found processes: $PIDS"
        
        # Graceful shutdown first
        kill -TERM $PIDS 2>/dev/null
        sleep 2
        
        # Force kill if still running
        REMAINING=$(ps aux | grep "$pattern" | grep -v grep | awk '{print $2}')
        if [ ! -z "$REMAINING" ]; then
            echo "   Force killing remaining processes: $REMAINING"
            kill -9 $REMAINING 2>/dev/null
        fi
        
        echo "✅ $name stopped"
    else
        echo "   No $name processes found"
    fi
}

# Stop all services
kill_port 8000 "Laravel Backend"
kill_port 3000 "Vue.js Frontend"
kill_port 8080 "phpMyAdmin"

# Stop by process pattern (backup method)
kill_by_name "php artisan serve" "Laravel Artisan"
kill_by_name "npm run dev" "NPM Dev Server"
kill_by_name "php -S localhost:8080" "PHP Built-in Server"

echo ""
echo "⏳ Final cleanup..."
sleep 2

# Clear any remaining processes
pkill -f "artisan serve" 2>/dev/null
pkill -f "npm run dev" 2>/dev/null
pkill -f "php -S localhost:8080" 2>/dev/null

echo ""
echo "✅ Console Project stopped successfully!"
echo ""
echo "To start again, run: ./start.sh"
echo "To restart, run: ./restart.sh"