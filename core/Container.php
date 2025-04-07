<?php 
// Container.php

// namespace Sigawa\Sigawax\Core;

// use Sigawa\Sigawax\Core\Contracts\ContainerInterface;

// class Container implements ContainerInterface{
    // use AsyncTrait;
    
    // public function __construct()
    // {
    //     $this->bootAsync();
    // }

    // protected function bootAsync(): void
    // {
    //     $preferred = $_ENV['SIGAWAX_ASYNC_ENGINE'] ?? null;

    //     $engine = match ($preferred) {
    //         'react' => new ReactEngine(),
    //         'fiber' => new FiberEngine(),
    //         default => null,
    //     };

    //     if ($engine) {
    //         $this->useAsyncEngine($engine);
    //     }
    // }
// }
