<?php
/**
 * Process API Test Requests - AJAX Handler
 */

require_once 'config/config.php';
require_once 'includes/api-handler.php';
require_once 'includes/validation.php';

// Set JSON headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Initialize response array
$response = ['success' => false, 'error' => 'Invalid request'];

try {
    // Get action from request
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    switch ($action) {
        case 'test_api':
            handleTestAPI();
            break;
            
        case 'get_api_parameters':
            handleGetAPIParameters();
            break;
            
        case 'get_api_info':
            handleGetAPIInfo();
            break;
            
        case 'validate_parameters':
            handleValidateParameters();
            break;
            
        default:
            $response['error'] = 'Unknown action';
            echo json_encode($response);
            exit;
    }
} catch (Exception $e) {
    $response['error'] = 'Server error: ' . $e->getMessage();
    echo json_encode($response);
    exit;
}

/**
 * Handle API test request
 */
function handleTestAPI() {
    $apiName = $_POST['api'] ?? '';
    $params = json_decode($_POST['params'] ?? '[]', true) ?: [];
    
    // Validate input
    if (empty($apiName)) {
        echo json_encode(['success' => false, 'error' => 'API name is required']);
        exit;
    }
    
    // Validate parameters
    $validation = ValidationHandler::validateAPIParameters($apiName, $params);
    if (!$validation['valid']) {
        echo json_encode(['success' => false, 'error' => implode(', ', $validation['errors'])]);
        exit;
    }
    
    // Initialize API handler
    $apiHandler = new APIHandler();
    
    // Call API
    $result = $apiHandler->callAPI($apiName, $params);
    
    // Add additional info
    $result['api_name'] = $apiName;
    $result['parameters'] = $params;
    $result['test_timestamp'] = date('Y-m-d H:i:s');
    
    echo json_encode($result);
    exit;
}

/**
 * Handle get API parameters request
 */
function handleGetAPIParameters() {
    $apiName = $_POST['api'] ?? '';
    
    if (empty($apiName)) {
        echo json_encode(['success' => false, 'error' => 'API name is required']);
        exit;
    }
    
    $apiHandler = new APIHandler();
    $apiInfo = $apiHandler->getAPIInfo($apiName);
    
    if ($apiInfo['endpoint']) {
        echo json_encode([
            'success' => true,
            'parameters' => $apiInfo['parameters'],
            'info' => [
                'name' => $apiInfo['name'],
                'description' => $apiInfo['description'],
                'endpoint' => BASE_URL . $apiInfo['endpoint']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'API not found']);
    }
    exit;
}

/**
 * Handle get API info request
 */
function handleGetAPIInfo() {
    $apiName = $_POST['api'] ?? '';
    
    if (empty($apiName)) {
        echo json_encode(['success' => false, 'error' => 'API name is required']);
        exit;
    }
    
    $apiHandler = new APIHandler();
    $apiInfo = $apiHandler->getAPIInfo($apiName);
    
    echo json_encode([
        'success' => true,
        'data' => $apiInfo
    ]);
    exit;
}

/**
 * Handle validate parameters request
 */
function handleValidateParameters() {
    $apiName = $_POST['api'] ?? '';
    $params = json_decode($_POST['params'] ?? '[]', true) ?: [];
    
    $validation = ValidationHandler::validateAPIParameters($apiName, $params);
    
    echo json_encode($validation);
    exit;
}
?>