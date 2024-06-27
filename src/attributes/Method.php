<?php
namespace ntentan\attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Method 
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
}
