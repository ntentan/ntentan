<?php
namespace ntentan\http\filters;

use Psr\Http\Message\ServerRequestInterface;
use Override;

class Route implements RequestFilter
{
    #[Override]
    public function match(ServerRequestInterface $request): bool
    {
        throw new \Exception('Not implemented');
    }
}
