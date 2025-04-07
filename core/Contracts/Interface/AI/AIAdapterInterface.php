<?php 
namespace Sigawa\Sigawax\Core\Contracts\Interface\AI;
interface AIAdapterInterface {
      /**
     * Suggest useful services or improvements based on context.
     *
     * @param string $context
     * @return array
     */
    public function suggest(string $context = ''): array;

    /**
     * Predict services or methods in a given namespace.
     *
     * @param string $namespace
     * @return array
     */
    public function predict(string $namespace): array;

    /**
     * Suggest refactoring or improvements for a given abstract.
     *
     * @param string $class
     * @return array
     */
    public function suggestRefactor(string $class): array;
     /**
     * Whether this AI adapter is currently usable (e.g. has valid API keys).
     */
    public function isEnabled(): bool;

    /**
     * Explain dependencies in detail for a given abstract.
     *
     * @param string $abstract
     * @param bool $withAI
     * @return string
     */
    public function explainDependencies(string $abstract, bool $withAI = true): string;
    public function analyze(string $code): string;

    public function complete(string $prompt): string;
}