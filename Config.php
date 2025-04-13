
<?php

class Config
{
    private $settings;

    public function __construct()
    {
        $this->settings = [
            'api' => [
                'baseUrl' => 'https://preprod-dvs-api.dtone.com/v1',
                'apiKey' => 'Wh27QPVOV/W1R9r+1Pat3vJ5JgvUjwDnQ4nlqdFjoiM=',
                'apiSecret' => 'your_api_secret_here' // Add your actual secret here
            
            ]
        ];
    }

    public function get($key)
    {
        $keys = explode('.', $key);
        $value = $this->settings;

        foreach ($keys as $key) {
            if (isset($value[$key])) {
                $value = $value[$key];
            } else {
                return null;
            }
        }

        return $value;
    }
}
