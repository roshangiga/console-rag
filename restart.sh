#!/bin/bash

# Console Project Restart Script
# This script stops all running services and restarts them

echo "🔄 Restarting Console Project..."
echo "================================="

# Function to find and kill processes by port
kill_port() {
    local port=$1
    local name=$2
    
    echo "🛑 Stopping $name on port $port..."
    
    # Find PIDs using the port
    PIDS=$(lsof -ti :$port 2>/dev/null)
    
    if [ ! -z "$PIDS" ]; then
        echo "   Found processes: $PIDS"
        kill $PIDS 2>/dev/null
        sleep 2
        
        # Force kill if still running
        REMAINING=$(lsof -ti :$port 2>/dev/null)
        if [ ! -z "$REMAINING" ]; then
            echo "   Force killing remaining processes: $REMAINING"
            kill -9 $REMAINING 2>/dev/null
        fi
        
        echo "✅ $name stopped successfully"
    else
        echo "   No processes found on port $port"
    fi
}

# Function to kill processes by name pattern
kill_by_name() {
    local pattern=$1
    local name=$2
    
    echo "🛑 Stopping $name processes..."
    
    PIDS=$(ps aux | grep "$pattern" | grep -v grep | awk '{print $2}')
    
    if [ ! -z "$PIDS" ]; then
        echo "   Found processes: $PIDS"
        kill $PIDS 2>/dev/null
        sleep 2
        
        # Force kill if still running
        REMAINING=$(ps aux | grep "$pattern" | grep -v grep | awk '{print $2}')
        if [ ! -z "$REMAINING" ]; then
            echo "   Force killing remaining processes: $REMAINING"
            kill -9 $REMAINING 2>/dev/null
        fi
        
        echo "✅ $name stopped successfully"
    else
        echo "   No $name processes found"
    fi
}

# Stop all services
echo "🛑 Stopping all Console services..."
echo ""

# Stop by port
kill_port 8000 "Laravel Backend"
kill_port 3000 "Vue.js Frontend" 
kill_port 8080 "phpMyAdmin"

# Stop by process pattern (backup method)
kill_by_name "php artisan serve" "Laravel Artisan"
kill_by_name "npm run dev" "NPM Dev Server"
kill_by_name "php -S localhost:8080" "PHP Built-in Server"

echo ""
echo "⏳ Waiting for services to fully stop..."
sleep 3

# Clear any stale processes
pkill -f "artisan serve" 2>/dev/null
pkill -f "npm run dev" 2>/dev/null
pkill -f "php -S localhost:8080" 2>/dev/null

echo "✅ All services stopped."
echo ""

# Start services again
echo "🚀 Starting Console Project..."
echo "=============================="

# Check if start.sh exists and is executable
if [ ! -x "./start.sh" ]; then
    echo "❌ start.sh not found or not executable"
    echo "   Make sure you're in the project root directory"
    echo "   Run: chmod +x start.sh"
    exit 1
fi

# Start the application
exec ./start.sh