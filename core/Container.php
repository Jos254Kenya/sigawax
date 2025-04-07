<?php

use Sigawa\Sigawax\Core\Contracts\ContainerInterface;
use Sigawa\Sigawax\Core\Contracts\ServiceProviderInterface;
use Sigawa\Sigawax\Core\Exceptions\BindingResolutionException;
use Sigawa\Sigawax\Core\Exceptions\ServiceNotFoundException;

/**
 * Class Container
 * 
 * This class serves as the service container for Sigawax framework. 
 * It manages bindings, service resolution, AI-powered predictions, and more.
 */
class Container implements ContainerInterface
{
    /**
     * container  bindings
     * @var array
     */
    protected array $bindings = [];
    /**
     * Shared instances (singletons).
     *  @var array
     */
    protected array $instances = [];

    protected array $shared = [];
    /**
     * Contextual bindings by concrete + abstraction.
     */
    protected array $contextual = [];
    /**
     * resolvingCallbacks
     * @var array
     */
    protected array $resolvingCallbacks = [];
    /**
     * afterResolvingCallbacks
     * @var array
     */
    protected array $afterResolvingCallbacks = [];
    /**
     * Summary of onBindCallbacks
     * @var array
     */
    protected array $onBindCallbacks = [];
    /**
     * Services tagged by group name.
     * @var array
     */
    protected array $tags = [];
    /**
     * Summary of parameters
     * @var array
     */
    protected array $parameters = [];
    /**
     * Registered service providers.
     */
    protected array $providers = [];
    /**
     * The registered aliases.
     *
     * @var array<string, string>
     */
    protected array $aliases = [];
    /**
     * Summary of singletons
     * @var array
     */
    protected $singletons = [];
    /**
     * Scopes.
     */
    protected array $scopes = [];

    /**
     * Summary of serviceProviders
     * @var array
     */
    protected array $serviceProviders = [];
    /**
     * Currently building stack (for contextual binding).
     */
    protected array $buildStack = [];
    /**
     * Bind an abstract type to a concrete implementation.
     * Bind a service to the container.
     * @param string $abstract
     * @param mixed $concrete
     * @param bool $shared
     * @return void
     */

    public function bind(string $abstract, $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
        // Trigger onBind lifecycle hooks
        foreach ($this->onBindCallbacks as $callback) {
            $callback($abstract, $concrete);
        }
    }
    /**
     * Bind a singleton.
     */
    public function singleton(string $abstract, $concrete): void
    {
        $this->shared[$abstract] = true;
        $this->bind($abstract, $concrete);
    }

    public function instance(string $abstract, $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    public function bindWhen(string $abstract, string $context, $concrete): void
    {
        $this->contextual[$context][$abstract] = $concrete;
    }

    public function has(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]);
    }
    /**
     * Register an alias for a service.
     */

    /**
     * Get the real name behind an alias.
     */
    public function alias(string $alias, string $abstract): void
    {
        $this->aliases[$alias] = $abstract;
    }
    public function getAlias(string $name): string
    {
        return $this->aliases[$name] ?? $this->smartGuessAlias($name);
    }
    protected function smartGuessAlias(string $name): string
    {
        // Example logic for dynamic fallback
        $guess = str_replace(' ', '', ucwords(str_replace(['.', '-', '_'], ' ', $name)));
        if (interface_exists($guess)) {
            return $guess;
        }

        $defaultNamespace = 'App\\Services\\';
        if (class_exists($defaultNamespace . $guess)) {
            return $defaultNamespace . $guess;
        }

        throw new ServiceNotFoundException("Alias [$name] not found and cannot be guessed.");
    }


    public function isSingleton(string $abstract): bool
    {
        return isset($this->shared[$abstract]);
    }

    public function forget(string $abstract): void
    {
        unset($this->bindings[$abstract], $this->instances[$abstract], $this->shared[$abstract]);
    }
    /**
     * Resolve a service from the container.
     *
     * @param string $abstract
     * @param array $parameters
     * @return mixed
     * @throws Exception
     */
    public function make(string $abstract, array $parameters = [])
    {
        // 🧠 Resolve alias first (whether static or smart)
        $abstract = $this->getAlias($abstract);

        // ✅ Already resolved singleton?
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // ❌ Not bound? Throw
        if (!$this->has($abstract)) {
            throw new ServiceNotFoundException("Service not bound: {$abstract}");
        }

        // 🎯 Resolve concrete from binding
        $concrete = $this->getConcrete($abstract);

        // 🛠️ Build it
        $object = $this->build($concrete, $parameters);

        // 🔒 Cache if singleton/shared
        if ($this->isSingleton($abstract) || $this->isShared($abstract)) {
            $this->instances[$abstract] = $object;
        }

        // ⚙️ Trigger lifecycle hooks
        foreach ($this->resolvingCallbacks[$abstract] ?? [] as $callback) {
            $callback($object, $this);
        }
        foreach ($this->afterResolvingCallbacks[$abstract] ?? [] as $callback) {
            $callback($object, $this);
        }

        return $object;
    }

    /**
     * Check if the binding is shared (singleton).
     */
    protected function isShared(string $abstract): bool
    {
        return $this->bindings[$abstract]['shared'] ?? false;
    }
    /**
     * Get the concrete implementation for the given abstract type.
     */
    protected function getConcrete(string $abstract)
    {
        return $this->bindings[$abstract] ?? $abstract;
    }

    /**
     * Build a class instance (resolving dependencies).
     */
    protected function build($concrete, array $parameters = [])
    {
        if ($concrete instanceof Closure) {
            return $concrete($this, ...array_values($parameters)); // Spread for flexibility
        }

        if (!class_exists($concrete)) {
            throw new BindingResolutionException("Class [$concrete] does not exist.");
        }

        $this->buildStack[] = $concrete;

        $reflection = new \ReflectionClass($concrete);

        if (!$reflection->isInstantiable()) {
            throw new BindingResolutionException("Class [$concrete] is not instantiable.");
        }

        $constructor = $reflection->getConstructor();

        if (is_null($constructor)) {
            array_pop($this->buildStack);
            return new $concrete;
        }

        $dependencies = [];
        foreach ($constructor->getParameters() as $param) {
            $name = $param->getName();
            $type = $param->getType();

            if (array_key_exists($name, $parameters)) {
                $dependencies[] = $parameters[$name];
            } elseif ($type && !$type->isBuiltin()) {
                $dependencies[] = $this->make($type->getName());
            } elseif ($param->isDefaultValueAvailable()) {
                $dependencies[] = $param->getDefaultValue();
            } else {
                throw new BindingResolutionException("Unresolvable dependency [\${$name}] in class [$concrete]");
            }
        }

        array_pop($this->buildStack);

        return $reflection->newInstanceArgs($dependencies);
    }


    public function call($callable, array $parameters = [])
    {
        if (is_array($callable)) {
            $ref = new \ReflectionMethod($callable[0], $callable[1]);
        } else {
            $ref = new \ReflectionFunction($callable);
        }

        $args = [];
        foreach ($ref->getParameters() as $param) {
            $name = $param->getName();
            if (array_key_exists($name, $parameters)) {
                $args[] = $parameters[$name];
            } elseif (($type = $param->getType()) && !$type->isBuiltin()) {
                $args[] = $this->make($type->getName());
            } else {
                $args[] = null;
            }
        }

        return call_user_func_array($callable, $args);
    }

    public function resolving(string $abstract, callable $callback): void
    {
        $this->resolvingCallbacks[$abstract][] = $callback;
    }

    public function afterResolving(string $abstract, callable $callback): void
    {
        $this->afterResolvingCallbacks[$abstract][] = $callback;
    }
    /**
     * Retrieve cached data from file.
     */
    public function retrieveCache(string $key)
    {
        $cacheFile = $this->getCacheFilePath($key);

        if (!file_exists($cacheFile)) {
            return null; // No cache found
        }

        $cacheData = unserialize(file_get_contents($cacheFile));

        // Check if the cache has expired
        if ($cacheData['expires_at'] < time()) {
            $this->clearCache($key); // Clear expired cache
            return null;
        }

        return $cacheData['data']; // Return the cached data
    }
    /**
     * Clear a specific cache file.
     */
    public function clearCache(string $key): void
    {
        $cacheFile = $this->getCacheFilePath($key);
        if (file_exists($cacheFile)) {
            unlink($cacheFile); // Delete the cache file
        }
    }
    /**
     * Generate the file path for the cache.
     */
    private function getCacheFilePath(string $key): string
    {
        // Use a hash of the key as the filename to ensure uniqueness
        return __DIR__ . '/cache/' . md5($key) . '.cache';
    }
    /**
     * Cache data to a file.
     */
    public function storeCache(string $key, $data, int $ttl = 3600): void
    {
        $cacheFile = $this->getCacheFilePath($key);
        $cacheData = [
            'data' => $data,
            'expires_at' => time() + $ttl,
        ];

        file_put_contents($cacheFile, serialize($cacheData));
    }
    /**
     * Check if a specific cache exists.
     */
    public function hasCache(string $key): bool
    {
        $cacheFile = $this->getCacheFilePath($key);
        return file_exists($cacheFile) && !$this->isCacheExpired($cacheFile);
    }
    /**
     * Check if a cache has expired.
     */
    private function isCacheExpired(string $cacheFile): bool
    {
        $cacheData = unserialize(file_get_contents($cacheFile));
        return $cacheData['expires_at'] < time();
    }
    public function onBind(callable $callback): void
    {
        $this->onBindCallbacks[] = $callback;
    }

    public function tag(string $tag, array $services): void
    {
        foreach ($services as $service) {
            $this->tags[$tag][] = $service;
        }
    }

    public function tagged(string $tag): array
    {
        return $this->tags[$tag] ?? [];
    }

    public function scope(string $name, Closure $callback)
    {
        return $callback($this);
    }
     /**
     * Forget a scope.
     */
    public function forgetScope(string $scopeName): void
    {
        unset($this->scopes[$scopeName]);
    }

    public function getBindings(): array
    {
        return $this->bindings;
    }

    public function getContextualBindings(): array
    {
        return $this->contextual;
    }

    public function getResolvedInstances(): array
    {
        return $this->instances;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function explain(string $abstract): array
    {
        $alias = $this->getAlias($abstract);
        $bound = $this->has($alias);
        $bindings = $this->getBindings();
        $binding = $bindings[$alias] ?? [];

        return [
            'abstract' => $abstract,
            'alias_resolved_to' => $alias,
            'bound' => $bound,
            'concrete' => $binding['concrete'] ?? null,
            'singleton' => $binding['singleton'] ?? false,
            'shared' => $binding['shared'] ?? false,
            'resolved' => isset($this->instances[$alias]),
            'has_hooks' => isset($this->resolvingCallbacks[$alias]) || isset($this->afterResolvingCallbacks[$alias]),
            'tags' => $this->tags[$alias] ?? [],
            'contextual' => $this->contextual[$alias] ?? [],
        ];
    }

    public function listDependencies(string $abstract): array
    {
        $concrete = $this->getConcrete($abstract);
        $reflection = new \ReflectionClass($concrete);
        $constructor = $reflection->getConstructor();
        return $constructor ? array_map(fn($p) => $p->getName(), $constructor->getParameters()) : [];
    }

    public function suggest(string $context = ''): array
    {
        return array_keys($this->bindings);
    }
    /**
     * Predict the most likely service to bind based on the given context.
     * You can improve this logic with historical data or trends.
     * @var string $namespace
     */
    public function predict(string $namespace): array
    {
        return array_filter(array_keys($this->bindings), fn($key) => str_starts_with($key, $namespace));
    }

    /**
     * Summary of setParameter
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setParameter(string $key, $value): void
    {
        $this->parameters[$key] = $value;
    }

    /**
     * Summary of getParameter
     * @param string $key
     */
    public function getParameter(string $key)
    {
        return $this->parameters[$key] ?? null;
    }

    /**
     * Summary of register
     * @param Sigawa\Sigawax\Core\Contracts\ServiceProviderInterface $provider
     * @return void
     */
    public function register(ServiceProviderInterface $provider): void
    {
        $class = get_class($provider);
        if (!$this->isRegistered($class)) {
            $provider->register($this);
            $this->serviceProviders[$class] = $provider;
        }
    }

    /**
     * Summary of isRegistered
     * @param string $providerClass
     * @return bool
     */
    public function isRegistered(string $providerClass): bool
    {
        return isset($this->serviceProviders[$providerClass]);
    }
    /**
     * Export the current container state as a snapshot.
     * 
     * @return string JSON string representation of the container's state.
     */
    /**
     * Export the current state of the container as an array.
     * This includes bindings, singletons, parameters, scopes, tags, and contextual bindings.
     */

    public function exportState(): array
    {
        return [
            'bindings' => $this->bindings,
            'instances' => $this->instances,
            'shared' => $this->shared,
            'tags' => $this->tags,
            'parameters' => $this->parameters,
        ];
    }
    /**
     * Automatically discover and register all service providers in the providers/ directory.
     */
    public function discoverProviders(): void
    {
        $providerDirectory = __DIR__ . '/providers';
        $providerFiles = glob($providerDirectory . '/*.php');

        foreach ($providerFiles as $providerFile) {
            $this->registerProvider($providerFile);
        }
    }

    /**
     * Register a service provider by its fully qualified class name.
     *
     * @param string $providerClass Fully qualified provider class name.
     * @return void
     * @throws \Exception
     */
    public function registerProvider(string $providerClass): void
    {
        // Ensure the provider class exists (Composer autoloading should handle this).
        if (!class_exists($providerClass)) {
            throw new Exception("Provider class '{$providerClass}' not found.");
        }

        // Instantiate the provider, passing in the container itself.
        $provider = new $providerClass($this);

        // Optionally, store the provider for later reference (e.g., if you need to call shutdown methods).
        $this->providers[] = $provider;

        // Register the provider.
        $provider->register();

        // If the provider has a boot method, call it.
        if (method_exists($provider, 'boot')) {
            $provider->boot();
        }
    }

    /**
     * Register all providers that need to be booted.
     */
    public function bootProviders(): void
    {
        foreach ($this->providers as $provider) {
            if (method_exists($provider, 'boot')) {
                $provider->boot();
            }
        }
    }
    /**
     * Example of using the AI predictions and suggestions for improved development.
     */
    public function enhancedResolve(string $abstract)
    {
        // AI-assisted predictions and suggestions
        $predicted = $this->predict($abstract);
        $suggestions = $this->suggest($abstract);

        if (empty($suggestions)) {
            $suggestions[] = $predicted; // Fallback to predicted if no suggestions found
        }
        // You can implement intelligent logging here based on AI-assisted resolution
        return $this->make($abstract, $predicted); // Resolve based on prediction
    }

    public function importState(array $state): void
    {
        $this->bindings = $state['bindings'] ?? [];
        $this->instances = $state['instances'] ?? [];
        $this->shared = $state['shared'] ?? [];
        $this->tags = $state['tags'] ?? [];
        $this->parameters = $state['parameters'] ?? [];
    }
    /**
     * Retrieve a binding from the container.
     *
     * If the key is bound, returns it (and if it's a closure, it invokes it).
     * Otherwise, it attempts to auto-resolve the dependency via reflection.
     *
     * @param string $key
     * @return mixed
     * @throws Exception
     */
    public function get(string $key)
    {
        // If the key is already bound, return it.
        if (isset($this->bindings[$key])) {
            $value = $this->bindings[$key];

            // If the bound value is a closure, invoke it and cache the result.
            if (is_callable($value)) {
                $object = $value($this);
                $this->bindings[$key] = $object;
                return $object;
            }

            return $value;
        }

        // If not already bound, attempt to auto-resolve the class.
        if (!class_exists($key)) {
            throw new Exception("Class {$key} not found.");
        }

        try {
            $reflection = new ReflectionClass($key);
        } catch (ReflectionException $e) {
            throw new Exception("Failed to reflect class {$key}: " . $e->getMessage());
        }

        // Ensure the class is instantiable.
        if (!$reflection->isInstantiable()) {
            throw new Exception("Class {$key} is not instantiable.");
        }

        $constructor = $reflection->getConstructor();
        // If there's no constructor, just instantiate the class.
        if ($constructor === null) {
            $object = new $key;
            $this->bind($key, $object);
            return $object;
        }

        $dependencies = $constructor->getParameters();
        $resolvedDependencies = [];

        foreach ($dependencies as $dependency) {
            // Try to get the class type of the dependency.
            $dependencyClass = $dependency->getType() && !$dependency->getType()->isBuiltin()
                ? $dependency->getType()->getName()
                : null;

            if ($dependencyClass) {
                // Recursively resolve the dependency.
                $resolvedDependencies[] = $this->get($dependencyClass);
            } else {
                // If no class is hinted, use the default value if available.
                if ($dependency->isDefaultValueAvailable()) {
                    $resolvedDependencies[] = $dependency->getDefaultValue();
                } else {
                    throw new Exception("Unable to resolve dependency '{$dependency->getName()}' for class {$key}.");
                }
            }
        }

        // Instantiate the class with the resolved dependencies.
        $object = $reflection->newInstanceArgs($resolvedDependencies);

        // Optionally, bind the resolved object back to the container for caching.
        $this->bind($key, $object);

        return $object;
    }
    /**
     * Log a service binding event.
     */
    public function logBinding(string $abstract, $concrete): void
    {
        $message = "Binding: {$abstract} -> {$concrete}";
        $this->logEvent('binding', $message);
    }

    /**
     * Log a service resolution event.
     */
    public function logResolution(string $abstract, $instance): void
    {
        $message = "Resolved: {$abstract} -> " . get_class($instance);
        $this->logEvent('resolution', $message);
    }

    /**
     * Log a lifecycle event.
     */
    public function logLifecycle(string $abstract, string $event): void
    {
        $message = "Lifecycle Event: {$abstract} - {$event}";
        $this->logEvent('lifecycle', $message);
    }

    /**
     * Log AI insights.
     */
    public function logAI(string $type, string $abstract, array $insights): void
    {
        $message = "{$type} Insight: {$abstract} - " . implode(', ', $insights);
        $this->logEvent('ai', $message);
    }

    /**
     * General log event handler.
     */
    private function logEvent(string $type, string $message): void
    {
        // Determine where to log the event (e.g., file, database, external service)
        // For simplicity, we'll log to a file here.
        $logFile = __DIR__ . '/container.log';

        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] [{$type}] {$message}\n";

        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }
}
