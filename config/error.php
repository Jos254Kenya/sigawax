<?php  
// config/error.php
// this file contains configurations for error display.
return [
    'display_error' => getenv('DISPLAY_ERRORS', true), // Control error display (dev vs prod)
    'api_error_format' => [
        'prod' => [
            ':message' => 'An error occurred: :message',
            ':code' => 'Error code: :code',
            ':trace' => 'Trace: :trace',
            ':file' => 'File: :file',
            ':line' => 'Line: :line'
        ],
        'dev' => [
            ':message' => 'Development error: :message',
            ':code' => 'Error code: :code',
            ':trace' => 'Stack trace: :trace',
            ':file' => 'File path: :file',
            ':line' => 'Error line: :line'
        ],
    ],
    'log_level' => 'debug', // Can be 'debug', 'info', 'error', etc.
];

