<?php 

namespace Sigawa\Sigawax\Core\Contracts\Interface;
interface AutoWireInterface {
     /**
     * Enable autowiring for the given class.
     *
     * @param string $class
     */
    public function enableAutowire(string $class): void;

    /**
     * Disable autowiring for the given class.
     *
     * @param string $class
     */
    public function disableAutowire(string $class): void;


}