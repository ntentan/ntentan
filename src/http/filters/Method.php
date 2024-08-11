<?php
namespace ntentan\http\filters;

use Psr\Http\Message\ServerRequestInterface;
use Attribute;
use ntentan\mvc\ControllerSpec;

#[Attribute(Attribute::TARGET_METHOD)]
class Method implements RequestFilter
{
    private string $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }    

    public function getType(): string
    {
        return $this->type;
    }
    public function match(ServerRequestInterface $request): bool
    {
        return strtolower($request->getMethod()) == strtolower($this->type);
    }

}
