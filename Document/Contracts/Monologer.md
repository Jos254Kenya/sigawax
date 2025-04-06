# Monolog Logger Implementation 
### namespace Sigawa\Sigawax\Core\Logging;

## Overview

`MonologLogger` is a custom implementation of the `LoggerInterface` using the Monolog library. It provides a flexible logging solution with multiple log levels, such as `emergency`, `alert`, `critical`, `error`, `warning`, `notice`, `info`, and `debug`. This implementation writes logs to a file located at `storage/logs/app.log`.

---

## Features

- **Monolog Integration**: Utilizes Monolog for logging functionality.
- **Log Levels**: Supports Monolog's standard log levels: `emergency`, `alert`, `critical`, `error`, `warning`, `notice`, `info`, `debug`.
- **File-based Logging**: Logs are saved in a file (`app.log`) inside the `storage/logs` directory.
- **Contextual Logging**: Additional context can be provided with each log message for more detailed information.

---

## Installation

To use `MonologLogger`, install the required dependencies (Monolog) via Composer:

```bash
composer require monolog/monolog
```

Or since is part of the framework, no need to run this command as it will be automatically included in the scope.