<?php 

namespace Sigawa\Sigawax\Core\Contracts\System;

interface PathResolverInterface
{
    public function base(string $path = ''): string;
    public function config(string $file = ''): string;
    public function storage(string $path = ''): string;
    public function resource(string $path = ''): string;
}
