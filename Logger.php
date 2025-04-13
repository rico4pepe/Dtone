<?php

class Logger
{
    private $logDirectory;

    public function __construct($logDirectory = null)
    {
        $this->logDirectory = $logDirectory ?? __DIR__ . DIRECTORY_SEPARATOR . 'logs';

        if (!file_exists($this->logDirectory)) {
            mkdir($this->logDirectory, 0777, true);
        }
    }

    public function log($log_msg)
    {
        $logFile = $this->logDirectory . '/log_' . date('d-M-Y') . '.log';

        if (!is_writable($this->logDirectory)) {
            error_log("Directory not writable: " . $this->logDirectory);
            return false;
        }

        $result = @file_put_contents($logFile, $log_msg . PHP_EOL, FILE_APPEND);

        if ($result === false) {
            $error = error_get_last();
            error_log("Log write failed: " . print_r($error, true));
            return false;
        }

        return true;
    }
}
