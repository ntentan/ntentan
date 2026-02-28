<?php

namespace ntentan\middleware\filters;

use ntentan\Context;
use ntentan\http\Uri;
use Psr\Http\Message\ServerRequestInterface;

class PrefixFilter implements ConfigurableFilter
{
    private ServerRequestInterface $request;
    private Context $context;
    private string $prefix;

    public function __construct(ServerRequestInterface $request, Context $context)
    {
        $this->request = $request;
        $this->context = $context;
    }

    public function configure(string $prefix): void
    {
        $this->prefix = $prefix;
    }

    public function filter(array $params): bool
    {
        $uri = $this->request->getUri();
        $path = $uri->getPath();
        if (str_starts_with($path, $this->prefix)) {
            $path = substr($path, strlen($this->prefix));
            $path = $path == "" ? '/' : $path;
            
            // If the uri is an instance of ntentan\http\Uri, we can use the withPrefix method
            // to set the prefix. This is not a standard method of the UriInterface interface.
            if ($uri instanceof Uri) {
                $uri->withPrefix($this->prefix);
            }
            $this->request->withUri($uri->withPath($path), true);
            $this->context->setPrefix($this->prefix);
            return true;
        }
        return false;
    }
}