# MySQL Installation Guide for Console Project

## Ubuntu/Debian
```bash
# Update package list
sudo apt update

# Install MySQL server
sudo apt install mysql-server mysql-client

# Start MySQL service
sudo systemctl start mysql
sudo systemctl enable mysql

# Secure MySQL installation (optional but recommended)
sudo mysql_secure_installation

# Create database for the project
sudo mysql -u root -p
CREATE DATABASE console_rag;
EXIT;
```

## CentOS/RHEL/Rocky Linux
```bash
# Install MySQL repository
sudo dnf install mysql-server mysql

# Start MySQL service
sudo systemctl start mysqld
sudo systemctl enable mysqld

# Get temporary root password
sudo grep 'temporary password' /var/log/mysqld.log

# Secure installation
sudo mysql_secure_installation

# Create database
mysql -u root -p
CREATE DATABASE console_rag;
EXIT;
```

## Windows
1. Download MySQL Installer from: https://dev.mysql.com/downloads/installer/
2. Run the installer and choose "Server only" or "Full" installation
3. Follow the setup wizard
4. Set root password
5. Start MySQL service from Services panel
6. Use MySQL Command Line Client or phpMyAdmin to create database:
   ```sql
   CREATE DATABASE console_rag;
   ```

## macOS
```bash
# Using Homebrew
brew install mysql

# Start MySQL service
brew services start mysql

# Secure installation
mysql_secure_installation

# Create database
mysql -u root -p
CREATE DATABASE console_rag;
EXIT;
```

## Docker (Alternative)
```bash
# Run MySQL in Docker container
docker run --name console-mysql \
  -e MYSQL_ROOT_PASSWORD=password \
  -e MYSQL_DATABASE=console_rag \
  -p 3306:3306 \
  -d mysql:8.0

# The database will be accessible at localhost:3306
# Update .env file with: DB_PASSWORD=password
```

## Verification
After installation, verify MySQL is working:
```bash
mysql -u root -p
SHOW DATABASES;
```

You should see the `console_rag` database listed.

## Configuration
The project is configured to use:
- **Host**: 127.0.0.1 (localhost)
- **Port**: 3306 (default)
- **Database**: console_rag
- **Username**: root
- **Password**: (empty by default, set during installation)

If you used a password during MySQL setup, update the `.env` file:
```
DB_PASSWORD=your_mysql_password
```