<?php

class MyHttpClient
{
    private $config;
    private $logger;

    public function __construct(Config $config, Logger $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }
  

    /**
     * Validates required headers and their format
     * @param array $headers
     * @throws InvalidArgumentException
     */
    private function validateHeaders(array $headers): void
    {
        $requiredHeaders = ['Api-Key', 'Api-Secret', 'Content-Type'];
        
        foreach ($requiredHeaders as $required) {
            if (!isset($headers[$required])) {
                $this->logger->log("Missing required header: {$required}");
                throw new InvalidArgumentException("Missing required header: {$required}");
            }
        }

        // Validate header values are not empty
        foreach ($headers as $key => $value) {
            if (empty($value) && $value !== '0') {
                $this->logger->log("Header {$key} cannot be empty");
                throw new InvalidArgumentException("Header {$key} cannot be empty");
            }
        }
    }

    /**
     * Format headers from associative array to CURL format
     * @param array $headers
     * @return array
     */
    private function formatHeaders(array $headers): array
    {
        $formattedHeaders = [];
        foreach ($headers as $key => $value) {
            $formattedHeaders[] = "{$key}: {$value}";
        }
        return $formattedHeaders;
    }

    /**
     * Get default headers with API credentials
     * @return array
     */
    private function getDefaultHeaders(): array
    {
        return [
            'Api-Key' => $this->config->get('api.apiKey'),
            'Api-Secret' => $this->config->get('api.apiSecret'),
            'Content-Type' => 'application/json'
        ];
    }

    public function request($method, $url, $headers = [], $params = [])
    {
        try {
            // Merge default headers with any provided headers
            $headers = array_merge($this->getDefaultHeaders(), $headers);
            
            // Validate headers before processing
            $this->validateHeaders($headers);
            
            $ch = curl_init();
            
            if (!$ch) {
                throw new RuntimeException("Failed to initialize CURL");
            }

            // If URL is relative, prepend the base URL
            if (strpos($url, 'http') !== 0) {
                $url = $this->config->get('api.baseUrl') . $url;
            }

            if (strtoupper($method) === 'GET') {
                if (isset($params['code'])) {
                    $url = str_replace('{code}', $params['code'], $url);
                    unset($params['code']);
                }

                if (!empty($params)) {
                    $url = $url . '?' . http_build_query($params);
                }
            }

            // Format headers for CURL
            $formattedHeaders = $this->formatHeaders($headers);

            // Set basic CURL options
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => $formattedHeaders,
                CURLOPT_HEADER => true, // To capture response headers
                CURLOPT_VERBOSE => true // For debugging
            ]);

            // Set method-specific options
            switch (strtoupper($method)) {
                case 'POST':
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
                    break;
                    
                case 'PUT':
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
                    break;

                case 'DELETE':
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                    break;
            }

            // Execute request and get response
            $response = curl_exec($ch);
            
            if ($response === false) {
                throw new RuntimeException('Curl error: ' . curl_error($ch));
            }

            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $responseHeaders = substr($response, 0, $headerSize);
            $responseBody = substr($response, $headerSize);
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_close($ch);

            return [
                'statusCode' => $statusCode,
                'headers' => $this->parseResponseHeaders($responseHeaders),
                'body' => $responseBody
            ];

        } catch (Exception $e) {
            if (isset($ch)) {
                curl_close($ch);
            }
            throw $e;
        }
    }

    /**
     * Parse response headers into an associative array
     * @param string $headerString
     * @return array
     */
    private function parseResponseHeaders(string $headerString): array
    {
        $headers = [];
        $headerLines = explode("\r\n", $headerString);
        
        foreach ($headerLines as $line) {
            if (strpos($line, ':') !== false) {
                [$key, $value] = explode(':', $line, 2);
                $headers[trim($key)] = trim($value);
            }
        }
        
        return $headers;
    }
}