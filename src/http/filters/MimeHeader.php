<?php
namespace ntentan\http\filters;

use Attribute;
use Psr\Http\Message\ServerRequestInterface;

#[Attribute(Attribute::TARGET_METHOD)]
class MimeHeader extends Header
{
    #[\Override]
    public function match(ServerRequestInterface $request): bool
    {
        $headerLine = $request->getHeaderLine($this->header);
        if ($headerLine === '') {
            return false;
        }
        $parts = array_map('trim', explode(';', $headerLine));
        return in_array($this->value, $parts, true) || in_array($this->value, $request->getHeader($this->header), true);
    }
}
