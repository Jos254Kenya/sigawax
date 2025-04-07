<?php

namespace Sigawa\Sigawax\Core\Contracts\Async;

interface AsyncEngineInterface
{
    public function run(callable $task): mixed;

    public function defer(callable $task): mixed;

    public function supports(): bool;
}
