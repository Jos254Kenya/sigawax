<?php 
namespace Sigawa\Sigawax\Core\Contracts\Interface;
interface ResolutionInterface {
     
    /** ──────── RESOLUTION & INVOCATION ──────── */
    
    /**
     * Resolve an instance by its abstract name.
     *
     * @template T
     * @param class-string<T> $abstract
     * @param array $parameters
     * @return T
     * @throws \Sigawa\Sigawax\Core\Exceptions\BindingResolutionException|\Sigawa\Sigawax\Core\Exceptions\ServiceNotFoundException
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

}