<?php

namespace Sigawa\Sigawax\Core\AI;

use Sigawa\Sigawax\Core\Contracts\Interface\AI\AIAdapterInterface;

class AIAdapterRegistry
{
    /**
     * @var AIAdapterInterface[]
     */
    protected array $adapters = [];

    public function register(string $key, AIAdapterInterface $adapter): void
    {
        $this->adapters[$key] = $adapter;
    }

    public function get(string $key): ?AIAdapterInterface
    {
        return $this->adapters[$key] ?? null;
    }

    public function getDefault(): AIAdapterInterface
    {
        foreach ($this->adapters as $adapter) {
            if (method_exists($adapter, 'isEnabled') && $adapter->isEnabled()) {
                return $adapter;
            }
        }

        // Fallback to first registered (local/default)
        return reset($this->adapters);
    }

    public function all(): array
    {
        return $this->adapters;
    }
}
