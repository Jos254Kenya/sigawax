<?php

namespace Sigawa\Sigawax\Core\Contracts;

use Sigawa\Sigawax\Core\Contracts\Interface\AI\AIAdapterInterface;
use Sigawa\Sigawax\Core\Contracts\Interface\AliasingInterface;
use Sigawa\Sigawax\Core\Contracts\Interface\AsyncInterface;
use Sigawa\Sigawax\Core\Contracts\Interface\AutoWireInterface;
use Sigawa\Sigawax\Core\Contracts\Interface\BindingInterface;
use Sigawa\Sigawax\Core\Contracts\Interface\DebuggingInterface;
use Sigawa\Sigawax\Core\Contracts\Interface\ParameterInterface;
use Sigawa\Sigawax\Core\Contracts\Interface\ProviderInterface;
use Sigawa\Sigawax\Core\Contracts\Interface\ResolutionInterface;
use Sigawa\Sigawax\Core\Contracts\Interface\ScopingInterface;
use Sigawa\Sigawax\Core\Contracts\Interface\SnapshotInterface;
use TaggingInterface;

/**
 * Interface ContainerInterface
 *
 * A powerful, extensible container contract for dependency injection,
 * parameter management, and intelligent developer experience.
 *
 * Visionary improvements integrated:
 *  - Types, Generics & Strong Contracts (PHPStan/IDE Power-Up)
 *  - Service Aliasing (Symfony-style)
 *  - Async Service Resolution (Vision 2040 Baby)
 *  - Autowire Control + Attributes Support
 *  - Event Hooks for Observability
 *  - Immutable & Sandbox Containers
 *  - Auto-Discovery Mechanism (Think Laravel Providers but Smarter)
 *  - Bonus: Super-AI Integration Concepts
 */

interface ContainerInterface extends AIAdapterInterface,
    AliasingInterface,
    AsyncInterface,
    AutoWireInterface,
    BindingInterface,
    DebuggingInterface,
    ParameterInterface,
    ProviderInterface,
    ResolutionInterface,
    ScopingInterface,
    SnapshotInterface,
    TaggingInterface
{};
