<?php
namespace ntentan\http\filters;

use Psr\Http\Message\ServerRequestInterface;
use Override;


class Route implements RequestFilter
{
    private string $route;
    protected string $method;

    public function __construct(string $route)
    {
        $this->route = $route;
    }

    function match(ServerRequestInterface $request): bool
    {
        return strtolower($request->getMethod()) == strtolower($this->type);
    }

    public static function compileRoute(string $pattern): array
    {
        $variables = [];

        // Generate a PCRE regular expression from pattern
        $regexp = preg_replace_callback(
                "/{(?<prefix>\*|\#)?(?<name>[a-z_][a-zA-Z0-9\_]*)}/", function ($matches) use (&$variables) {
                $variables[] = $matches['name'];
                return sprintf(
                    "(?<{$matches['name']}>[a-z0-9_.~:#[\]@!$&'()*+,;=%s\s-]+)?",
                    $matches['prefix'] != '' ? "\-/_" : null
                );
            },
            str_replace('/', '(/)*', $pattern)
        );

        return [$regexp, $variables];
    }
}
