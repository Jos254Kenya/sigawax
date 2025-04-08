<?php

namespace Sigawa\Sigawax\Core\Alias;

use Sigawa\Sigawax\Core\Contracts\Interface\AliasingInterface;
use RuntimeException;

class AliasManager implements AliasingInterface
{
    /**
     * Global alias map: alias => abstract
     *
     * @var array<string, string>
     */
    protected array $aliases = [];

    /**
     * Cached resolved aliases: alias => resolved
     *
     * @var array<string, string>
     */
    protected array $aliasResolutionCache = [];

    /**
     * Group => [aliases]
     *
     * @var array<string, array<int, string>>
     */
    protected array $aliasGroups = [];
    /**
     * Track alias usage: alias => count
     *
     * @var array<string, int>
     */
    protected array $aliasUsageCount = [];

    /**
     * Scoped aliasing: scope => [alias => abstract]
     *
     * @var array<string, array<string, string>>
     */
    protected array $scopedAliases = [];
    /**
     * Named alias profiles for hot-swappable sets
     *
     * @var array<string, array<string, string>>
     */
    protected array $aliasProfiles = [];


    /**
     * List of callable context resolvers
     *
     * @var array<callable>
     */
    protected array $contextResolvers = [];
    /**
     * Hooks for resolution lifecycle events
     *
     * @var array<string, callable[]>
     */
    protected array $resolutionHooks = [
        'before' => [],
        'after' => [],
    ];

    /**
     * Whether resolution logging is enabled
     *
     * @var bool
     */
    protected bool $enableLogging = false;

    /**
     * Internal alias resolution logs
     *
     * @var array<int, array<string, mixed>>
     */
    protected array $resolutionLogs = [];


    // ─────────────────────────────────────────────
    // Core Alias Operations
    // ─────────────────────────────────────────────

    public function alias(string $abstract, string $alias): void
    {
        if (isset($this->aliases[$alias]) && $this->aliases[$alias] !== $abstract) {
            trigger_error("Alias '{$alias}' already points to '{$this->aliases[$alias]}'. Overwriting may cause resolution conflicts.", E_USER_WARNING);
        }

        $this->aliases[$alias] = $abstract;
        unset($this->aliasResolutionCache[$alias]);
    }

    public function isAlias(string $alias): bool
    {
        return array_key_exists($alias, $this->aliases);
    }

    public function getAbstractForAlias(string $alias): ?string
    {
        return $this->aliases[$alias] ?? null;
    }

    public function getAliasesForAbstract(string $abstract): array
    {
        return array_keys(array_filter($this->aliases, fn($a) => $a === $abstract));
    }

    public function getAliases(): array
    {
        return $this->aliases;
    }

    public function removeAlias(string $alias): void
    {
        unset($this->aliases[$alias], $this->aliasResolutionCache[$alias]);

        foreach ($this->aliasGroups as $group => &$groupAliases) {
            $groupAliases = array_filter($groupAliases, fn($a) => $a !== $alias);
            if (empty($groupAliases)) {
                unset($this->aliasGroups[$group]);
            }
        }
    }

    // ─────────────────────────────────────────────
    // Alias Profiling
    // ─────────────────────────────────────────────
    public function defineProfile(string $name, array $aliases): void
    {
        $this->aliasProfiles[$name] = $aliases;
    }

    public function activateProfile(string $name): void
    {
        if (!isset($this->aliasProfiles[$name])) {
            throw new RuntimeException("Alias profile '{$name}' not found.");
        }

        foreach ($this->aliasProfiles[$name] as $alias => $abstract) {
            $this->alias($abstract, $alias);
        }
    }

    public function removeProfile(string $name): void
    {
        unset($this->aliasProfiles[$name]);
    }

    public function getProfiles(): array
    {
        return $this->aliasProfiles;
    }
    public function detectConflicts(): array
    {
        $duplicates = [];
        $seen = [];

        foreach ($this->aliases as $alias => $abstract) {
            if (in_array($abstract, $seen)) {
                $duplicates[] = ['alias' => $alias, 'abstract' => $abstract];
            }
            $seen[] = $abstract;
        }

        return $duplicates;
    }

    public function detectCircularAliases(): array
    {
        $circular = [];

        foreach ($this->aliases as $alias => $_) {
            $path = $this->getAliasResolutionPath($alias);

            if ($path && $path[0] === end($path) && count($path) > 1) {
                $circular[] = $path;
            }
        }

        return $circular;
    }
    public function exportForDashboard(): array
    {
        return [
            'aliases' => $this->aliases,
            'groups' => $this->aliasGroups,
            'scoped' => $this->scopedAliases,
            'profiles' => $this->aliasProfiles,
            'conflicts' => $this->detectConflicts(),
            'circular' => $this->detectCircularAliases(),
            'logs' => $this->getResolutionLogs(),
        ];
    }

    // ─────────────────────────────────────────────
    // Alias Logging
    // ─────────────────────────────────────────────
    public function enableLogging(bool $enabled = true): void
    {
        $this->enableLogging = $enabled;
    }

    public function getResolutionLogs(): array
    {
        return $this->resolutionLogs;
    }

    public function clearResolutionLogs(): void
    {
        $this->resolutionLogs = [];
    }

    public function onBeforeResolve(callable $callback): void
    {
        $this->resolutionHooks['before'][] = $callback;
    }

    public function onAfterResolve(callable $callback): void
    {
        $this->resolutionHooks['after'][] = $callback;
    }

    // ─────────────────────────────────────────────
    // Alias Resolution
    // ─────────────────────────────────────────────

    public function resolveAlias(string $name, ?string $scope = null, array $context = []): string
    {
        $context = array_merge($this->resolveContext(), $context);
        $scope ??= $context['scope'] ?? null;

        foreach ($this->resolutionHooks['before'] as $callback) {
            $callback($name, $scope, $context);
        }

        $original = $name;
        $visited = [];

        try {
            if ($scope && isset($this->scopedAliases[$scope][$name])) {
                // Scoped resolution
                while (isset($this->scopedAliases[$scope][$name])) {
                    if (in_array($name, $visited, true)) {
                        throw new RuntimeException("Circular scoped alias detected in '{$scope}': " . implode(" -> ", $visited) . " -> $name");
                    }
                    $visited[] = $name;
                    $name = $this->scopedAliases[$scope][$name];
                }
            } else {
                // Global resolution with cache
                if (isset($this->aliasResolutionCache[$name])) {
                    $this->trackUsage($name);
                    $resolved = $this->aliasResolutionCache[$name];
                } else {
                    while (isset($this->aliases[$name])) {
                        if (in_array($name, $visited, true)) {
                            throw new RuntimeException("Circular alias detected: " . implode(" -> ", $visited) . " -> $name");
                        }
                        $visited[] = $name;
                        $name = $this->aliases[$name];
                    }
                    $resolved = $name;
                    $this->aliasResolutionCache[$original] = $resolved;
                }
            }
        } catch (\Throwable $e) {
            $suggestion = $this->suggestAlias($original, $scope);
            throw new RuntimeException(
                "Alias '{$original}' could not be resolved" . ($suggestion ? ". Did you mean '{$suggestion}'?" : '.'),
                0,
                $e
            );
        }

        $this->trackUsage($original);

        foreach ($this->resolutionHooks['after'] as $callback) {
            $callback($original, $resolved, $scope, $context);
        }

        if ($this->enableLogging) {
            $this->resolutionLogs[] = [
                'alias' => $original,
                'resolved' => $resolved,
                'scope' => $scope,
                'context' => $context,
                'visited' => $visited,
                'timestamp' => microtime(true),
            ];
        }

        return $resolved;
    }
    public function getAliasUsageStats(): array
    {
        arsort($this->aliasUsageCount);
        return $this->aliasUsageCount;
    }
    public function resolveGuess(string $className): ?string
    {
        $guess = $this->suggestAlias($className);
        return $this->aliases[$guess] ?? null;
    }
    public function exportResolutionHistory(): array
    {
        return $this->aliasUsageCount; // This can be stored to disk or sent to SigawaxAI
    }

    protected function trackUsage(string $alias): void
    {
        if (!isset($this->aliasUsageCount[$alias])) {
            $this->aliasUsageCount[$alias] = 0;
        }
        $this->aliasUsageCount[$alias]++;
    }
    protected function suggestAlias(string $input, ?string $scope = null): ?string
    {
        $candidates = $scope && isset($this->scopedAliases[$scope])
            ? array_keys($this->scopedAliases[$scope])
            : array_keys($this->aliases);

        $closest = null;
        $shortest = PHP_INT_MAX;

        foreach ($candidates as $alias) {
            $lev = levenshtein($input, $alias);

            if ($lev < $shortest) {
                $shortest = $lev;
                $closest = $alias;
            }
        }

        return $shortest <= 3 ? $closest : null;
    }

    protected function resolveAliasGlobally(string $name): string
    {
        if (isset($this->aliasResolutionCache[$name])) {
            return $this->aliasResolutionCache[$name];
        }

        $visited = [];
        $original = $name;

        while (isset($this->aliases[$name])) {
            if (in_array($name, $visited, true)) {
                throw new RuntimeException("Circular alias detected: " . implode(" -> ", $visited) . " -> $name");
            }
            $visited[] = $name;
            $name = $this->aliases[$name];
        }

        return $this->aliasResolutionCache[$original] = $name;
    }

    public function getAliasResolutionPath(string $alias): array
    {
        $path = [];

        while (isset($this->aliases[$alias])) {
            $path[] = $alias;
            $alias = $this->aliases[$alias];
        }

        $path[] = $alias;
        return $path;
    }

    // ─────────────────────────────────────────────
    // Groups
    // ─────────────────────────────────────────────

    public function assignAliasToGroup(string $alias, string $group): void
    {
        if (!isset($this->aliases[$alias])) {
            trigger_error("Cannot assign unregistered alias '{$alias}' to group '{$group}'.", E_USER_WARNING);
            return;
        }

        if (!isset($this->aliasGroups[$group])) {
            $this->aliasGroups[$group] = [];
        }

        if (!in_array($alias, $this->aliasGroups[$group], true)) {
            $this->aliasGroups[$group][] = $alias;
        }
    }

    public function getAliasesInGroup(string $group): array
    {
        return $this->aliasGroups[$group] ?? [];
    }

    public function getAliasGroups(): array
    {
        return $this->aliasGroups;
    }

    // ─────────────────────────────────────────────
    // Scoped Aliases
    // ─────────────────────────────────────────────

    public function aliasInScope(string $abstract, string $alias, string $scope): void
    {
        if (!isset($this->scopedAliases[$scope])) {
            $this->scopedAliases[$scope] = [];
        }

        $this->scopedAliases[$scope][$alias] = $abstract;
    }

    public function resolveAliasInScope(string $alias, string $scope): string
    {
        $aliases = $this->scopedAliases[$scope] ?? [];
        $visited = [];

        while (isset($aliases[$alias])) {
            if (in_array($alias, $visited, true)) {
                throw new RuntimeException("Circular alias in scope '{$scope}': " . implode(" -> ", $visited) . " -> $alias");
            }

            $visited[] = $alias;
            $alias = $aliases[$alias];
        }

        return $alias;
    }

    // ─────────────────────────────────────────────
    // Context Resolvers
    // ─────────────────────────────────────────────

    public function registerContextResolver(callable $resolver): void
    {
        $this->contextResolvers[] = $resolver;
    }

    public function resolveContext(): array
    {
        $context = [];

        foreach ($this->contextResolvers as $resolver) {
            $result = $resolver();
            if (is_array($result)) {
                $context = array_merge($context, $result);
            }
        }

        return $context;
    }
}
