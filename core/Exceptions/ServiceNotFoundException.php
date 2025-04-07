<?php
namespace Sigawa\Sigawax\Core\Exceptions;

use Exception;

class ServiceNotFoundException extends Exception
{
    protected $message = 'The requested service could not be found in the container.';
}