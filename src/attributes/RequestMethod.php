<?php
namespace ntentan\attributes;

use Attribute;

enum RequestMethodType: string 
{
    case POST = "POST";
    case GET = "GET";
};

#[Attribute(Attribute::TARGET_METHOD)]
class RequestMethod 
{
    private $type;

    public function __construct(RequestMethodType $type)
    {
        $this->type = $type;
    }    

    public function getType(): string
    {
        return $this->type->value;
    }
}
