<?php

// Require all dependencies
require_once 'Config.php';
require_once 'Logger.php';
require_once 'MyHttpClient.php';
require_once 'ApiClient.php';
require_once 'Authentication.php';

// Initialize components
$config = new Config();
$logger = new Logger();
$myhttpClient = new MyHttpClient();

// Get API credentials from Config
$apiKey = $config->get('api.apiKey');
$privateKeyPath = $config->get('api.privateKeyPath');

$auth = new Authentication($apiKey, $privateKeyPath, $logger);
$apiClient = new ApiClient($myhttpClient, $auth, $logger, $config);

// Initialize parameters from the request (with default values)
$currencyId = $_GET['currencyId'] ?? null;
$currencyCode = $_GET['currencyCode'] ?? null;

$requestData = [
    'currencyId' => $currencyId,
    'currencyCode' => $currencyCode
];

try {
    // Make the GET request to fetch product categories
    $response = $apiClient->request('GET', 'api/v1/reseller/providers', $requestData);

    // Return API response
    header('Content-Type: application/json');
    echo $response;
} catch (Exception $e) {
    // Return error response
    header('Content-Type: application/json');
    echo json_encode([
        "success" => false,
        "statusCode" => 500,
        "message" => "An error occurred: " . $e->getMessage(),
        "data" => null
    ]);
}
