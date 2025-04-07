<?php 

namespace Sigawa\Sigawax\Core\Contracts\Interface;
interface AsyncInterface {
    
    /**
     * Asynchronously resolve an instance.
     *
     * @param string $abstract
     * @param array $parameters
     * @return mixed  // Ideally a Promise or Future type
     */
    public function makeAsync(string $abstract, array $parameters = []);

}