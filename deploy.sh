#!/bin/bash

# LMS Deployment Script for cPanel
# This script handles the missing fileinfo extension issue on cPanel

echo "=== LMS Deployment Script ==="
echo ""

# Pull latest code
echo "1. Pulling latest code..."
git pull origin saroj

# Install composer dependencies (ignore fileinfo requirement)
echo ""
echo "2. Installing composer dependencies..."
composer install --ignore-platform-req=ext-fileinfo

# Clear Laravel cache
echo ""
echo "3. Clearing Laravel cache..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Run migrations
echo ""
echo "4. Running migrations..."
php artisan migrate --force

echo ""
echo "=== Deployment Complete ==="
