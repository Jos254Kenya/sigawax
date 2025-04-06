<?php
namespace Sigawa\Sigawax\Core\Contracts;

/**
 * LoggerInterface defines the methods required for a logging system.
 * This interface is designed to allow flexibility for different logging implementations
 * (e.g., file-based, database logging, or third-party services).
 */
interface LoggerInterface
{
    /**
     * Logs an emergency message. This level is used for critical situations 
     * requiring immediate attention, such as system failure.
     *
     * @param string $message The emergency message.
     * @param array $context Optional contextual data related to the log entry.
     * 
     * @return void
     */
    public function emergency($message, array $context = []): void;

    /**
     * Logs an alert message. This level is used for conditions that require 
     * immediate attention, such as a service outage.
     *
     * @param string $message The alert message.
     * @param array $context Optional contextual data related to the log entry.
     * 
     * @return void
     */
    public function alert($message, array $context = []): void;

    /**
     * Logs a critical message. This level is used for severe errors or issues 
     * that affect the system's stability or operation.
     *
     * @param string $message The critical message.
     * @param array $context Optional contextual data related to the log entry.
     * 
     * @return void
     */
    public function critical($message, array $context = []): void;

    /**
     * Logs an error message. This level is used for general errors that 
     * are non-critical but should be investigated.
     *
     * @param string $message The error message.
     * @param array $context Optional contextual data related to the log entry.
     * 
     * @return void
     */
    public function error($message, array $context = []): void;

    /**
     * Logs a warning message. This level is used to indicate potential issues 
     * or deviations that are not necessarily errors but require attention.
     *
     * @param string $message The warning message.
     * @param array $context Optional contextual data related to the log entry.
     * 
     * @return void
     */
    public function warning($message, array $context = []): void;

    /**
     * Logs a notice message. This level is used to provide normal but significant 
     * information about the application’s operation.
     *
     * @param string $message The notice message.
     * @param array $context Optional contextual data related to the log entry.
     * 
     * @return void
     */
    public function notice($message, array $context = []): void;

    /**
     * Logs an informational message. This level is used for general information 
     * about the system's operation that may be helpful for debugging or auditing.
     *
     * @param string $message The informational message.
     * @param array $context Optional contextual data related to the log entry.
     * 
     * @return void
     */
    public function info($message, array $context = []): void;

    /**
     * Logs a debug message. This level is used for detailed debugging information 
     * that is useful during development or troubleshooting.
     *
     * @param string $message The debug message.
     * @param array $context Optional contextual data related to the log entry.
     * 
     * @return void
     */
    public function debug($message, array $context = []): void;

    /**
     * Logs a message at a specific log level. This method allows flexibility 
     * by specifying the log level dynamically.
     *
     * @param string $level The log level (e.g., "emergency", "error", etc.).
     * @param string $message The log message.
     * @param array $context Optional contextual data related to the log entry.
     * 
     * @return void
     */
    public function log($level, $message, array $context = []): void;
}