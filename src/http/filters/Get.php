<?php
namespace ntentan\http\filters;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Get extends Route
{
    protected string $method = 'get';
}