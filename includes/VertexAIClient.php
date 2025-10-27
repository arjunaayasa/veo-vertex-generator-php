<?php
/**
 * Vertex AI Client for Veo3 Video Generation
 * Using Google Cloud AI Platform API
 */

use Google\Cloud\AIPlatform\V1\PredictionServiceClient;
use Google\Cloud\AIPlatform\V1\EndpointName;
use Google\Protobuf\Value;
use Google\Protobuf\Struct;
use Google\ApiCore\ApiException;

class VertexAIClient {
    private $projectId;
    private $location;
    private $credentialsPath;
    private $model;
    private $client;
    private $accessToken;
    private $serviceAccountData;
    
    // Available models
    private $availableModels = [
        'veo-3.1-generate-preview',
        'veo-3.1-fast-generate-preview',
        'veo-3.0-generate-001',
        'veo-3.0-fast-generate-001',
        'veo-2.0-generate-001',
        'veo-2.0-generate-exp',
        'veo-2.0-generate-preview'
    ];
    
    public function __construct($projectId, $location, $credentialsPath = null, $model = 'veo-3.0-generate-001') {
        $this->projectId = $projectId;
        $this->location = $location;
        $this->credentialsPath = $credentialsPath;
        $this->model = $model;
        $this->accessToken = null;
        $this->serviceAccountData = null;
        
        // Validate model
        if (!in_array($this->model, $this->availableModels)) {
            throw new Exception('Invalid model: ' . $this->model);
        }
        
        // Initialize client
        $this->initializeClient();
    }
    
    public function setAccessToken($token) {
        $this->accessToken = $token;
    }
    
    public function setServiceAccountJson($json) {
        $this->serviceAccountData = json_decode($json, true);
        if (!$this->serviceAccountData) {
            throw new Exception('Invalid service account JSON');
        }
    }
    
    public function setModel($model) {
        if (!in_array($model, $this->availableModels)) {
            throw new Exception('Invalid model: ' . $model);
        }
        $this->model = $model;
    }
    
    public function getAvailableModels() {
        return $this->availableModels;
    }
    
    /**
     * Initialize Prediction Service Client
     */
    private function initializeClient() {
        $options = [
            'apiEndpoint' => $this->location . '-aiplatform.googleapis.com'
        ];
        
        // Use service account credentials if provided
        if ($this->credentialsPath && file_exists($this->credentialsPath)) {
            $options['credentials'] = $this->credentialsPath;
        }
        
        try {
            $this->client = new PredictionServiceClient($options);
        } catch (Exception $e) {
            // Fallback to using gcloud auth with REST API
            $this->client = null;
        }
    }
    
    /**
     * Get access token for REST API fallback
     */
    private function getAccessToken() {
        // Method 1: Use provided access token
        if ($this->accessToken) {
            return $this->accessToken;
        }
        
        // Method 2: Generate from service account JSON
        if ($this->serviceAccountData) {
            return $this->getAccessTokenFromServiceAccount($this->serviceAccountData);
        }
        
        // Method 3: Try gcloud CLI
        $output = shell_exec('gcloud auth print-access-token 2>&1');
        if ($output && !strpos($output, 'ERROR')) {
            return trim($output);
        }
        
        // Method 4: Use credentials file if provided
        if ($this->credentialsPath && file_exists($this->credentialsPath)) {
            try {
                putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $this->credentialsPath);
                
                $credentials = new \Google\Auth\Credentials\ServiceAccountCredentials(
                    'https://www.googleapis.com/auth/cloud-platform',
                    $this->credentialsPath
                );
                
                $authToken = $credentials->fetchAuthToken();
                return $authToken['access_token'];
            } catch (Exception $e) {
                // Continue to next method
            }
        }
        
        throw new Exception('Unable to authenticate. Please provide valid credentials.');
    }
    
    /**
     * Get access token from service account data
     */
    private function getAccessTokenFromServiceAccount($credentials) {
        // Create JWT
        $now = time();
        $jwt = [
            'iss' => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/cloud-platform',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now
        ];
        
        // Encode header and payload
        $jwtHeader = $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $jwtPayload = $this->base64UrlEncode(json_encode($jwt));
        
        // Sign
        $signatureInput = $jwtHeader . '.' . $jwtPayload;
        $signature = '';
        openssl_sign($signatureInput, $signature, $credentials['private_key'], 'SHA256');
        $jwtSignature = $this->base64UrlEncode($signature);
        
        $signedJwt = $signatureInput . '.' . $jwtSignature;
        
        // Exchange JWT for access token
        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $signedJwt
        ]));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('Failed to get access token from service account');
        }
        
        $tokenData = json_decode($response, true);
        return $tokenData['access_token'];
    }
    
    private function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Generate video from text prompt
     */
    public function generateTextToVideo($params) {
        $instances = [
            [
                'prompt' => $params['prompt']
            ]
        ];
        
        $parameters = [
            'durationSeconds' => $params['duration'],
            'aspectRatio' => $params['aspectRatio'],
            'resolution' => $params['resolution'],
            'sampleCount' => $params['sampleCount'],
            'compressionQuality' => $params['compressionQuality'] ?? 'optimized',
            'enhancePrompt' => $params['enhancePrompt'],
            'personGeneration' => $params['personGeneration'] ?? 'allow_adult'
        ];
        
        // Add generateAudio only for Veo 3+ models
        if (strpos($this->model, 'veo-3') === 0) {
            $parameters['generateAudio'] = $params['generateAudio'];
        }
        
        // Add optional negative prompt
        if (!empty($params['negativePrompt'])) {
            $parameters['negativePrompt'] = $params['negativePrompt'];
        }
        
        return $this->predict($instances, $parameters);
    }
    
    /**
     * Generate video from image
     */
    public function generateImageToVideo($params) {
        $instances = [
            [
                'image' => [
                    'bytesBase64Encoded' => $params['image'],
                    'mimeType' => $params['mimeType']
                ]
            ]
        ];
        
        // Add optional prompt
        if (!empty($params['prompt'])) {
            $instances[0]['prompt'] = $params['prompt'];
        }
        
        $parameters = [
            'durationSeconds' => $params['duration'],
            'aspectRatio' => $params['aspectRatio'],
            'resolution' => $params['resolution'],
            'resizeMode' => $params['resizeMode'],
            'sampleCount' => $params['sampleCount'] ?? 1,
            'compressionQuality' => $params['compressionQuality'] ?? 'optimized',
            'personGeneration' => $params['personGeneration'] ?? 'allow_adult'
        ];
        
        // Add generateAudio only for Veo 3+ models
        if (strpos($this->model, 'veo-3') === 0) {
            $parameters['generateAudio'] = $params['generateAudio'];
        }
        
        return $this->predict($instances, $parameters);
    }
    
    /**
     * Make prediction request to Vertex AI
     */
    private function predict($instances, $parameters) {
        // Use REST API approach for Veo (since it uses predictLongRunning)
        $url = sprintf(
            'https://%s-aiplatform.googleapis.com/v1/projects/%s/locations/%s/publishers/google/models/%s:predictLongRunning',
            $this->location,
            $this->projectId,
            $this->location,
            $this->model
        );
        
        $requestBody = [
            'instances' => $instances,
            'parameters' => $parameters
        ];
        
        // Get access token
        $accessToken = $this->getAccessToken();
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBody));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
            'User-Agent: Veo3-PHP-Client/1.0'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            throw new Exception('cURL error: ' . $curlError);
        }
        
        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMessage = $errorData['error']['message'] ?? 'Unknown error';
            throw new Exception('Vertex AI error: ' . $errorMessage . ' (HTTP ' . $httpCode . ')');
        }
        
        $result = json_decode($response, true);
        
        if (!$result || !isset($result['name'])) {
            throw new Exception('Invalid response from Vertex AI');
        }
        
        return $result;
    }
    
    /**
     * Poll operation status
     */
    public function pollOperation($operationName) {
        $url = sprintf(
            'https://%s-aiplatform.googleapis.com/v1/%s',
            $this->location,
            $operationName
        );
        
        // Get access token
        $accessToken = $this->getAccessToken();
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
            'User-Agent: Veo3-PHP-Client/1.0'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            throw new Exception('cURL error: ' . $curlError);
        }
        
        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMessage = $errorData['error']['message'] ?? 'Unknown error';
            throw new Exception('Vertex AI error: ' . $errorMessage . ' (HTTP ' . $httpCode . ')');
        }
        
        $result = json_decode($response, true);
        
        if (!$result) {
            throw new Exception('Invalid response from Vertex AI');
        }
        
        return $result;
    }
}
