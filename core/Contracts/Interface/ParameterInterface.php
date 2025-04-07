<?php 

namespace Sigawa\Sigawax\Core\Contracts\Interface;
interface ParameterInterface{
    
    public function setParameter(string $key, $value): void;

    public function getParameter(string $key);
}