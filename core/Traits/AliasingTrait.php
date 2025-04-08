<?php

namespace Sigawa\Sigawax\Core\Traits;

trait AliasingTrait
{
    /**
     * Holds alias ➜ abstract mappings.
     *
     * @var array<string, string>
     */
    protected array $aliases = [];

    /**
     * Cache for resolved aliases.
     *
     * @var array<string, string>
     */
    protected array $aliasResolutionCache = [];

    /**
     * Holds group ➜ [aliases] mappings.
     *
     * @var array<string, array<int, string>>
     */
    protected array $aliasGroups = [];

    /**
     * Define an alias for an abstract type.
     */
    public function alias(string $abstract, string $alias): void
    {
        if (isset($this->aliases[$alias]) && $this->aliases[$alias] !== $abstract) {
            trigger_error(
                "Alias '{$alias}' already points to '{$this->aliases[$alias]}'. Overwriting may cause resolution conflicts.",
                E_USER_WARNING
            );
        }

        $this->aliases[$alias] = $abstract;
        $this->aliasResolutionCache[$alias] = $abstract;
    }

    /**
     * Check if the given string is an alias.
     */
    public function isAlias(string $alias): bool
    {
        return array_key_exists($alias, $this->aliases);
    }

    /**
     * Get the abstract mapped to an alias.
     */
    public function getAbstractForAlias(string $alias): ?string
    {
        return $this->aliases[$alias] ?? null;
    }

    /**
     * Get all aliases for the given abstract.
     */
    public function getAliasesForAbstract(string $abstract): array
    {
        return array_keys(
            array_filter($this->aliases, fn (string $mapped) => $mapped === $abstract)
        );
    }

    /**
     * Get all registered alias mappings.
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }

    /**
     * Remove an alias if it exists.
     */
    public function removeAlias(string $alias): void
    {
        unset($this->aliases[$alias], $this->aliasResolutionCache[$alias]);

        foreach ($this->aliasGroups as $group => &$groupAliases) {
            $groupAliases = array_filter($groupAliases, fn ($a) => $a !== $alias);
        
            if (empty($groupAliases)) {
                unset($this->aliasGroups[$group]);
            }
        }
        
    }

    /**
     * Resolve a given name — if it's an alias, return its abstract;
     * otherwise, return the original string.
     */
    public function resolveAlias(string $name): string
    {
        return $this->aliasResolutionCache[$name]
            ??= $this->aliases[$name] ?? $name;
    }

    /**
     * Assign an alias to a group.
     */
    public function assignAliasToGroup(string $alias, string $group): void
    {
        if (!isset($this->aliases[$alias])) {
            trigger_error("Alias '{$alias}' does not exist and cannot be grouped.", E_USER_WARNING);
            return;
        }
        if (!isset($this->aliasGroups[$group])) {
            $this->aliasGroups[$group] = [];
        }

        if (!in_array($alias, $this->aliasGroups[$group], true)) {
            $this->aliasGroups[$group][] = $alias;
        }
        
        
    }

    /**
     * Get all aliases under a specific group.
     */
    public function getAliasesInGroup(string $group): array
    {
        return $this->aliasGroups[$group] ?? [];
    }

    /**
     * Get all groups with their aliases.
     */
    public function getAliasGroups(): array
    {
        return $this->aliasGroups;
    }
}
