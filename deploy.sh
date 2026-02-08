#!/bin/bash

echo "🚀 Starting deployment..."

# Pull latest changes
echo "📥 Pulling latest changes from Git..."
git pull origin main

# Clear cache
echo "🗑️  Clearing Symfony cache..."
rm -rf var/cache/*

# Set proper permissions
echo "🔐 Setting permissions..."
chown -R www-data:www-data .
chmod -R 775 var/

# Optional: Run migrations if needed
# echo "📊 Running database migrations..."
# php bin/console doctrine:migrations:migrate --no-interaction

echo "✅ Deployment complete!"
echo ""
echo "💡 Don't forget to hard refresh your browser (Ctrl+Shift+R)"
