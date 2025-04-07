<?php 
namespace Sigawa\Sigawax\Core\Contracts\Interface;
interface BindingInterface {
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
}