<?php  

namespace Sigawa\Sigawax\Core\Contracts\Interface;
interface DebuggingInterface{
      
    public function getBindings(): array;

    public function getContextualBindings(): array;

    public function getResolvedInstances(): array;

    public function explain(string $abstract): array;

    public function listDependencies(string $abstract): array;
}