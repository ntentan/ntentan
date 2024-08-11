<?php
namespace ntentan\http\filters;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class MimeHeader extends Header
{
    #[\Override]
    public function match(string $value): bool
    {
        return false;
    }
}

