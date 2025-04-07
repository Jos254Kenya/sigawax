<?php 

namespace Sigawa\Sigawax\Core\System;

use Sigawa\Sigawax\Core\Contracts\System\PathResolverInterface;

class PathResolver implements PathResolverInterface
{
    protected string $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, DIRECTORY_SEPARATOR);
    }

    public function base(string $path = ''): string
    {
        return $this->resolve($this->basePath, $path);
    }

    public function config(string $file = ''): string
    {
        return $this->resolve($this->basePath . '/config', $file);
    }

    public function storage(string $path = ''): string
    {
        return $this->resolve($this->basePath . '/storage', $path);
    }

    public function resource(string $path = ''): string
    {
        return $this->resolve($this->basePath . '/resources', $path);
    }

    protected function resolve(string $root, string $path = ''): string
    {
        return $path ? $root . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : $root;
    }
}
