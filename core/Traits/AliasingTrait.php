<?php

namespace Sigawa\Sigawax\Core\Traits;

trait AliasingTrait
{
    /**
     * Holds abstract => alias mappings.
     *
     * @var array<string, string>
     */
    protected array $aliases = [];

    /**
     * Define an alias for an abstract type.
     *
     * @param string $abstract
     * @param string $alias
     */
    public function alias(string $abstract, string $alias): void
    {
        $this->aliases[$alias] = $abstract;
    }

    /**
     * Resolve an alias to its actual abstract, if any.
     *
     * @param string $abstractOrAlias
     * @return string
     */
    protected function resolveAlias(string $abstractOrAlias): string
    {
        return $this->aliases[$abstractOrAlias] ?? $abstractOrAlias;
    }

    /**
     * Check if a given string is an alias.
     *
     * @param string $alias
     * @return bool
     */
    public function isAlias(string $alias): bool
    {
        return isset($this->aliases[$alias]);
    }
}
