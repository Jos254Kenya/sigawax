<?php  
namespace Sigawa\Sigawax\Core\Exceptions;

use Exception;

class BindingResolutionException extends Exception
{
    protected $message = 'The container failed to resolve a binding or dependency.';
}