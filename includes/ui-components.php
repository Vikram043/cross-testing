<?php
/**
 * Reusable UI Components for eCourts API Tester
 */

class UIComponents {
    
    /**
     * Create parameter input field
     * @param array $param - Parameter definition
     * @param string $value - Current value
     * @return string - HTML for input field
     */
    public static function createParameterInput($param, $value = '') {
        $html = '<div class="mb-3">';
        $html .= '<label for="' . $param['key'] . '" class="form-label">' . $param['label'] . '</label>';
        
        switch ($param['type']) {
            case 'text':
                $html .= '<input type="text" 
                                class="form-control parameter-input" 
                                id="' . $param['key'] . '" 
                                name="' . $param['key'] . '" 
                                value="' . htmlspecialchars($value) . '" 
                                placeholder="' . ($param['placeholder'] ?? '') . '" 
                                ' . ($param['required'] ? 'required' : '') . '
                                ' . (isset($param['pattern']) ? 'pattern="' . $param['pattern'] . '"' : '') . '>';
                break;
                
            case 'number':
                $html .= '<input type="number" 
                                class="form-control parameter-input" 
                                id="' . $param['key'] . '" 
                                name="' . $param['key'] . '" 
                                value="' . htmlspecialchars($value) . '" 
                                ' . ($param['required'] ? 'required' : '') . '>';
                break;
                
            case 'date':
                $html .= '<input type="date" 
                                class="form-control parameter-input" 
                                id="' . $param['key'] . '" 
                                name="' . $param['key'] . '" 
                                value="' . htmlspecialchars($value) . '" 
                                ' . ($param['required'] ? 'required' : '') . '>';
                break;
                
            case 'select':
                $html .= '<select class="form-select parameter-input" 
                                 id="' . $param['key'] . '" 
                                 name="' . $param['key'] . '" 
                                 ' . ($param['required'] ? 'required' : '') . '>';
                if (isset($param['options'])) {
                    foreach ($param['options'] as $option) {
                        $selected = ($value == $option['value']) ? 'selected' : '';
                        $html .= '<option value="' . $option['value'] . '" ' . $selected . '>' . $option['label'] . '</option>';
                    }
                }
                $html .= '</select>';
                break;
                
            case 'textarea':
                $html .= '<textarea class="form-control parameter-input" 
                                  id="' . $param['key'] . '" 
                                  name="' . $param['key'] . '" 
                                  rows="3" 
                                  ' . ($param['required'] ? 'required' : '') . '>' . htmlspecialchars($value) . '</textarea>';
                break;
        }
        
        if (isset($param['description'])) {
            $html .= '<div class="form-text">' . $param['description'] . '</div>';
        }
        
        $html .= '</div>';
        return $html;
    }
    
    /**
     * Create API card component
     * @param array $api - API information
     * @return string - HTML for API card
     */
    public static function createAPICard($api) {
        $categoryColors = [
            'Overview' => 'bg-primary',
            'Case Search' => 'bg-success',
            'Causelist' => 'bg-warning',
            'Caveat Search' => 'bg-info',
            'Establishment' => 'bg-danger',
            'Master Data' => 'bg-secondary'
        ];
        
        $color = $categoryColors[$api['category']] ?? 'bg-dark';
        
        $html = '<div class="col-md-6 col-lg-4">';
        $html .= '<div class="api-card glass-card h-100">';
        $html .= '<div class="position-relative">';
        $html .= '<div class="category-badge ' . $color . ' text-white">' . $api['category'] . '</div>';
        $html .= '</div>';
        $html .= '<div class="api-card-header">';
        $html .= '<h5 class="mb-0"><i class="fas fa-code me-2"></i>' . $api['name'] . '</h5>';
        $html .= '</div>';
        $html .= '<div class="api-card-body d-flex flex-column">';
        $html .= '<p class="text-muted mb-3 flex-grow-1">' . $api['description'] . '</p>';
        $html .= '<div class="mt-auto">';
        $html .= '<div class="d-flex justify-content-between align-items-center">';
        $html .= '<small class="text-muted"><i class="fas fa-hashtag me-1"></i>' . $api['id'] . '</small>';
        $html .= '<a href="api-test.php?api=' . $api['id'] . '" class="btn btn-sm btn-api">';
        $html .= '<i class="fas fa-play me-1"></i> Test Now';
        $html .= '</a>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Create result display panel
     * @param array $result - API response result
     * @return string - HTML for result display
     */
    public static function createResultPanel($result) {
        if (!$result) {
            return '<div class="alert alert-warning">No result to display</div>';
        }
        
        $html = '<div class="result-panel">';
        
        // Status badge
        $statusClass = $result['success'] ? 'success' : 'danger';
        $html .= '<div class="d-flex justify-content-between align-items-center mb-4">';
        $html .= '<h4><i class="fas fa-poll me-2"></i>API Test Result</h4>';
        $html .= '<span class="badge bg-' . $statusClass . '">' . 
                 ($result['success'] ? 'SUCCESS' : 'FAILED') . '</span>';
        $html .= '</div>';
        
        // Basic info
        $html .= '<div class="row mb-4">';
        $html .= '<div class="col-md-3">';
        $html .= '<div class="card bg-light">';
        $html .= '<div class="card-body">';
        $html .= '<h6><i class="fas fa-code me-2"></i>HTTP Status</h6>';
        $html .= '<h3 class="text-' . ($result['http_code'] == 200 ? 'success' : 'danger') . '">' . $result['http_code'] . '</h3>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '<div class="col-md-3">';
        $html .= '<div class="card bg-light">';
        $html .= '<div class="card-body">';
        $html .= '<h6><i class="fas fa-clock me-2"></i>Response Time</h6>';
        $html .= '<h3 class="text-info">' . ($result['response_time'] ?? 'N/A') . '</h3>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '<div class="col-md-3">';
        $html .= '<div class="card bg-light">';
        $html .= '<div class="card-body">';
        $html .= '<h6><i class="fas fa-shield-alt me-2"></i>Token Valid</h6>';
        $html .= '<h3 class="' . ($result['token_valid'] ? 'text-success' : 'text-danger') . '">';
        $html .= $result['token_valid'] ? 'YES' : 'NO';
        $html .= '</h3>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '<div class="col-md-3">';
        $html .= '<div class="card bg-light">';
        $html .= '<div class="card-body">';
        $html .= '<h6><i class="fas fa-database me-2"></i>Data Size</h6>';
        $html .= '<h3 class="text-primary">' . 
                 (isset($result['data']) ? strlen(json_encode($result['data'])) . ' bytes' : 'N/A') . 
                 '</h3>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        // Error display
        if (!$result['success']) {
            $html .= '<div class="alert alert-danger">';
            $html .= '<h5><i class="fas fa-exclamation-triangle me-2"></i>Error Details</h5>';
            $html .= '<p>' . ($result['error'] ?? 'Unknown error') . '</p>';
            
            if (isset($result['raw_response'])) {
                $html .= '<button class="btn btn-sm btn-outline-danger mt-2" type="button" 
                          data-bs-toggle="collapse" data-bs-target="#rawError">View Raw Response</button>';
                $html .= '<div class="collapse mt-2" id="rawError">';
                $html .= '<pre class="bg-dark text-light p-3 rounded"><code>' . 
                         htmlspecialchars(json_encode($result['raw_response'], JSON_PRETTY_PRINT)) . 
                         '</code></pre>';
                $html .= '</div>';
            }
            $html .= '</div>';
        }
        
        // Data display
        if (isset($result['data'])) {
            $html .= '<div class="mb-4">';
            $html .= '<div class="d-flex justify-content-between align-items-center mb-3">';
            $html .= '<h5><i class="fas fa-table me-2"></i>Response Data</h5>';
            $html .= '<div class="btn-group">';
            $html .= '<button class="btn btn-sm btn-outline-primary" onclick="copyToClipboard(\'#responseData\')">Copy</button>';
            $html .= '<button class="btn btn-sm btn-outline-success" onclick="downloadJSON()">Download</button>';
            $html .= '<button class="btn btn-sm btn-outline-info" onclick="toggleView()">Toggle View</button>';
            $html .= '</div>';
            $html .= '</div>';
            
            $html .= '<div id="jsonViewer" class="border rounded">';
            $html .= '<pre id="responseData" class="mb-0 p-3" style="max-height: 500px; overflow-y: auto; background: #f8f9fa;">';
            $html .= htmlspecialchars(json_encode($result['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $html .= '</pre>';
            $html .= '</div>';
            
            // Table view for structured data
            $html .= '<div id="tableView" class="border rounded mt-3 d-none" style="max-height: 500px; overflow-y: auto;">';
            $html .= self::createDataTable($result['data']);
            $html .= '</div>';
            $html .= '</div>';
        }
        
        // Raw response
        if (isset($result['raw_response'])) {
            $html .= '<div class="mb-4">';
            $html .= '<h5><i class="fas fa-file-code me-2"></i>Raw Response</h5>';
            $html .= '<div class="border rounded">';
            $html .= '<pre class="mb-0 p-3" style="max-height: 300px; overflow-y: auto; background: #f8f9fa;">';
            $html .= htmlspecialchars(json_encode($result['raw_response'], JSON_PRETTY_PRINT));
            $html .= '</pre>';
            $html .= '</div>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Create data table from array
     * @param array $data - Data to display
     * @param int $level - Nesting level
     * @return string - HTML table
     */
    private static function createDataTable($data, $level = 0) {
        if (!is_array($data) || empty($data)) {
            return '<div class="p-3 text-muted">No data available</div>';
        }
        
        // If it's a numeric array (list), show as cards
        if (array_keys($data) === range(0, count($data) - 1)) {
            $html = '<div class="p-3">';
            foreach ($data as $index => $item) {
                $html .= '<div class="card mb-2">';
                $html .= '<div class="card-header">Item ' . ($index + 1) . '</div>';
                $html .= '<div class="card-body">';
                $html .= self::createDataTable($item, $level + 1);
                $html .= '</div>';
                $html .= '</div>';
            }
            $html .= '</div>';
            return $html;
        }
        
        // Associative array - show as table
        $html = '<table class="table table-sm table-hover mb-0">';
        $html .= '<thead class="table-light"><tr><th>Key</th><th>Value</th></tr></thead>';
        $html .= '<tbody>';
        
        foreach ($data as $key => $value) {
            $html .= '<tr>';
            $html .= '<td><strong>' . htmlspecialchars($key) . '</strong></td>';
            $html .= '<td>';
            
            if (is_array($value)) {
                if (empty($value)) {
                    $html .= '<span class="text-muted">Empty array</span>';
                } else {
                    $html .= '<button class="btn btn-sm btn-outline-primary" type="button" 
                              data-bs-toggle="collapse" data-bs-target="#collapse-' . $level . '-' . htmlspecialchars($key) . '">
                              Show ' . count($value) . ' items</button>';
                    $html .= '<div class="collapse mt-2" id="collapse-' . $level . '-' . htmlspecialchars($key) . '">';
                    $html .= self::createDataTable($value, $level + 1);
                    $html .= '</div>';
                }
            } elseif (is_bool($value)) {
                $html .= '<span class="badge bg-' . ($value ? 'success' : 'danger') . '">' . ($value ? 'true' : 'false') . '</span>';
            } elseif (is_null($value)) {
                $html .= '<span class="text-muted">null</span>';
            } elseif ($value === '') {
                $html .= '<span class="text-muted">(empty)</span>';
            } else {
                $html .= htmlspecialchars((string)$value);
            }
            
            $html .= '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</tbody>';
        $html .= '</table>';
        
        return $html;
    }
    
    /**
     * Create status badge
     * @param int $statusCode - HTTP status code
     * @return string - HTML badge
     */
    public static function createStatusBadge($statusCode) {
        $statusMap = [
            200 => ['class' => 'success', 'label' => 'OK'],
            400 => ['class' => 'danger', 'label' => 'Bad Request'],
            403 => ['class' => 'danger', 'label' => 'Forbidden'],
            404 => ['class' => 'warning', 'label' => 'Not Found'],
            500 => ['class' => 'danger', 'label' => 'Server Error'],
            504 => ['class' => 'warning', 'label' => 'Timeout'],
        ];
        
        $status = $statusMap[$statusCode] ?? ['class' => 'secondary', 'label' => $statusCode];
        
        return '<span class="badge bg-' . $status['class'] . '">' . $statusCode . ' ' . $status['label'] . '</span>';
    }
    
    /**
     * Create API test history item
     * @param array $test - Test history data
     * @return string - HTML list item
     */
    public static function createHistoryItem($test) {
        $html = '<div class="list-group-item">';
        $html .= '<div class="d-flex w-100 justify-content-between">';
        $html .= '<h6 class="mb-1">' . $test['api_name'] . '</h6>';
        $html .= '<small class="text-muted">' . $test['timestamp'] . '</small>';
        $html .= '</div>';
        $html .= '<p class="mb-1">' . $test['description'] . '</p>';
        $html .= '<div class="d-flex justify-content-between align-items-center">';
        $html .= '<small>' . self::createStatusBadge($test['status_code']) . '</small>';
        $html .= '<button class="btn btn-sm btn-outline-primary" onclick="loadTestResult(' . $test['id'] . ')">View</button>';
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Create loading spinner
     * @param string $message - Loading message
     * @return string - HTML for spinner
     */
    public static function createLoadingSpinner($message = 'Loading...') {
        $html = '<div class="text-center py-5">';
        $html .= '<div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">';
        $html .= '<span class="visually-hidden">Loading...</span>';
        $html .= '</div>';
        $html .= '<p class="mt-3">' . $message . '</p>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Create empty state
     * @param string $icon - FontAwesome icon
     * @param string $title - Title text
     * @param string $message - Message text
     * @return string - HTML for empty state
     */
    public static function createEmptyState($icon, $title, $message) {
        $html = '<div class="text-center py-5">';
        $html .= '<div class="mb-3">';
        $html .= '<i class="fas ' . $icon . ' fa-4x text-muted"></i>';
        $html .= '</div>';
        $html .= '<h4 class="text-muted mb-3">' . $title . '</h4>';
        $html .= '<p class="text-muted">' . $message . '</p>';
        $html .= '</div>';
        
        return $html;
    }
}
?>