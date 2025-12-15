<?php
/**
 * Input Validation Functions for eCourts API
 */

class ValidationHandler {
    
    /**
     * Validate CNR number (16 characters alphanumeric)
     * @param string $cnr - CNR number
     * @return bool - True if valid
     */
    public static function validateCNR($cnr) {
        return preg_match('/^[A-Z0-9]{16}$/', $cnr);
    }
    
    /**
     * Validate date format (yyyy-mm-dd)
     * @param string $date - Date string
     * @return bool - True if valid
     */
    public static function validateDate($date) {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date);
    }
    
    /**
     * Validate establishment code (6 characters alphanumeric)
     * @param string $estCode - Establishment code
     * @return bool - True if valid
     */
    public static function validateEstCode($estCode) {
        return preg_match('/^[A-Z0-9]{6}$/', $estCode);
    }
    
    /**
     * Validate year (4 digits)
     * @param string $year - Year
     * @return bool - True if valid
     */
    public static function validateYear($year) {
        return preg_match('/^\d{4}$/', $year) && $year >= 1900 && $year <= date('Y');
    }
    
    /**
     * Validate case type (numeric)
     * @param string $caseType - Case type
     * @return bool - True if valid
     */
    public static function validateCaseType($caseType) {
        return is_numeric($caseType);
    }
    
    /**
     * Validate registration/filing number (max 7 digits)
     * @param string $number - Registration/filing number
     * @return bool - True if valid
     */
    public static function validateRegFilingNumber($number) {
        return preg_match('/^\d{1,7}$/', $number);
    }
    
    /**
     * Validate name string (minimum 3 characters)
     * @param string $name - Name string
     * @return bool - True if valid
     */
    public static function validateName($name) {
        return strlen(trim($name)) >= 3;
    }
    
    /**
     * Validate pending/disposed flag (P or D)
     * @param string $pendDisp - Pending/disposed flag
     * @return bool - True if valid
     */
    public static function validatePendDisp($pendDisp) {
        return in_array(strtoupper($pendDisp), ['P', 'D']);
    }
    
    /**
     * Validate search type (A or S)
     * @param string $searchType - Search type
     * @return bool - True if valid
     */
    public static function validateSearchType($searchType) {
        return in_array(strtoupper($searchType), ['A', 'S']);
    }
    
    /**
     * Validate caveat code (15 digits)
     * @param string $caveatCode - Caveat code
     * @return bool - True if valid
     */
    public static function validateCaveatCode($caveatCode) {
        return preg_match('/^\d{15}$/', $caveatCode);
    }
    
    /**
     * Validate state code (2 digits)
     * @param string $stateCode - State code
     * @return bool - True if valid
     */
    public static function validateStateCode($stateCode) {
        return preg_match('/^\d{1,2}$/', $stateCode);
    }
    
    /**
     * Validate API parameters based on API type
     * @param string $apiName - API endpoint name
     * @param array $params - Parameters to validate
     * @return array - Validation results
     */
    public static function validateAPIParameters($apiName, $params) {
        $errors = [];
        
        switch ($apiName) {
            case 'search_by_cnr':
                if (!isset($params['cino']) || !self::validateCNR($params['cino'])) {
                    $errors[] = 'Invalid CNR number (must be 16 alphanumeric characters)';
                }
                break;
                
            case 'show_business':
                if (!isset($params['cino']) || !self::validateCNR($params['cino'])) {
                    $errors[] = 'Invalid CNR number';
                }
                if (!isset($params['business_date']) || !self::validateDate($params['business_date'])) {
                    $errors[] = 'Invalid business date (format: yyyy-mm-dd)';
                }
                break;
                
            case 'show_orders':
                if (!isset($params['cino']) || !self::validateCNR($params['cino'])) {
                    $errors[] = 'Invalid CNR number';
                }
                if (!isset($params['order_no']) || !is_numeric($params['order_no'])) {
                    $errors[] = 'Invalid order number';
                }
                if (!isset($params['order_date']) || !self::validateDate($params['order_date'])) {
                    $errors[] = 'Invalid order date (format: yyyy-mm-dd)';
                }
                break;
                
            // Add validation for other APIs...
                
            default:
                // No specific validation for this API
                break;
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
?>