<?php
namespace ntentan\http\filters;

use Psr\Http\Message\ServerRequestInterface;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Post extends Route
{
    protected string $method = 'post';
}
