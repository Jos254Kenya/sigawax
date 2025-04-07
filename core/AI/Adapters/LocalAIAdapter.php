<?php

namespace Sigawa\Sigawax\Core\AI\Adapters;

use Sigawa\Sigawax\Core\Contracts\Interface\AI\AIAdapterInterface;

class LocalAIAdapter implements AIAdapterInterface
{
    public function suggest(string $context = ''): array
    {
        return ['Logger', 'EventBus', 'Config'];
    }

    public function predict(string $namespace): array
    {
        return [$namespace . '\\ServiceA'];
    }
    public function isEnabled(): bool
    {
        return true;
    }
    
    public function suggestRefactor(string $class): array
    {
        return ['Add docblocks', 'Reduce method size'];
    }

    public function explainDependencies(string $abstract, bool $withAI = true): string
    {
        return "$abstract depends on many services. Consider injecting interfaces.";
    }

    public function analyze(string $code): string
    {
        return "Static analysis suggests splitting logic into services.";
    }

    public function complete(string $prompt): string
    {
        return "You could wrap this with a decorator for caching.";
    }
}
