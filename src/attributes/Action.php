<?php
namespace ntentan\attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Action
{
    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function getPath(): string
    {
        return $this->path;
    }
}