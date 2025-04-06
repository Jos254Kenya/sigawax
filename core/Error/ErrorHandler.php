<?php

namespace Sigawa\Sigawax\Core\Error;

use Exception;
use Sigawa\Sigawax\Core\Contracts\LoggerInterface;

class ErrorHandler
{
    /**
     * @var LoggerInterface $logger
     * Instance of the logger to log errors.
     */
    protected $logger;

    /**
     * @var array $config
     * Configuration for error handling.
     */
    protected $config;

    /**
     * ErrorHandler constructor.
     * 
     * @param LoggerInterface $logger The logger instance for error logging.
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        
        // Load error configuration from the file (use absolute path to ensure correct location).
        $this->config = require __DIR__ . '/../../config/error.php'; // Error config
    }

    /**
     * Handle an exception and generate a response based on environment settings.
     * 
     * @param Exception $e The exception instance to handle.
     * @return string The formatted error response (JSON for API or rendered HTML for web).
     */
    public function handle(Exception $e)
    {
        // Get environment (development or production)
        $env = getenv('APP_ENV') ?: 'production'; // Fallback to 'production' if APP_ENV is not set.
        
        // Determine the error format based on the environment
        $errorFormat = $this->config['api_error_format'][$env] ?? $this->config['api_error_format']['prod'];
        
        // Log the error details for further debugging
        $this->logError($e);
        
        // Format the error message for API-friendly output (or web output)
        $response = $this->formatError($e, $errorFormat);

        // Return the response (in JSON for API or rendered error page for web)
        return $this->respondWithError($response);
    }

    /**
     * Log the exception details using the provided logger.
     * 
     * @param Exception $e The exception instance to log.
     */
    protected function logError(Exception $e)
    {
        // Log the exception message, stack trace, file, and line for debugging.
        $this->logger->error($e->getMessage(), [
            'exception' => $e,
            'trace' => $e->getTraceAsString(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
    }

    /**
     * Format the error based on the specified format configuration.
     * 
     * @param Exception $e The exception to format.
     * @param array $errorFormat The format configuration (includes keys like ':message', ':code', etc.).
     * @return array The formatted error message.
     */
    protected function formatError(Exception $e, array $errorFormat)
    {
        // Initialize an array to store the formatted error response.
        $formatted = [];
        
        // Loop through the error format array and replace placeholders with actual values from the exception.
        foreach ($errorFormat as $key => $value) {
            $formatted[$key] = str_replace(
                [':message', ':code', ':trace', ':file', ':line'],
                [$e->getMessage(), $e->getCode(), $e->getTraceAsString(), $e->getFile(), $e->getLine()],
                $value
            );
        }

        return $formatted;
    }

    /**
     * Respond with the error in an appropriate format based on the request type.
     * 
     * @param array $response The formatted error response.
     * @return string The error response (either JSON or HTML).
     */
    protected function respondWithError($response)
    {
        // If the request is from an API (expects JSON response), return a JSON error response.
        if ($this->isApiRequest()) {
            return json_encode([
                'status' => 'error',
                'error' => $response
            ]);
        }

        // Otherwise, return an HTML error page (for web requests).
        return $this->renderErrorPage($response);
    }

    /**
     * Check if the current request is an API request based on the 'Accept' header.
     * 
     * @return bool True if it's an API request (accepts JSON), false otherwise.
     */
    protected function isApiRequest()
    {
        // Check the 'Accept' header to determine if the request expects JSON (API request).
        return isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
    }

    /**
     * Render an error page for non-API (web) requests.
     * 
     * @param array $response The formatted error response.
     * @return string The rendered error page.
     */
    protected function renderErrorPage($response)
    {
        // Return a user-friendly error page (can be adapted to any templating engine like Blade or Twig).
        // TODO: implement view rendering
        // return view('errors.generic', ['error' => $response]);
    }
}
