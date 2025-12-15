<?php
/**
 * Main API Handler for eCourts API Tester
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/encryption.php';
require_once __DIR__ . '/validation.php';

class APIHandler {
    
    private $accessToken = null;
    private $tokenExpiry = null;
    
    /**
     * Get OAuth2 access token from NAPIX
     * @return string|false - Access token or false on failure
     */
    public function getAccessToken() {
        // Check if token is still valid
        if ($this->accessToken && $this->tokenExpiry > time()) {
            return $this->accessToken;
        }
        
        $url = BASE_URL . 'oauth2/token';
        
        $postData = http_build_query([
            'grant_type' => 'client_credentials',
            'scope' => 'napix'
        ]);
        
        $headers = [
            'Authorization: Basic ' . base64_encode(NAPIX_CLIENT_ID . ':' . NAPIX_CLIENT_SECRET),
            'Content-Type: application/x-www-form-urlencoded'
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => 30
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($response && $httpCode === 200) {
            $data = json_decode($response, true);
            if (isset($data['access_token'])) {
                $this->accessToken = $data['access_token'];
                $this->tokenExpiry = time() + $data['expires_in'] - 300; // 5-minute buffer
                
                // Store in session for reuse
                $_SESSION['access_token'] = $this->accessToken;
                $_SESSION['token_expiry'] = $this->tokenExpiry;
                
                return $this->accessToken;
            }
        }
        
        error_log("Failed to get access token. HTTP Code: $httpCode, Error: $error");
        return false;
    }
    
    /**
     * Call eCourts API
     * @param string $apiName - API endpoint name
     * @param array $params - API parameters
     * @param string $method - HTTP method (GET/POST)
     * @return array - Response data
     */
    public function callAPI($apiName, $params = [], $method = 'GET') {
        global $apiEndpoints;
        
        // Validate API name
        if (!isset($apiEndpoints[$apiName])) {
            return [
                'success' => false,
                'error' => 'Invalid API endpoint',
                'http_code' => 400
            ];
        }
        
        // Get access token
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return [
                'success' => false,
                'error' => 'Failed to obtain access token',
                'http_code' => 401
            ];
        }
        
        // Build parameter string
        $paramString = EncryptionHandler::buildParameterString($params);
        
        // Encrypt request string
        $requestStr = EncryptionHandler::encryptRequest($paramString);
        $requestStr = urlencode($requestStr);
        
        // Generate request token (hash)
        $requestToken = EncryptionHandler::generateHash($paramString);
        
        // Build URL
        $endpoint = $apiEndpoints[$apiName];
        $url = BASE_URL . $endpoint . 
               '?dept_id=' . DEPT_ID . 
               '&request_str=' . $requestStr . 
               '&request_token=' . $requestToken . 
               '&version=' . API_VERSION;
        
        // Set headers
        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Accept: application/json',
            'Content-Type: application/json'
        ];
        
        // Initialize cURL
        $ch = curl_init();
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_FOLLOWLOCATION => true
        ];
        
        // Set method-specific options
        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;
            if (!empty($params)) {
                $options[CURLOPT_POSTFIELDS] = json_encode($params);
            }
        }
        
        curl_setopt_array($ch, $options);
        
        // Execute request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Process response
        if ($response) {
            $responseData = json_decode($response, true);
            
            if ($responseData && isset($responseData['response_str'])) {
                // Decrypt response
                $decryptedResponse = EncryptionHandler::decryptResponse($responseData['response_str']);
                $decryptedData = json_decode($decryptedResponse, true);
                
                // Verify response token
                $isValid = EncryptionHandler::verifyResponseToken(
                    $decryptedResponse,
                    $responseData['response_token'] ?? ''
                );
                
                return [
                    'success' => true,
                    'http_code' => $httpCode,
                    'data' => $decryptedData,
                    'raw_response' => $responseData,
                    'token_valid' => $isValid,
                    'response_time' => date('Y-m-d H:i:s')
                ];
            } else {
                return [
                    'success' => false,
                    'http_code' => $httpCode,
                    'error' => $responseData['error'] ?? 'Invalid response format',
                    'raw_response' => $responseData
                ];
            }
        }
        
        return [
            'success' => false,
            'http_code' => $httpCode,
            'error' => $error ?: 'No response from server',
            'response_time' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Get API information for UI
     * @param string $apiName - API endpoint name
     * @return array - API metadata
     */
    public function getAPIInfo($apiName) {
        global $apiEndpoints, $apiParameters;
        
        $info = [
            'name' => $apiName,
            'endpoint' => $apiEndpoints[$apiName] ?? null,
            'parameters' => $apiParameters[$apiName] ?? [],
            'category' => $this->getAPICategory($apiName),
            'description' => $this->getAPIDescription($apiName)
        ];
        
        return $info;
    }
    
    /**
     * Get all APIs grouped by category
     * @return array - Categorized APIs
     */
    public function getAllAPIs() {
        global $apiCategories, $apiEndpoints;
        
        $categorized = [];
        foreach ($apiCategories as $category => $apis) {
            $categorized[$category] = [];
            foreach ($apis as $api) {
                if (isset($apiEndpoints[$api])) {
                    $categorized[$category][] = [
                        'id' => $api,
                        'name' => $this->formatAPIName($api),
                        'description' => $this->getAPIDescription($api)
                    ];
                }
            }
        }
        
        return $categorized;
    }
    
    /**
     * Format API name for display
     * @param string $apiName - Internal API name
     * @return string - Formatted display name
     */
    private function formatAPIName($apiName) {
        return ucwords(str_replace('_', ' ', $apiName));
    }
    
    /**
     * Get API category
     * @param string $apiName - Internal API name
     * @return string - Category name
     */
    private function getAPICategory($apiName) {
        global $apiCategories;
        
        foreach ($apiCategories as $category => $apis) {
            if (in_array($apiName, $apis)) {
                return $category;
            }
        }
        
        return 'Other';
    }
    
    /**
     * Get API description
     * @param string $apiName - Internal API name
     * @return string - Description
     */
    private function getAPIDescription($apiName) {
        $descriptions = [
            'overview' => 'Get details about current eCourts API specification',
            'search_by_cnr' => 'Get complete case history by CNR number',
            'show_business' => 'Get business transacted on a particular date',
            'show_orders' => 'View Order or Judgment delivered on a particular date',
            'current_status' => 'Get current status of case(s) by CNR number(s)',
            'search_by_case_number' => 'Search cases by Case Registration Number',
            'search_by_filing_number' => 'Search cases by Case Filing Number',
            'search_by_party_name' => 'Search cases by party name',
            'search_by_advocate_name' => 'Search cases by advocate name',
            'search_by_advocate_bar' => 'Search cases by advocate bar registration number',
            'search_by_act' => 'Search cases by Act',
            'causelist_bench' => 'Get list of benches based on causelist date',
            'causelist_details' => 'Get cases listed (Causelist) on a particular date',
            'show_causelist' => 'View Causelist delivered on a particular date',
            'caveat_search' => 'Search caveats by Caveator/Caveatee name',
            'caveat_history' => 'Get complete history of caveat by Caveat Code',
            'state_master' => 'Get list of states and state codes',
            'district_master' => 'Get list of districts for a state',
            'bench_master' => 'Get list of benches of High Court',
            'act_master' => 'Get list of Acts with National Act Codes',
            'case_type_master' => 'Get list of Case Types and Case Type Codes'
        ];
        
        return $descriptions[$apiName] ?? 'No description available';
    }
}
?>