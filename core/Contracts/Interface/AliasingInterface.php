<?php 

namespace Sigawa\Sigawax\Core\Contracts\Interface;
interface AliasingInterface {
    /**
     * Define an alias for an abstract type.
     *
     * @param string $abstract
     * @param string $alias
     */
    public function alias(string $abstract, string $alias): void;

}