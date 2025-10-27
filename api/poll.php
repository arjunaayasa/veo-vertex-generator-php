<?php
/**
 * Polling endpoint for checking video generation status
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Load configuration
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/VertexAIClient.php';

try {
    // Get request body
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['operationName'])) {
        throw new Exception('Operation name is required');
    }
    
    $operationName = $input['operationName'];
    
    // Initialize Vertex AI client
    $client = new VertexAIClient(
        PROJECT_ID,
        LOCATION,
        CREDENTIALS_PATH
    );
    
    // Poll operation status
    $result = $client->pollOperation($operationName);
    
    // Check if operation is complete
    $done = $result['done'] ?? false;
    
    if ($done) {
        // Extract videos from response
        $videos = [];
        if (isset($result['response']['videos'])) {
            foreach ($result['response']['videos'] as $video) {
                $videos[] = [
                    'url' => $video['gcsUri'] ?? null,
                    'mimeType' => $video['mimeType'] ?? 'video/mp4',
                    'bytesBase64Encoded' => $video['bytesBase64Encoded'] ?? null
                ];
            }
        }
        
        // Save to gallery
        saveToGallery($videos);
        
        echo json_encode([
            'done' => true,
            'videos' => $videos,
            'raiFilteredCount' => $result['response']['raiMediaFilteredCount'] ?? 0
        ]);
    } else {
        echo json_encode([
            'done' => false,
            'message' => 'Video generation in progress'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}

/**
 * Save generated videos to gallery
 */
function saveToGallery($videos) {
    $galleryFile = __DIR__ . '/../data/gallery.json';
    
    // Create data directory if it doesn't exist
    if (!is_dir(__DIR__ . '/../data')) {
        mkdir(__DIR__ . '/../data', 0755, true);
    }
    
    // Load existing gallery
    $gallery = [];
    if (file_exists($galleryFile)) {
        $gallery = json_decode(file_get_contents($galleryFile), true) ?? [];
    }
    
    // Add new videos with timestamp
    foreach ($videos as $video) {
        $gallery[] = array_merge($video, [
            'timestamp' => time(),
            'date' => date('Y-m-d H:i:s')
        ]);
    }
    
    // Keep only last 50 videos
    $gallery = array_slice($gallery, -50);
    
    // Save gallery
    file_put_contents($galleryFile, json_encode($gallery, JSON_PRETTY_PRINT));
}
