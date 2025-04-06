# Sigawa Logger Interface

## Overview

The `LoggerInterface` defines a set of logging methods that help manage logging for your application. It categorizes log messages based on severity with different log levels such as `emergency`, `alert`, `critical`, `error`, `warning`, `notice`, `info`, and `debug`. The interface also supports contextual logging, making it easy to add extra data to log messages.

### Features:

- **Log Levels**: Includes 8 log levels (`emergency`, `alert`, `critical`, `error`, `warning`, `notice`, `info`, `debug`).
- **Contextual Logging**: Allows adding additional context to log entries with the optional `$context` parameter.
- **Dynamic Logging**: A flexible `log()` method that allows logging at any specified level.
- **Custom Implementations**: The interface can be implemented in custom logging systems, such as file logging, database logging, or integration with third-party services.

---

## Installation

To use the `LoggerInterface`, implement it in your custom logger class. Here is an example of how to do so.

### Example Implementation

```php
namespace MyApp\Logging;

use Sigawa\Sigawax\Core\Contracts\LoggerInterface;

class FileLogger implements LoggerInterface
{
    // Implement the 'emergency' log level
    public function emergency($message, array $context = []): void
    {
        // Your code to log emergency messages
    }

    // Implement the 'alert' log level
    public function alert($message, array $context = []): void
    {
        // Your code to log alert messages
    }

    // Implement the 'critical' log level
    public function critical($message, array $context = []): void
    {
        // Your code to log critical messages
    }

    // Implement the 'error' log level
    public function error($message, array $context = []): void
    {
        // Your code to log error messages
    }

    // Implement the 'warning' log level
    public function warning($message, array $context = []): void
    {
        // Your code to log warning messages
    }

    // Implement the 'notice' log level
    public function notice($message, array $context = []): void
    {
        // Your code to log notice messages
    }

    // Implement the 'info' log level
    public function info($message, array $context = []): void
    {
        // Your code to log info messages
    }

    // Implement the 'debug' log level
    public function debug($message, array $context = []): void
    {
        // Your code to log debug messages
    }

    // Implement the 'log' method for custom log levels
    public function log($level, $message, array $context = []): void
    {
        // Your code to handle dynamic log levels
    }
}
