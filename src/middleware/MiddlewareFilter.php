<?php

namespace ntentan\middleware;

use ntentan\Context;
use ntentan\http\Uri;
use Psr\Http\Message\ServerRequestInterface;

class MiddlewareFilter
{
    private Context $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    public function prefix(string $prefix): callable
    {
        return function(ServerRequestInterface $request) use ($prefix) {
            $uri = $request->getUri();
            $path = $uri->getPath();
            if (str_starts_with($path, $prefix)) {
                $path = substr($_SERVER['REQUEST_URI'], strlen($prefix));
                $path = $path == "" ? '/' : $path;
                if ($uri instanceof Uri) {
                    $uri->withPrefix($prefix);
                }
                $request->withUri($uri->withPath($path), true);
                $this->context->setPrefix($prefix);
                return true;
            }
        };
    }
}