-- Fix MySQL authentication for development
-- Run this with: sudo mysql < fix-mysql.sql

-- Allow root to login without password for development
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '';
FLUSH PRIVILEGES;

-- Create the database
CREATE DATABASE IF NOT EXISTS console_rag;

-- Show success message
SELECT 'MySQL configured successfully for Console project!' as Status;