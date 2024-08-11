<?php
namespace ntentan\http\filters;

use Psr\Http\Message\ServerRequestInterface;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Header implements RequestFilter
{
    public string $header;
    public string $value;
    
    public function __construct(string $header, string $value)
    {
        $this->header = $header;
        $this->value = $value;
    }
    
    public function getHeader(): string
    {
        return $this->header;
    }
    
    public function getValue(): string
    {
        return $this->value;
    }
    
    #[\Override]
    public function match(ServerRequestInterface $request): bool
    {
        $values = $request->getHeader($this->header);
        return count($values) > 0 && in_array($this->value, $values);
    }
}

