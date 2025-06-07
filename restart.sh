#!/bin/bash

# Console Project Restart Script
# This script calls stop.sh and then start.sh for clean restart

echo "🔄 Restarting Console Project..."
echo "================================="

# Check if stop.sh exists and is executable
if [ ! -x "./stop.sh" ]; then
    echo "❌ stop.sh not found or not executable"
    echo "   Make sure you're in the project root directory"
    echo "   Run: chmod +x stop.sh"
    exit 1
fi

# Check if start.sh exists and is executable
if [ ! -x "./start.sh" ]; then
    echo "❌ start.sh not found or not executable"
    echo "   Make sure you're in the project root directory"
    echo "   Run: chmod +x start.sh"
    exit 1
fi

# Stop all services first
echo "📍 Step 1: Stopping all services..."
./stop.sh

# Wait a moment for complete shutdown
echo ""
echo "⏳ Waiting for complete shutdown..."
sleep 2

# Start all services
echo ""
echo "📍 Step 2: Starting all services..."
exec ./start.sh