<?php

namespace Sigawa\Sigawax\Core\Contracts;

use Closure;

interface ContainerInterface
{
    /** ──────── BASIC BINDINGS ──────── */

    public function bind(string $abstract, $concrete): void;

    public function singleton(string $abstract, $concrete): void;

    public function instance(string $abstract, $instance): void;

    public function bindWhen(string $abstract, string $context, $concrete): void;

    public function has(string $abstract): bool;

    public function isSingleton(string $abstract): bool;

    public function forget(string $abstract): void;


    /** ──────── RESOLUTION & INVOCATION ──────── */

    public function make(string $abstract, array $parameters = []);

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

    public function suggest(string $context = ''): array;

    public function predict(string $namespace): array;
    /** ──────── PARAMETERS & CONFIGURATION ──────── */

    public function setParameter(string $key, $value): void;

    public function getParameter(string $key);
    /** ──────── SERVICE PROVIDERS ──────── */
    public function register(ServiceProviderInterface $provider): void;
    public function isRegistered(string $providerClass): bool;

    /** ──────── CACHING / SNAPSHOTTING ──────── */
    public function exportState(): array;

    public function importState(array $state): void;
}
