<?php
namespace Sigawa\Sigawax\Core\Async\Engines;

use React\EventLoop\Loop;
use React\Promise\Promise;
use Sigawa\Sigawax\Core\Contracts\Async\AsyncEngineInterface;

class ReactEngine implements AsyncEngineInterface
{
    public function run(callable $task): mixed
    {
        return new Promise(function ($resolve) use ($task) {
            Loop::addTimer(0.001, function () use ($resolve, $task) {
                $resolve($task());
            });
        });
    }

    public function defer(callable $task): mixed
    {
        return $this->run($task);
    }

    public function supports(): bool
    {
        return class_exists(Loop::class);
    }
}
