<?php 
namespace Sigawa\Sigawax\Core\Contracts\Interface;

use Closure;
use Sigawa\Sigawax\Core\Contracts\ContainerInterface;

interface SnapshotInterface {
     /**
     * Create an immutable snapshot of the container.
     *
     * @return ContainerInterface
     */
    public function snapshot(): ContainerInterface;

    /**
     * Execute a closure within a sandboxed container.
     *
     * @param Closure $callback
     * @return mixed
     */
    public function sandbox(Closure $callback);

    /** ──────── AUTO-DISCOVERY MECHANISM ──────── */
    
    /**
     * Automatically discover and register services from a given path.
     *
     * @param string $path
     */
    public function discover(string $path): void;
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