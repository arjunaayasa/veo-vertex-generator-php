# Veo3 Video Generator - Installation Guide

## Quick Start

### Option 1: Automatic Installation (Recommended)

```bash
cd veo3
./install.sh
```

### Option 2: Manual Installation

```bash
# Install Composer dependencies
composer install

# Create directories
mkdir -p data logs config
chmod 755 api/ includes/ data/ logs/
```

## Google Cloud Setup

### 1. Install Google Cloud SDK

```bash
# macOS
brew install --cask google-cloud-sdk

# Or download from: https://cloud.google.com/sdk/docs/install
```

### 2. Authenticate

**Method A: Using gcloud CLI (Easiest)**
```bash
gcloud auth login
gcloud config set project YOUR_PROJECT_ID
gcloud auth application-default login
```

**Method B: Using Service Account**
1. Create service account in Google Cloud Console
2. Download JSON key
3. Place in `config/service-account-key.json`
4. Update `config/config.php`:
   ```php
   define('CREDENTIALS_PATH', __DIR__ . '/service-account-key.json');
   ```

### 3. Enable APIs

```bash
gcloud services enable aiplatform.googleapis.com
```

Or via Console:
- Go to: https://console.cloud.google.com/apis/library
- Search for "Vertex AI API"
- Click "Enable"

## Configuration

Edit `config/config.php`:

```php
<?php
define('PROJECT_ID', 'your-actual-project-id');  // ← Change this
define('LOCATION', 'us-central1');               // ← Your region
define('CREDENTIALS_PATH', __DIR__ . '/service-account-key.json'); // Optional
```

## Running the Application

### Development Server

```bash
cd veo3
php -S localhost:8000
```

Open: http://localhost:8000

### Production (Apache/Nginx)

**Apache:**
```apache
<VirtualHost *:80>
    ServerName veo3.local
    DocumentRoot /path/to/veo3
    
    <Directory /path/to/veo3>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**Nginx:**
```nginx
server {
    listen 80;
    server_name veo3.local;
    root /path/to/veo3;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
    }
}
```

## Dependencies

The application uses:

- **google/cloud-aiplatform**: Official Google Cloud AI Platform SDK
- **google/auth**: Google Authentication Library

Installed via Composer automatically.

## Troubleshooting

### "Unable to authenticate"

**Solution 1:**
```bash
gcloud auth application-default login
```

**Solution 2:**
```bash
export GOOGLE_APPLICATION_CREDENTIALS="/path/to/service-account-key.json"
```

### "API not enabled"

```bash
gcloud services enable aiplatform.googleapis.com
```

### "Permission denied"

Make sure service account has role:
- Vertex AI User

```bash
gcloud projects add-iam-policy-binding YOUR_PROJECT_ID \
    --member="serviceAccount:YOUR_SA@YOUR_PROJECT.iam.gserviceaccount.com" \
    --role="roles/aiplatform.user"
```

### Composer not found

```bash
# Install Composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
sudo mv composer.phar /usr/local/bin/composer
```

## Testing

### Test Authentication

```bash
gcloud auth application-default print-access-token
```

If this returns a token, authentication is working.

### Test API Access

```bash
curl -H "Authorization: Bearer $(gcloud auth print-access-token)" \
     https://us-central1-aiplatform.googleapis.com/v1/projects/YOUR_PROJECT_ID/locations/us-central1/endpoints
```

## Cost Estimation

Veo 3 pricing (approximate):
- ~$0.10 - $0.20 per second of generated video
- 8-second video ≈ $0.80 - $1.60

Monitor costs at: https://console.cloud.google.com/billing

## Environment Variables (Optional)

Create `.env` file:

```bash
GOOGLE_APPLICATION_CREDENTIALS=/path/to/service-account-key.json
GOOGLE_CLOUD_PROJECT=your-project-id
VERTEX_AI_LOCATION=us-central1
```

## Next Steps

1. ✅ Complete installation
2. ✅ Configure Google Cloud credentials
3. ✅ Start development server
4. 🎬 Generate your first video!

## Support

- Google Cloud Console: https://console.cloud.google.com
- Vertex AI Docs: https://cloud.google.com/vertex-ai/docs
- Veo Documentation: https://cloud.google.com/vertex-ai/generative-ai/docs/video/overview
