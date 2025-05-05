<?php

// Require all dependencies
require_once 'Config.php';
require_once 'Logger.php';
require_once 'MyHttpClient.php';
require_once 'OrderService.php';


// Initialize components
$config = new Config();
$logger = new Logger();
$myhttpClient = new MyHttpClient($config, $logger);



$validator = new OrderService($logger);

try {
    // Get raw JSON input from the request
    $rawInput = file_get_contents("php://input");
    $logger->log("Raw input received: " . $rawInput);

    $requestData = json_decode($rawInput, true);

    // Validate and sanitize input
    $validationResult = $validator->sanitizeAndValidate($requestData);

    if (!$validationResult['success']) {
        throw new InvalidArgumentException(json_encode($validationResult['errors']));
    }

    $sanitizedData = $validationResult['data']; // Extract the cleaned data

    $params =  $sanitizedData;
    // Make the POST request with sanitized data
    $response = $myhttpClient->request('POST', '/async/transactions',[],  $sanitizedData );

    // Return API response
    header('Content-Type: application/json');
    $decoded = json_decode($response['body'], true);

    if (json_last_error() === JSON_ERROR_NONE) {
        echo json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    } else {
        echo $response['body'];
    }
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
