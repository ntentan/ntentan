<?php
namespace ntentan\http\filters;

use Psr\Http\Message\ServerRequestInterface;


class Route implements RequestFilter
{
    private string $regexp;
    private array $values;
    protected string $method;

    public function __construct(string $route)
    {
        $this->regexp = self::compile($route);
    }

    function match(ServerRequestInterface $request): bool
    {
        if (strtolower($request->getMethod()) == strtolower($this->method)
            && preg_match("|^{$this->regexp}$|i", urldecode($request->getUri()->getPath()), $matches)
        ) {
            $this->values = $matches;
            return true;
        }
        return false;
    }

    public static function compile(string $pattern): string
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

        return $regexp;
    }

    public function getValues(): array
    {
        return $this->values;
    }
}
