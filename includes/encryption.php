<?php
/**
 * Encryption and Decryption Functions for eCourts API
 */

require_once __DIR__ . '/../config/config.php';

class EncryptionHandler {
    
    /**
     * Generate HMAC-SHA256 hash for request token
     * @param string $inputStr - The concatenated parameter string
     * @return string - Hexadecimal hash
     */
    public static function generateHash($inputStr) {
        return hash_hmac('sha256', $inputStr, HASH_SECRET);
    }
    
    /**
     * Encrypt request string using AES-128-CBC
     * @param string $inputStr - The concatenated parameter string
     * @return string - Base64 encoded encrypted string
     */
    public static function encryptRequest($inputStr) {
        $encrypted = openssl_encrypt(
            $inputStr,
            ENCRYPTION_METHOD,
            AUTHENTICATION_KEY,
            OPENSSL_RAW_DATA,
            IV
        );
        
        return base64_encode($encrypted);
    }
    
    /**
     * Decrypt response string
     * @param string $encryptedStr - Base64 encoded encrypted string
     * @return string - Decrypted JSON string
     */
    public static function decryptResponse($encryptedStr) {
        $payload = base64_decode($encryptedStr);
        
        $decrypted = openssl_decrypt(
            $payload,
            ENCRYPTION_METHOD,
            AUTHENTICATION_KEY,
            OPENSSL_RAW_DATA,
            IV
        );
        
        return $decrypted;
    }
    
    /**
     * Generate concatenated parameter string
     * @param array $params - Associative array of parameters
     * @return string - Pipe-separated key=value string
     */
    public static function buildParameterString($params) {
        $pairs = [];
        foreach ($params as $key => $value) {
            $pairs[] = $key . '=' . $value;
        }
        return implode('|', $pairs);
    }
    
    /**
     * Verify response token
     * @param string $responseStr - Decrypted response string
     * @param string $responseToken - Received response token
     * @return bool - True if tokens match
     */
    public static function verifyResponseToken($responseStr, $responseToken) {
        $calculatedToken = hash_hmac('sha256', $responseStr, HASH_SECRET);
        return hash_equals($calculatedToken, $responseToken);
    }
}
?>