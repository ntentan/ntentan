<?php
namespace ntentan\http\filters;

use Psr\Http\Message\ServerRequestInterface;
use ntentan\mvc\ControllerSpec;

interface RequestFilter 
{
    function match(ServerRequestInterface $request): bool;
}

