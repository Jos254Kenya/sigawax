<?php

namespace Sigawa\Sigawax\Core\Container\Traits;

trait AsyncTrait
{
    /**
     * Asynchronously resolve an instance.
     *
     * For now, we just wrap the sync `make()` in a closure to fake async.
     * Later, we can plug in real Fiber, ReactPHP, Amp, or Threads.
     *
     * @param string $abstract
     * @param array $parameters
     * @return \Closure
     */
    public function makeAsync(string $abstract, array $parameters = [])
    {
        return function () use ($abstract, $parameters) {
            return $this->make($abstract, $parameters);
        };
    }
}
