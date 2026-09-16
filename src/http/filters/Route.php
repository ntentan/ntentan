<?php
namespace ntentan\http\filters;

use Psr\Http\Message\ServerRequestInterface;
use Override;


abstract class Route implements RequestFilter
{
    private string $route;

    public function __construct(string $route)
    {
        $this->route = $route;
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
