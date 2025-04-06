<?php
namespace Sigawa\Sigawax\Core\Providers;

use Sigawa\Sigawax\Core\Container;

abstract class ServiceProvider
{
    /**
     * The container instance.
     */
    protected Container $container;

    /**
     * Constructor to receive the container.
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Register services into the container.
     */
    abstract public function register(): void;
}
