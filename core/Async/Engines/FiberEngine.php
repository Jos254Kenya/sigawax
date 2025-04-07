<?php 

namespace Sigawa\Sigawax\Core\Async\Engines;

use Fiber;
use Sigawa\Sigawax\Core\Contracts\Async\AsyncEngineInterface;

class FiberEngine implements AsyncEngineInterface
{
    public function run(callable $task): mixed
    {
        $fiber = new Fiber($task);
        $fiber->start();
        return $fiber->getReturn();
    }

    public function defer(callable $task): mixed
    {
        return new Fiber($task);
    }

    public function supports(): bool
    {
        return class_exists(Fiber::class);
    }
}
