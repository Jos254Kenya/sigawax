<?php

namespace Sigawa\Sigawax\Core\Contracts\Interface;

use Sigawa\Sigawax\Core\Contracts\Async\AsyncEngineInterface;

interface AsyncInterface
{
    /**
     * Defer the resolution of a service asynchronously.
     *
     * @param string $abstract
     * @param array $parameters
     * @return mixed
     */
    public function makeAsync(string $abstract, array $parameters = []): mixed;

    /**
     * Run an async task and wait for the result (blocking).
     *
     * @param string $abstract
     * @param array $parameters
     * @return mixed
     */
    public function await(string $abstract, array $parameters = []): mixed;

    /**
     * Manually set the async engine (for testing or customization).
     *
     * @param AsyncEngineInterface $engine
     * @return void
     */
    public function useAsyncEngine(AsyncEngineInterface $engine): void;
    /**
     * Get the currently active async engine instance.
     */
    public function getAsyncEngine(): AsyncEngineInterface;
}
