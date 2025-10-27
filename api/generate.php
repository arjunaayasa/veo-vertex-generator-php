<?php
/**
 * Veo3 Video Generation API Handler
 * 
 * This script handles video generation requests to Google Cloud Vertex AI
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
    
    if (!$input) {
        throw new Exception('Invalid request body');
    }
    
    // Validate credentials from user input
    $projectId = $input['projectId'] ?? null;
    $accessToken = $input['accessToken'] ?? null;
    $serviceAccountJson = $input['serviceAccountJson'] ?? null;
    $location = $input['location'] ?? 'us-central1';
    
    if (!$projectId) {
        throw new Exception('Project ID is required');
    }
    
    if (!$accessToken && !$serviceAccountJson) {
        throw new Exception('Either Access Token or Service Account JSON is required');
    }
    
    // Validate required fields
    $type = $input['mode'] ?? 'text-to-video';
    $model = $input['model'] ?? 'veo-3.0-generate-001';
    
    // Initialize Vertex AI client with user credentials
    $client = new VertexAIClient(
        $projectId,
        $location,
        null, // No credentials file
        $model
    );
    
    // Set access token if provided
    if ($accessToken) {
        $client->setAccessToken($accessToken);
    } else if ($serviceAccountJson) {
        $client->setServiceAccountJson($serviceAccountJson);
    }
    
    // Prepare request based on type
    if ($type === 'text-to-video') {
        $result = handleTextToVideo($client, $input);
    } elseif ($type === 'image-to-video') {
        $result = handleImageToVideo($client, $input);
    } else {
        throw new Exception('Invalid generation type');
    }
    
    // Return operation name
    echo json_encode([
        'success' => true,
        'operationName' => $result['name'],
        'message' => 'Video generation started'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}

/**
 * Handle text-to-video generation
 */
function handleTextToVideo($client, $input) {
    $prompt = $input['prompt'] ?? '';
    
    if (empty($prompt)) {
        throw new Exception('Prompt is required for text-to-video generation');
    }
    
    $params = [
        'prompt' => $prompt,
        'negativePrompt' => $input['negativePrompt'] ?? null,
        'duration' => $input['duration'] ?? 8,
        'aspectRatio' => $input['aspectRatio'] ?? '16:9',
        'resolution' => $input['resolution'] ?? '1080p',
        'sampleCount' => $input['sampleCount'] ?? 1,
        'compressionQuality' => $input['compressionQuality'] ?? 'optimized',
        'enhancePrompt' => $input['enhancePrompt'] ?? true,
        'generateAudio' => $input['generateAudio'] ?? true,
        'personGeneration' => $input['enableAdult'] ? 'allow_adult' : 'dont_allow'
    ];
    
    return $client->generateTextToVideo($params);
}

/**
 * Handle image-to-video generation
 */
function handleImageToVideo($client, $input) {
    $image = $input['image'] ?? '';
    $mimeType = $input['mimeType'] ?? 'image/jpeg';
    
    if (empty($image)) {
        throw new Exception('Image is required for image-to-video generation');
    }
    
    $params = [
        'image' => $image,
        'mimeType' => $mimeType,
        'prompt' => $input['prompt'] ?? null,
        'duration' => $input['duration'] ?? 8,
        'aspectRatio' => $input['aspectRatio'] ?? '16:9',
        'resolution' => $input['resolution'] ?? '1080p',
        'resizeMode' => $input['resizeMode'] ?? 'pad',
        'sampleCount' => $input['sampleCount'] ?? 1,
        'compressionQuality' => $input['compressionQuality'] ?? 'optimized',
        'generateAudio' => $input['generateAudio'] ?? true,
        'personGeneration' => $input['enableAdult'] ? 'allow_adult' : 'dont_allow'
    ];
    
    return $client->generateImageToVideo($params);
}
