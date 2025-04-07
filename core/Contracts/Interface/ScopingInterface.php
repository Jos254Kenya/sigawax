<?php

namespace Sigawa\Sigawax\Core\Contracts\Interface;

use Closure;

interface ScopingInterface
{
    /** ──────── SCOPES & ISOLATION ──────── */

    public function scope(string $name, Closure $callback);

    public function forgetScope(string $name): void;

    /**
     * Forks the current container and returns an isolated scope (immutable-style).
     *
     * @return static
     */
    public function fork(): static;

    /**
     * Executes a callable within an isolated, disposable scope.
     *
     * @param Closure $callback
     * @return mixed
     */
    public function sandbox(Closure $callback): mixed;

    /**
     * Checks whether a scope exists.
     *
     * @param string $name
     * @return bool
     */
    public function hasScope(string $name): bool;
     /**
     * Register a callback to be fired when a service is resolved.
     *
     * @param callable $callback
     */
    public function onResolve(callable $callback): void;

    /**
     * Register a callback to be fired when a service is forgotten.
     *
     * @param callable $callback
     */
    public function onForget(callable $callback): void;


}
