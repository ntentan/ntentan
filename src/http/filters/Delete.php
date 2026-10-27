<?php
namespace ntentan\http\filters;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Delete extends Route
{
    protected string $method = 'delete';
}