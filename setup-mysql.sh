#!/bin/bash

# MySQL Setup Script for Console Project
echo "🗄️  Setting up MySQL for Console Project..."
echo "========================================"

# Check if MySQL is already installed
if command -v mysql &> /dev/null; then
    echo "✅ MySQL is already installed"
else
    echo "📦 Installing MySQL..."
    sudo apt update
    sudo apt install -y mysql-server mysql-client
fi

# Start MySQL service
echo "🔧 Starting MySQL service..."
sudo service mysql start

# Create database
echo "🗃️  Creating console_rag database..."
sudo mysql -e "CREATE DATABASE IF NOT EXISTS console_rag;"
if [ $? -eq 0 ]; then
    echo "✅ Database 'console_rag' created successfully"
else
    echo "❌ Failed to create database"
    exit 1
fi

# Optional: Create a dedicated user (more secure)
echo "👤 Creating dedicated MySQL user (optional)..."
sudo mysql -e "CREATE USER IF NOT EXISTS 'console_user'@'localhost' IDENTIFIED BY 'console_pass';"
sudo mysql -e "GRANT ALL PRIVILEGES ON console_rag.* TO 'console_user'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"

echo ""
echo "🎉 MySQL setup complete!"
echo "=================================="
echo "📊 Database: console_rag"
echo "🔗 Connection: localhost:3306"
echo "👤 User: root (no password) or console_user/console_pass"
echo ""
echo "To use the dedicated user, update backend/.env:"
echo "DB_USERNAME=console_user"
echo "DB_PASSWORD=console_pass"
echo ""
echo "Now you can run: ./start.sh"