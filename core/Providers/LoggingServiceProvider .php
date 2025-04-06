<?php
namespace Sigawa\Sigawax\Core\Providers;

use Sigawa\Sigawax\Core\Contracts\LoggerInterface;
use Sigawa\Sigawax\Core\Logging\MonologLogger;
use Sigawa\Sigawax\Core\Error\ErrorHandler;

class LoggingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register LoggerInterface with an instance of MonologLogger
        $this->container->bind(LoggerInterface::class, new MonologLogger());

        // Register ErrorHandler, resolving LoggerInterface from the container
        $logger = $this->container->get(LoggerInterface::class);
        $this->container->bind(ErrorHandler::class, new ErrorHandler($logger));
    }
}
