<?php

// Include required files
require_once 'Config.php';
require_once 'MyHttpClient.php';
require_once 'Logger.php';



    try {
        // Initialize config
        $config = new Config();
        
        // Initialize logger
        $logger = new Logger();

        // Initialize HTTP client with config
        $httpClient = new MyHttpClient($config, $logger);
        
        // Make the request
        // Using relative URL which will be combined with baseUrl from config
        $response = $httpClient->request('GET', '/v1/products');
        
        // Check status code
        if ($response['statusCode'] === 200) {
            // Parse and return the response body
            $products = json_decode($response['body'], true);
            return $products;
        } else {
            // Log error and return null
            $logger->log("API Error. Status code: " . $response['statusCode']);
            $logger->log("Response: " . $response['body']);
            echo "Error: " . $response['body'] . "\n";
            return null;
        }
    } catch (Exception $e) {
        // Log exception and return null
        $logger->log("Exception: " . $e->getMessage());
        echo "Error: " . $e->getMessage() . "\n";
        return null;
    }


