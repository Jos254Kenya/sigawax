<?php 
namespace Sigawa\Sigawax\Core\Logging;

use Sigawa\Sigawax\Core\Contracts\LoggerInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class MonologLogger implements LoggerInterface
{
    /**
     * @var Logger $logger
     * Instance of the Monolog Logger.
     */
    protected $logger;

    /**
     * Constructor to initialize the logger instance.
     * 
     * @param string $channel The channel name for the logger, default is 'app'.
     */
    public function __construct(string $channel = 'app')
    {
        $this->logger = new Logger($channel);
        
        // Set up the logging handler to write logs to 'app.log' in the storage/logs directory.
        // The log level is set to DEBUG, meaning all messages at DEBUG level and above will be logged.
        $this->logger->pushHandler(new StreamHandler(__DIR__ . '/../../storage/logs/app.log', Logger::DEBUG));
    }

    /**
     * Log an emergency message (most severe level).
     * 
     * @param string $message The log message.
     * @param array $context Additional context data for the log message.
     */
    public function emergency($message, array $context = []): void
    {
        $this->logger->emergency($message, $context);
    }

    /**
     * Log an alert message (severe issue that requires immediate attention).
     * 
     * @param string $message The log message.
     * @param array $context Additional context data for the log message.
     */
    public function alert($message, array $context = []): void
    {
        $this->logger->alert($message, $context);
    }

    /**
     * Log a critical message (critical conditions that impact application functionality).
     * 
     * @param string $message The log message.
     * @param array $context Additional context data for the log message.
     */
    public function critical($message, array $context = []): void
    {
        $this->logger->critical($message, $context);
    }

    /**
     * Log an error message (general errors).
     * 
     * @param string $message The log message.
     * @param array $context Additional context data for the log message.
     */
    public function error($message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }

    /**
     * Log a warning message (potential issues that need attention).
     * 
     * @param string $message The log message.
     * @param array $context Additional context data for the log message.
     */
    public function warning($message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }

    /**
     * Log a notice message (significant, but not an issue).
     * 
     * @param string $message The log message.
     * @param array $context Additional context data for the log message.
     */
    public function notice($message, array $context = []): void
    {
        $this->logger->notice($message, $context);
    }

    /**
     * Log an informational message (general information, status updates).
     * 
     * @param string $message The log message.
     * @param array $context Additional context data for the log message.
     */
    public function info($message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    /**
     * Log a debug message (detailed information, typically for debugging purposes).
     * 
     * @param string $message The log message.
     * @param array $context Additional context data for the log message.
     */
    public function debug($message, array $context = []): void
    {
        $this->logger->debug($message, $context);
    }

    /**
     * Log a message at a custom level.
     * 
     * @param string $level The log level (e.g., 'info', 'error').
     * @param string $message The log message.
     * @param array $context Additional context data for the log message.
     */
    public function log($level, $message, array $context = []): void
    {
        $this->logger->log($level, $message, $context);
    }
}