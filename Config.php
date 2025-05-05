
<?php

class Config
{
    private $settings;

    public function __construct()
    {
        $this->settings = [
            'api' => [
                'baseUrl' => 'https://preprod-dvs-api.dtone.com/v1',
                'apiKey' => '82149978-3098-497a-82c8-f92e33c35524',
                'apiSecret' => 'ed899aef-5a40-4a0a-b7af-0be72c5c7efa' 
            
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
