<?php
class Authentication
{
    private $apiKey;
    private $privateKeyPath;
    private $logger;

    public function __construct($apiKey, $privateKeyPath, Logger $logger)
    {
        $this->apiKey = $apiKey;
        $this->privateKeyPath = $privateKeyPath;
        $this->logger = $logger;
    }

    public function generateNonce()
    {
        // Generate a 24-character numeric nonce (example: '1116YYMMDDHHMMRandom')
        $noncePrefix = '1116';
        $date = date('ymd'); // yyMMdd format
        $time = date('Hi'); // HHmm format
        $random = rand(1000000000, 9999999999); // Random 10-digit number
        // $random = bin2hex(random_bytes(5)); // Random 10-character hex string
        $nonce = $noncePrefix . $date . $time . $random;
        $this->logger->log("Generated Nonce: " . $nonce);

    // Check if the nonce is 24 characters long
    if (strlen($nonce) !== 24) {
        $this->logger->log("Error: Nonce is not 24 characters long. Length: " . strlen($nonce));
        throw new Exception("Nonce must be exactly 24 characters long.");
    } else {
        $this->logger->log("Nonce length is valid: " . strlen($nonce)); // Log if length is valid
    }

    // Check if the nonce contains only numeric characters
    if (!ctype_digit($nonce)) {
        $this->logger->log("Error: Nonce contains non-numeric characters. Nonce: " . $nonce);
        throw new Exception("Nonce must be numeric.");
    } else {
        $this->logger->log("Nonce is numeric: " . $nonce); // Log if the nonce is numeric
    }

        
        return $nonce;
    }

    public function generateSignature($nonce)
    {
        // Concatenate apiKey and nonce to form data
        $data = $this->apiKey . $nonce;
        $this->logger->log("Data to Sign (Exact): " . $data);

        // Ensure private key file exists
        if (!file_exists($this->privateKeyPath) || !is_readable($this->privateKeyPath)) {
            $this->logger->log("Private key path: " . $this->privateKeyPath);
            throw new Exception("Private key file not found or unreadable: " . $this->privateKeyPath);
        }

        // Log private key path for debugging purposes
        $this->logger->log("Private key path: " . $this->privateKeyPath);

        // Read private key file content
        $privateKey = file_get_contents($this->privateKeyPath);
        $this->logger->log("Private Key Length: " . strlen($privateKey)); // Check key length
        $this->logger->log("Private Key (first 100 chars): " . substr($privateKey, 0, 100)); // Log part of private key

        if (!$privateKey) {
            throw new Exception("Failed to read private key.");
        }

        // Check if OpenSSL can load the private key
        $res = openssl_pkey_get_private($privateKey); 
        if (!$res) {
            $this->logger->log("OpenSSL error: " . openssl_error_string());
            throw new Exception("Invalid private key.");
        }

        // Initialize signature variable
        $signature = null;
        
        // Generate signature using OpenSSL
        if (!openssl_sign($data, $signature, $res, OPENSSL_ALGO_SHA256)) {
            $error = openssl_error_string();
            $this->logger->log("OpenSSL error during signature generation: " . $error);
            throw new Exception("Failed to generate signature.");
        }

        // Free the OpenSSL key resource
        openssl_pkey_free($res);  // Use openssl_pkey_free instead of openssl_free_key

        // Base64 URL-safe encode the signature
        //$encodedSignature = str_replace(['+', '/'], ['-', '_'], base64_encode($signature));  // URL-safe encoding
        $encodedSignature = base64_encode($signature);
        $this->logger->log("Base64 URL-safe Encoded Signature: " . $encodedSignature);

        // Log the final signature
        $this->logger->log("Generated Signature (Base64): " . $encodedSignature);

        // Confirm successful signature generation
        $this->logger->log("Signature generation process completed successfully.");
        
        return $encodedSignature;
    }
}
