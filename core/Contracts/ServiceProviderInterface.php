<?php

namespace Sigawa\Sigawax\Core\Contracts;

interface ServiceProviderInterface
{
    /**
     * Register bindings and services into the container.
     *
     * This is where you bind interfaces to implementations,
     * register singletons, tagged services, scoped services, etc.
     *
     * @param ContainerInterface $container
     * @return void
     */
    public function register(ContainerInterface $container): void;

    /**
     * Perform any actions needed after all services are registered.
     *
     * This is ideal for bootstrapping services that need to run
     * once everything is wired up (e.g., event listeners, config merge, etc.)
     *
     * @param ContainerInterface $container
     * @return void
     */
    public function boot(ContainerInterface $container): void;

    /**
     * Indicate if this provider should be lazily loaded.
     *
     * @return bool
     */
    public function isDeferred(): bool;

    /**
     * List the service(s) this provider provides.
     * Used by deferred loading to know when to trigger this provider.
     *
     * @return array<string>
     */
    public function provides(): array;
}
