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
class Container1 implements ContainerInterface
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
 * The registered aliases.
 *
 * @var array<string, string>
 */
protected array $aliases = [];

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

    public function forgetScope(string $name): void
    {
        // No-op for now
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

    public function predict(string $namespace): array
    {
        return array_filter(array_keys($this->bindings), fn($key) => str_starts_with($key, $namespace));
    }

    public function setParameter(string $key, $value): void
    {
        $this->parameters[$key] = $value;
    }

    public function getParameter(string $key)
    {
        return $this->parameters[$key] ?? null;
    }

    public function register(ServiceProviderInterface $provider): void
    {
        $class = get_class($provider);
        if (!$this->isRegistered($class)) {
            $provider->register($this);
            $this->serviceProviders[$class] = $provider;
        }
    }

    public function isRegistered(string $providerClass): bool
    {
        return isset($this->serviceProviders[$providerClass]);
    }

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

    public function importState(array $state): void
    {
        $this->bindings = $state['bindings'] ?? [];
        $this->instances = $state['instances'] ?? [];
        $this->shared = $state['shared'] ?? [];
        $this->tags = $state['tags'] ?? [];
        $this->parameters = $state['parameters'] ?? [];
    }
}
