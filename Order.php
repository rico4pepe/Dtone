<?php

// Require all dependencies
require_once 'Config.php';
require_once 'Logger.php';
require_once 'MyHttpClient.php';


// Initialize components
$config = new Config();
$logger = new Logger();
$myhttpClient = new MyHttpClient($config, $logger);



$validator = new OrderService($logger);

try {
    // Get raw JSON input from the request
    $rawInput = file_get_contents("php://input");
    $requestData = json_decode($rawInput, true); // Decode as an associative array

    // Validate and sanitize input
    $validationResult = $validator->sanitizeAndValidate($requestData);

    if (!$validationResult['success']) {
        throw new InvalidArgumentException(json_encode($validationResult['errors']));
    }

    $sanitizedData = $validationResult['data']; // Extract the cleaned data

    // Make the POST request with sanitized data
    $response = $apiClient->request('POST', '/v1/async/transactions', $sanitizedData);

    // Return API response
    header('Content-Type: application/json');
    echo $response;
} catch (InvalidArgumentException $e) {
    // Return validation error response
    header('Content-Type: application/json');
    echo json_encode([
        "success" => false,
        "statusCode" => 400,
        "message" => "Invalid request: " . $e->getMessage(),
        "data" => null
    ]);
} catch (Exception $e) {
    // Return generic error response
    header('Content-Type: application/json');
    echo json_encode([
        "success" => false,
        "statusCode" => 500,
        "message" => "An error occurred: " . $e->getMessage(),
        "data" => null
    ]);
}
