<?php

namespace Sigawa\Sigawax\Core\Contracts\Interface;

interface AliasingInterface
{
    public function alias(string $abstract, string $alias): void;

    public function isAlias(string $alias): bool;

    public function getAbstractForAlias(string $alias): ?string;

    public function getAliasesForAbstract(string $abstract): array;

    /**
     * @return array<string, string> alias => abstract
     */
    public function getAliases(): array;

    public function removeAlias(string $alias): void;

    public function resolveAlias(string $name): string;

    public function assignAliasToGroup(string $alias, string $group): void;

    /**
     * @return string[] List of aliases in the group
     */
    public function getAliasesInGroup(string $group): array;

    /**
     * @return array<string, string[]> group => [aliases]
     */
    public function getAliasGroups(): array;

    /**
     * Define a scoped alias. Useful for module-specific or context-sensitive aliasing.
     */
    public function aliasInScope(string $abstract, string $alias, string $scope): void;

    /**
     * Resolve an alias within a given scope.
     */
    public function resolveAliasInScope(string $alias, string $scope): string;

    /**
     * Get the full alias resolution path (useful for debugging).
     *
     * @return string[] e.g. ['FooAlias', 'BarAlias', 'ConcreteClass']
     */
    public function getAliasResolutionPath(string $alias): array;
}
