<?php  
namespace Sigawa\Sigawax\Core\Traits;

use ReflectionClass;
use ReflectionMethod;
use Sigawa\Sigawax\Core\Contracts\Interface\AI\AIAdapterInterface;
use Sigawa\Sigawax\Core\Exceptions\ServiceNotFoundException;

trait HasAIIntelligence
{
    protected ?AIAdapterInterface $aiAdapter = null;

    public function setAIAdapter(AIAdapterInterface $adapter): void
    {
        $this->aiAdapter = $adapter;
    }

    public function suggest(string $context = ''): array
    {
        $hints = [
            'http' => ['HttpClientInterface', 'RequestLogger'],
            'db' => ['DatabaseManager', 'ConnectionResolver'],
            'cache' => ['CacheManager', 'RedisAdapter'],
            'ai' => ['PromptBuilder', 'LLMService'],
        ];

        foreach ($hints as $key => $services) {
            if (str_contains(strtolower($context), $key)) {
                return $services;
            }
        }

        return ['Logger', 'Config', 'EventBus'];
    }

    public function predict(string $namespace): array
    {
        $predicted = [];
        $mapPath = base_path('vendor/composer/autoload_psr4.php');
        if (!file_exists($mapPath)) return [];

        $map = require $mapPath;
        foreach ($map as $ns => $paths) {
            if (str_starts_with($ns, $namespace)) {
                foreach ((array) $paths as $path) {
                    $files = glob($path . '/*.php');
                    foreach ($files as $file) {
                        $class = $ns . basename($file, '.php');
                        if (class_exists($class)) $predicted[] = $class;
                    }
                }
            }
        }

        return $predicted;
    }

    public function suggestRefactor(string $abstract): array
    {
        if ($this->aiAdapter) {
            return $this->aiAdapter->suggestRefactor($abstract);
        }

        if (!class_exists($abstract)) {
            throw new ServiceNotFoundException("[$abstract] not found for refactor suggestion.");
        }

        $reflection = new ReflectionClass($abstract);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        $longMethods = [];

        foreach ($methods as $method) {
            if ($method->getNumberOfParameters() > 4) {
                $longMethods[] = $method->getName();
            }
        }

        return [
            'longMethods' => $longMethods,
            'hasTooManyResponsibilities' => count($methods) > 15,
            'missingPHPDocs' => !$reflection->getDocComment(),
        ];
    }

    public function explainDependencies(string $abstract, bool $withAI = true): string
    {
        if (!class_exists($abstract)) {
            return "Class [$abstract] not found.";
        }

        if ($withAI && $this->aiAdapter) {
            return $this->aiAdapter->explainDependencies($abstract);
        }

        $reflection = new ReflectionClass($abstract);
        $constructor = $reflection->getConstructor();
        if (!$constructor) {
            return "$abstract has no constructor dependencies.";
        }

        $deps = [];
        foreach ($constructor->getParameters() as $param) {
            $type = $param->getType();
            $deps[] = $type ? $type->getName() : 'mixed';
        }

        return "$abstract depends on:\n- " . implode("\n- ", $deps);
    }
}