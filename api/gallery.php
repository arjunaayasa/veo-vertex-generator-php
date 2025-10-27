<?php
/**
 * Gallery endpoint for retrieving generated videos
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

try {
    $galleryFile = __DIR__ . '/../data/gallery.json';
    
    // Check if gallery exists
    if (!file_exists($galleryFile)) {
        echo json_encode([]);
        exit();
    }
    
    // Load gallery
    $gallery = json_decode(file_get_contents($galleryFile), true);
    
    if (!$gallery) {
        echo json_encode([]);
        exit();
    }
    
    // Return gallery in reverse order (newest first)
    echo json_encode(array_reverse($gallery));
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
