#!/bin/bash
# Start Laravel server with custom PHP configuration for file uploads
echo "Starting Laravel server with custom PHP configuration..."
echo "Upload limits: 50MB file size, 60MB post size"
php -c php.ini artisan serve --host=0.0.0.0 --port=8000