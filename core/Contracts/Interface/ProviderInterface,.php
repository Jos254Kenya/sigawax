<?php

namespace Sigawa\Sigawax\Core\Contracts\Interface;

use Sigawa\Sigawax\Core\Contracts\ServiceProviderInterface;

interface ProviderInterface {
    
    public function register(ServiceProviderInterface $provider): void;

    public function isRegistered(string $providerClass): bool;
}