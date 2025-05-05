<?php

class OrderService
{
    private Logger $logger;
   // private Config $config;
    //private MyHttpClient $httpClient;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
     
    }

    /**
     * Generate a unique external ID for transactions
     * 
     * @return string
     */
    private function generateExternalId(): string
    {
        // Simple algorithm for unique ID: Test + random number
        return 'Dtone' . rand(1000, 9999);
    }


    /**
     * Create a new transaction
     * 
     * @param array $data Validated input data
     * @return array Response from API
     */
    


    /**
     * Sanitize and validate input data.
     *
     * @param array $data
     * @return array
     */
    public function sanitizeAndValidate(array $data): array
{
    $sanitizedData = [];
    $errors = [];

    $rules = [
        'external_id' => ['required' => true, 'type' => 'string'],
        'product_id' => ['required' => true, 'type' => 'numeric'],
        'credit_party_identifier.mobile_number' => ['required' => true, 'type' => 'string'],
        'credit_party_identifier.account_number' => ['required' => true, 'type' => 'string'],
    ];

    foreach ($rules as $field => $rule) {
        $keys = explode('.', $field);
        $value = $data;

        foreach ($keys as $key) {
            if (!isset($value[$key])) {
                $value = null;
                break;
            }
            $value = $value[$key];
        }

        // Auto-generate external_id if missing
        if ($field === 'external_id' && $value === null) {
            $generatedId = $this->generateExternalId();
            $value = $generatedId;
            $this->logger->log("Auto-generated external_id: $generatedId");
        }

        if ($value === null) {
            if ($rule['required']) {
                $errors[$field] = "$field is required.";
            }
            continue;
        }

        // Sanitize
        $sanitizedValue = is_string($value) ? trim($value) : $value;

        // Validate
        if ($rule['type'] === 'string' && !is_string($sanitizedValue)) {
            $errors[$field] = "$field must be a string.";
        } elseif ($rule['type'] === 'numeric' && !is_numeric($sanitizedValue)) {
            $errors[$field] = "$field must be a numeric value.";
        }

        // Set sanitized value in nested structure
        $ref =& $sanitizedData;
        foreach ($keys as $i => $key) {
            if ($i === count($keys) - 1) {
                $ref[$key] = $sanitizedValue;
            } else {
                if (!isset($ref[$key]) || !is_array($ref[$key])) {
                    $ref[$key] = [];
                }
                $ref =& $ref[$key];
            }
        }
    }

    if (!empty($errors)) {
        $this->logger->log("Validation errors: " . json_encode($errors));
        return ['success' => false, 'errors' => $errors];
    }

    return ['success' => true, 'data' => $sanitizedData];
}

}