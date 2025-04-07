<?php

namespace Sigawa\Sigawax\Core\AI\Adapters;

use Sigawa\Sigawax\Core\Contracts\Interface\AI\AIAdapterInterface;

class NullAIAdapter implements AIAdapterInterface
{
    public function analyze(string $code): string
    {
        return "AI is disabled. No analysis available.";
    }
    public function suggest(string $context = ''): array
    {
        return ['Logger', 'EventBus', 'Config'];
    }
    public function suggestRefactor(string $class): array
    {
        return [];
    }
    public function predict(string $namespace): array
    {
        return [$namespace . '\\ServiceA'];
    }
    public function isEnabled(): bool
    {
        return false;
    }
    public function explainDependencies(string $abstract, bool $withAI = true): string
    {
        return "$abstract AI is disabled at the moment. No suggestions or predictions available.";
    }

    public function complete(string $prompt): string
    {
        return "";
    }
}
