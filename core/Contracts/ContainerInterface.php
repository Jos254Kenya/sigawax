<?php

namespace Sigawa\Sigawax\Core\Contracts;

use Closure;
use Sigawa\Sigawax\Core\Exceptions\BindingResolutionException;
use Sigawa\Sigawax\Core\Exceptions\ServiceNotFoundException;

/**
 * Interface ContainerInterface
 *
 * A powerful, extensible container contract for dependency injection,
 * parameter management, and intelligent developer experience.
 */
interface ContainerInterface
{
    /** ──────── BASIC BINDINGS ──────── */

    /**
     * Bind an abstract type to a concrete implementation.
     *
     * @param string $abstract
     * @param mixed $concrete
     */
    public function bind(string $abstract, $concrete): void;

    /**
     * Bind an abstract type as a singleton (shared instance).
     *
     * @param string $abstract
     * @param mixed $concrete
     */
    public function singleton(string $abstract, $concrete): void;

    /**
     * Register an existing instance as shared.
     *
     * @param string $abstract
     * @param mixed $instance
     */
    public function instance(string $abstract, $instance): void;

    /**
     * Bind a contextual implementation based on a consuming class.
     *
     * @param string $abstract
     * @param string $context
     * @param mixed $concrete
     */
    public function bindWhen(string $abstract, string $context, $concrete): void;

    public function has(string $abstract): bool;

    public function isSingleton(string $abstract): bool;

    public function forget(string $abstract): void;

    /** ──────── RESOLUTION & INVOCATION ──────── */

    /**
     * Resolve an instance by its abstract name.
     *
     * @param string $abstract
     * @param array $parameters
     * @return mixed
     * @throws BindingResolutionException|ServiceNotFoundException
     */
    public function make(string $abstract, array $parameters = []);

    /**
     * Invoke a callable with automatic dependency injection.
     *
     * @param callable $callable
     * @param array $parameters
     * @return mixed
     */
    public function call($callable, array $parameters = []);

    public function resolving(string $abstract, callable $callback): void;

    public function afterResolving(string $abstract, callable $callback): void;

    public function onBind(callable $callback): void;

    /** ──────── TAGGING & GROUPING ──────── */

    public function tag(string $tag, array $services): void;

    public function tagged(string $tag): array;

    /** ──────── SCOPES & ISOLATION ──────── */

    public function scope(string $name, Closure $callback);

    public function forgetScope(string $name): void;

    /** ──────── INTROSPECTION & DEBUGGING ──────── */

    public function getBindings(): array;

    public function getContextualBindings(): array;

    public function getResolvedInstances(): array;

    public function getTags(): array;

    public function explain(string $abstract): array;

    public function listDependencies(string $abstract): array;

    /** ──────── AI-POWERED INTELLIGENCE ──────── */

    /**
     * Suggest useful services or improvements based on context.
     *
     * @param string $context
     * @return array
     */
    public function suggest(string $context = ''): array;

    /**
     * Predict services or methods in a given namespace.
     *
     * @param string $namespace
     * @return array
     */
    public function predict(string $namespace): array;

    /** ──────── PARAMETERS & CONFIGURATION ──────── */

    public function setParameter(string $key, $value): void;

    public function getParameter(string $key);

    /** ──────── SERVICE PROVIDERS ──────── */

    public function register(ServiceProviderInterface $provider): void;

    public function isRegistered(string $providerClass): bool;

    /** ──────── CACHING / SNAPSHOTTING ──────── */

    /**
     * Export the internal container state (e.g. for caching).
     *
     * @return array
     */
    public function exportState(): array;

    /**
     * Import and apply a previously cached container state.
     *
     * @param array $state
     */
    public function importState(array $state): void;
}
