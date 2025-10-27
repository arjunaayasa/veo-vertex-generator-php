#!/bin/bash

echo "🚀 Installing Veo3 Video Generator dependencies..."

# Check if composer is installed
if ! command -v composer &> /dev/null
then
    echo "❌ Composer not found. Installing Composer..."
    
    # Download and install Composer
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php
    php -r "unlink('composer-setup.php');"
    
    # Move to /usr/local/bin
    sudo mv composer.phar /usr/local/bin/composer
    
    echo "✅ Composer installed successfully"
else
    echo "✅ Composer already installed"
fi

# Install PHP dependencies
echo "📦 Installing Google Cloud libraries..."
composer install

# Create necessary directories
echo "📁 Creating directories..."
mkdir -p data
mkdir -p logs
mkdir -p config

# Set permissions
echo "🔐 Setting permissions..."
chmod 755 api/
chmod 755 includes/
chmod 755 data/
chmod 755 logs/
chmod 644 data/gallery.json

# Initialize gallery
if [ ! -f "data/gallery.json" ]; then
    echo "[]" > data/gallery.json
fi

echo ""
echo "✅ Installation complete!"
echo ""
echo "📝 Next steps:"
echo "1. Edit config/config.php with your Google Cloud credentials"
echo "2. Run: php -S localhost:8000"
echo "3. Open http://localhost:8000 in your browser"
echo ""
echo "🔑 Authentication options:"
echo "   Option A: gcloud auth login"
echo "   Option B: Place service-account-key.json in config/"
echo ""
