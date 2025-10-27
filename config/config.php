<?php
/**
 * Configuration file for Veo3 Video Generator
 * 
 * IMPORTANT: Update these values with your Google Cloud credentials
 */

// Google Cloud Project Configuration
define('PROJECT_ID', 'your-project-id');
define('LOCATION', 'us-central1');

// Path to service account credentials JSON file
// You can also use gcloud CLI authentication instead
define('CREDENTIALS_PATH', __DIR__ . '/service-account-key.json');

// Application Configuration
define('APP_NAME', 'Veo3 Video Generator');
define('APP_VERSION', '1.0.0');

// Storage Configuration
define('STORAGE_BUCKET', 'gs://your-bucket-name');

// API Configuration
define('API_TIMEOUT', 300); // 5 minutes
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB

// Error Reporting
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Create logs directory if it doesn't exist
if (!is_dir(__DIR__ . '/../logs')) {
    mkdir(__DIR__ . '/../logs', 0755, true);
}
