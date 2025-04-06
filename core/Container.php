<?php

namespace Sigawa\Sigawax\Core;


use Closure;
use InvalidArgumentException;
use RuntimeException;
use Sigawa\Sigawax\Core\Contracts\ContainerInterface;
use Sigawa\Sigawax\Core\Contracts\ServiceProviderInterface;

class Container implements ContainerInterface
{
    /**
     * The container’s bindings.
     */
    protected array $bindings = [];

    /**
     * Shared instances (singletons).
     */
    protected array $instances = [];

    /**
     * Services tagged by group name.
     */
    protected array $tags = [];

    /**
     * Contextual bindings by concrete + abstraction.
     */
    protected array $contextual = [];

    /**
     * Lifecycle callbacks.
     */
    protected array $resolvingCallbacks = [];
    protected array $afterResolvingCallbacks = [];
    protected array $onBindCallbacks = [];
    protected array $globalOnBindCallbacks = [];
    protected $singletons = [];
    protected $resolvedInstances = [];
    protected $contextualBindings = [];
    // Initialize service providers property
    protected array $serviceProviders = [];

    /**
     * Parameters bag.
     */
    protected array $parameters = [];

    /**
     * Registered service providers.
     */
    protected array $providers = [];

    /**
     * Scopes.
     */
    protected array $scopes = [];

    /**
     * Currently building stack (for contextual binding).
     */
    protected array $buildStack = [];
    /**
     * Bind an abstract type to a concrete implementation.
     */
    public function bind(string $abstract, $concrete, bool $shared = false): void
    {
        $this->bindings[$abstract] = compact('concrete', 'shared');

        // Trigger onBind lifecycle hooks
        foreach ($this->onBindCallbacks[$abstract] ?? [] as $callback) {
            $callback($this);
        }
    }

    /**
     * Bind a singleton.
     */
    public function singleton(string $abstract, $concrete): void
    {
        $this->bind($abstract, $concrete, true);
    }
    public function call($callable, array $parameters = [])
    {
        return call_user_func_array($callable, $parameters);
    }
    public function explain(string $abstract): array
    {
        if (!$this->has($abstract)) {
            return [];
        }

        $bindings = $this->getBindings();
        // Fetching the relevant binding information for the service
        return isset($bindings[$abstract]) ? $bindings[$abstract] : [];
    }
    public function forget(string $abstract): void
    {
        unset($this->bindings[$abstract]);
    }
    public function getBindings(): array
    {
        return $this->bindings;
    }

    /**
     * Check if a binding exists.
     */
    public function has(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]);
    }
    public function getContextualBindings(): array
    {
        return $this->contextualBindings;
    }

    /**
     * Resolve a type from the container.
     */
    public function make(string $abstract, array $parameters = [])
    {
        // Check contextual binding first
        $concrete = $this->getConcrete($abstract);

        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // Build the object with parameters
        $object = $this->build($concrete, $parameters);

        // If it's shared, cache the instance
        if ($this->isShared($abstract)) {
            $this->instances[$abstract] = $object;
        }

        // Trigger lifecycle hooks
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
    public function register(ServiceProviderInterface $provider): void
    {
        if (!$this->isRegistered(get_class($provider))) {
            $provider->register($this);
            $this->serviceProviders[get_class($provider)] = $provider;
        }
    }

    /**
     * Get the concrete implementation for the given abstract type.
     */
    protected function getConcrete(string $abstract)
    {
        $context = end($this->buildStack);

        if ($context && isset($this->contextual[$context][$abstract])) {
            return $this->contextual[$context][$abstract];
        }

        return $this->bindings[$abstract]['concrete'] ?? $abstract;
    }

    /**
     * Build a class instance (resolving dependencies).
     */
    protected function build($concrete, array $parameters = [])
    {
        if ($concrete instanceof Closure) {
            return $concrete($this, $parameters);
        }

        if (!class_exists($concrete)) {
            throw new RuntimeException("Class [$concrete] does not exist.");
        }

        $reflector = new \ReflectionClass($concrete);

        if (!$reflector->isInstantiable()) {
            throw new RuntimeException("Class [$concrete] is not instantiable.");
        }

        $this->buildStack[] = $concrete;

        $constructor = $reflector->getConstructor();

        if (!$constructor) {
            array_pop($this->buildStack);
            return new $concrete;
        }

        $dependencies = $constructor->getParameters();
        $instances = [];

        foreach ($dependencies as $dependency) {
            $name = $dependency->getName();
            $type = $dependency->getType();

            //  Check for user-supplied parameters first
            if (array_key_exists($name, $parameters)) {
                $instances[] = $parameters[$name];
            }
            // 🔁 Auto-resolve class dependencies
            elseif ($type && !$type->isBuiltin()) {
                $instances[] = $this->make($type->getName());
            }
            // 🧪 Use default value if available
            elseif ($dependency->isDefaultValueAvailable()) {
                $instances[] = $dependency->getDefaultValue();
            }
            //  Can't resolve? Cry loudly
            else {
                throw new RuntimeException("Unresolvable dependency [\${$name}] in class {$concrete}");
            }
        }

        array_pop($this->buildStack);

        return $reflector->newInstanceArgs($instances);
    }
    public function getResolvedInstances(): array
    {
        return $this->resolvedInstances;
    }
    public function getTags(): array
    {
        return $this->tags;
    }
    public function instance(string $abstract, $instance): void
    {
        $this->resolvedInstances[$abstract] = $instance;
    }
    public function isRegistered(string $providerClass): bool
    {
        return isset($this->serviceProviders[$providerClass]);
    }
    public function isSingleton(string $abstract): bool
    {
        return isset($this->singletonBindings[$abstract]);
    }
    public function listDependencies(string $abstract): array
    {
        $dependencies = [];
        // Here, inspect the binding and fetch the dependencies (if any)
        return $dependencies;
    }

    /**
     * Resolve all dependencies via `make()`.
     */
    protected function resolveDependencies(array $dependencies): array
    {
        $results = [];

        foreach ($dependencies as $dependency) {
            $type = $dependency->getType();

            if ($type && !$type->isBuiltin()) {
                $results[] = $this->make($type->getName());
            } elseif ($dependency->isDefaultValueAvailable()) {
                $results[] = $dependency->getDefaultValue();
            } else {
                throw new RuntimeException("Unresolvable dependency: {$dependency->getName()}");
            }
        }

        return $results;
    }
    /**
     * Bind a concrete service based on a specific context.
     */
    /**
     * Bind a concrete service based on a specific context.
     */
    public function bindWhen(string $abstract, string $context, $concrete): void
    {
        $this->contextual[$context][$abstract] = $concrete;
    }
    /**
     * Tag a group of services with a specific tag.
     */
    public function tag(string $tag, array $services): void
    {
        foreach ($services as $service) {
            $this->tags[$tag][] = $service;
        }
    }
    /**
     * Resolve services by their tags.
     */
    public function tagged(string $tag): array
    {
        if (!isset($this->tags[$tag])) {
            return [];
        }

        $instances = [];
        foreach ($this->tags[$tag] as $abstract) {
            $instances[] = $this->make($abstract);
        }

        return $instances;
    }
    /**
     * Register a resolving callback for a given service.
     */
    public function resolving(string $abstract, $callback): void
    {
        $this->resolvingCallbacks[$abstract][] = $callback;
        if (!is_callable($callback)) {
            throw new InvalidArgumentException("The resolving callback for [$abstract] must be callable.");
        }
    }

    /**
     * Register an after-resolving callback for a given service.
     */
    public function afterResolving(string $abstract, $callback): void
    {
        $this->afterResolvingCallbacks[$abstract][] = $callback;
    }

    /**
     * Register an onBind callback for a given service.
     */
    public function onBind(callable $callback, ?string $abstract = null): void
    {
        if ($abstract) {
            $this->onBindCallbacks[$abstract][] = $callback;
        } else {
            $this->globalOnBindCallbacks[] = $callback;
        }
    }

    /**
     * Set a parameter in the container.
     */
    public function setParameter(string $key, $value): void
    {
        $this->parameters[$key] = $value;
    }

    /**
     * Get a parameter from the container.
     */
    public function getParameter(string $key)
    {
        return $this->parameters[$key] ?? null;
    }

    /**
     * Begin a new scope and optionally execute a callback within it.
     */
    public function scope(string $name, ?Closure $callback = null)
    {
        $this->scopes[$name] = [];

        if ($callback) {
            try {
                return $callback($this);
            } finally {
                // You can choose to keep or remove the scope after the callback
                unset($this->scopes[$name]);
            }
        }
    }


    /**
     * Forget a scope.
     */
    public function forgetScope(string $scopeName): void
    {
        unset($this->scopes[$scopeName]);
    }

    /**
     * Get all scoped bindings for a specific scope.
     */
    public function getScopedBindings(string $scopeName): array
    {
        return $this->scopes[$scopeName] ?? [];
    }

    /**
     * Bind a concrete service within a scope.
     */
    public function bindInScope(string $abstract, $concrete, string $scopeName): void
    {
        if (!isset($this->scopes[$scopeName])) {
            throw new RuntimeException("Scope [$scopeName] is not active.");
        }

        $this->scopes[$scopeName][$abstract] = $concrete;
    }

    /**
     * Resolve a binding from within a specific scope.
     */
    public function resolveInScope(string $abstract, string $scopeName)
    {
        if (isset($this->scopes[$scopeName][$abstract])) {
            return $this->make($this->scopes[$scopeName][$abstract]);
        }

        return $this->make($abstract); // Resolve normally if not in scope
    }

    /**
     * Suggest potential services or bindings based on the given context.
     * This is where you can enhance with AI-powered recommendations in the future.
     */
    public function suggest(string $context = ''): array
    {
        $suggestions = [];

        // Example logic for suggesting bindings based on the context (formerly abstract)
        if ($context === 'App\\Services\\Mailer') {
            $suggestions[] = 'App\\Services\\SMTPMailer';
            $suggestions[] = 'App\\Services\\SendGridMailer';
        }

        // Additional logic for more service suggestions could go here

        return $suggestions;
    }

    /**
     * Predict the most likely service to bind based on the given context.
     * You can improve this logic with historical data or trends.
     */
    public function predict(string $namespace): array
    {
        $prediction = [];

        // Example prediction logic based on namespace
        if ($namespace === 'App\\Services\\PaymentGateway') {
            $prediction[] = 'App\\Services\\StripePaymentGateway'; // Predict Stripe as the most common payment gateway
        }

        // You could add more predictions for other namespaces if needed
        return $prediction;
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
     * Register a service provider manually.
     */
    public function registerProvider(string $providerFile): void
    {
        // Include the provider file
        require_once $providerFile;

        // Assuming the provider class is the same name as the file, e.g., MyServiceProvider.php -> MyServiceProvider
        $providerClass = basename($providerFile, '.php');

        if (class_exists($providerClass)) {
            $provider = new $providerClass($this);
            $this->providers[] = $provider;

            // Register the provider
            $provider->register();

            // Boot the provider after it has been registered
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
        // Capture bindings and parameters
        $state = [
            'bindings' => $this->bindings,
            'singletons' => $this->singletons,
            'parameters' => $this->parameters,
            'scopes' => $this->scopes
        ];

        // Optionally, export tagged services and contextual bindings if needed
        $state['tags'] = $this->tags;
        $state['contextual'] = $this->contextual;

        return $state; // Return the state as an array
    }

    /**
     * Import a saved snapshot to restore the container's state.
     * 
     * @param array $state The array representing the saved container state.
     */
    public function importState(array $state): void
    {
        // Validate the state array to ensure it has all the necessary keys
        if (!is_array($state)) {
            throw new InvalidArgumentException("State must be an array.");
        }

        // Restore bindings, singletons, parameters, etc.
        $this->bindings = $state['bindings'] ?? [];
        $this->singletons = $state['singletons'] ?? [];
        $this->parameters = $state['parameters'] ?? [];
        $this->scopes = $state['scopes'] ?? [];

        // Restore tagged and contextual bindings if needed
        $this->tags = $state['tags'] ?? [];
        $this->contextual = $state['contextual'] ?? [];
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
}
