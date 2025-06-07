#!/bin/bash

# Console Project Startup Script
# Usage: bash start.sh
# Prerequisites: PHP 8.1+, Composer, Node.js 18+, npm
# Stops all servers with Ctrl+C
#
# This script starts both the Laravel backend and Vue.js frontend.

set -e

PORT_CHECK() {
    local PORT=$1
    if lsof -i :$PORT &>/dev/null; then
        echo "❌ Port $PORT is already in use. Please free it before running this script."
        exit 1
    fi
}

echo "🚀 Starting Console Project..."
echo "=================================="

# Function to cleanup background processes on script exit
cleanup() {
    echo ""
    echo "🛑 Shutting down servers..."
    if [ ! -z "$BACKEND_PID" ]; then
        echo "Stopping Laravel backend (PID: $BACKEND_PID)..."
        kill $BACKEND_PID 2>/dev/null
    fi
    if [ ! -z "$FRONTEND_PID" ]; then
        echo "Stopping Vue.js frontend (PID: $FRONTEND_PID)..."
        kill $FRONTEND_PID 2>/dev/null
    fi
    echo "✅ All servers stopped."
    exit 0
}

# Set up trap to cleanup on script exit
trap cleanup SIGINT SIGTERM

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed. Please install PHP 8.1 or higher."
    exit 1
fi

# Check if Composer is installed
if ! command -v composer &> /dev/null; then
    echo "❌ Composer is not installed. Please install Composer."
    exit 1
fi

# Check if Node.js is installed
if ! command -v node &> /dev/null; then
    echo "❌ Node.js is not installed. Please install Node.js 18 or higher."
    exit 1
fi

# Check if npm is installed
if ! command -v npm &> /dev/null; then
    echo "❌ npm is not installed. Please install npm."
    exit 1
fi

echo "✅ All dependencies are available."
echo ""

# Setup backend dependencies
cd backend

echo "📦 Setting up backend dependencies..."
if [ ! -d "vendor" ]; then
    echo "Installing Composer dependencies..."
    if ! composer install --no-interaction --prefer-dist --optimize-autoloader; then
        echo "❌ Composer install failed."
        exit 1
    fi
fi

echo "✅ Composer dependencies ready."

# Check if .env exists
if [ ! -f ".env" ]; then
    echo "Creating .env file..."
    cp .env.example .env
    php artisan key:generate
fi

# Run migrations and seeders
echo "Setting up database..."
if ! php artisan migrate --force; then
    echo "❌ Database migration failed."
    exit 1
fi
if ! php artisan db:seed --force; then
    echo "❌ Database seeding failed."
    exit 1
fi

echo "✅ Backend setup complete."
echo ""

# Setup frontend dependencies
cd ../frontend
echo "📦 Setting up frontend dependencies..."
if [ ! -d "node_modules" ]; then
    echo "Installing npm dependencies..."
    if ! npm install; then
        echo "❌ npm install failed."
        exit 1
    fi
fi

# Optional: Check if build exists (for production, e.g., dist folder)
# if [ ! -d "dist" ]; then
#     echo "Building frontend..."
#     if ! npm run build; then
#         echo "❌ Frontend build failed."
#         exit 1
#     fi
# fi

echo "✅ Frontend setup complete."
echo ""

# Port checks before starting servers
PORT_CHECK 8000
PORT_CHECK 3000

# Start Laravel backend in background
cd ../backend
echo "🔧 Starting Laravel backend on http://localhost:8000..."
php artisan serve --host=0.0.0.0 --port=8000 > /dev/null 2>&1 &
BACKEND_PID=$!

# Wait a moment for backend to start
sleep 3

# Check if backend started successfully
if ps -p $BACKEND_PID > /dev/null; then
    echo "✅ Laravel backend started successfully (PID: $BACKEND_PID)"
else
    echo "❌ Failed to start Laravel backend"
    exit 1
fi

# Start Vue.js frontend in background
cd ../frontend
echo "🎨 Starting Vue.js frontend on http://localhost:3000..."
npm run dev > /dev/null 2>&1 &
FRONTEND_PID=$!

# Wait a moment for frontend to start
sleep 3

# Check if frontend started successfully
if ps -p $FRONTEND_PID > /dev/null; then
    echo "✅ Vue.js frontend started successfully (PID: $FRONTEND_PID)"
else
    echo "❌ Failed to start Vue.js frontend"
    cleanup
    exit 1
fi

echo ""
echo "🎉 Console Project is now running!"
echo "=================================="
echo "🌐 Frontend: http://localhost:3000"
echo "🔗 Backend API: http://localhost:8000/api"
echo "📧 Demo Login: admin@maurtiustelecom.mu / password"
echo ""
echo "Press Ctrl+C to stop all servers..."

# Keep the script running and wait for user interrupt
while true; do
    sleep 1
done