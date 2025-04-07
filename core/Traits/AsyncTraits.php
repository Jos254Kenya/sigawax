<?php

namespace Sigawa\Sigawax\Core\Traits;

use Sigawa\Sigawax\Core\Contracts\Async\AsyncEngineInterface;
use Sigawa\Sigawax\Core\Async\Engines\FiberEngine;
use Sigawa\Sigawax\Core\Async\Engines\ReactEngine;

trait AsyncTrait
{
    protected AsyncEngineInterface $asyncEngine;

    protected function getAsyncEngine(): AsyncEngineInterface
    {
        if (isset($this->asyncEngine)) {
            return $this->asyncEngine;
        }

        $react = new ReactEngine();
        $fiber = new FiberEngine();

        $this->asyncEngine = $react->supports() ? $react :
                             ($fiber->supports() ? $fiber : throw new \RuntimeException("No supported async engine found."));

        return $this->asyncEngine;
    }

    public function makeAsync(string $abstract, array $parameters = []): mixed
    {
        return $this->getAsyncEngine()->defer(function () use ($abstract, $parameters) {
            return $this->make($abstract, $parameters);
        });
    }

    public function await(string $abstract, array $parameters = []): mixed
    {
        return $this->getAsyncEngine()->run(function () use ($abstract, $parameters) {
            return $this->make($abstract, $parameters);
        });
    }

    public function useAsyncEngine(AsyncEngineInterface $engine): void
    {
        $this->asyncEngine = $engine;
    }
}
