<?php

namespace ntentan\middleware\filters;

interface MiddlewareFilter
{
    public function filter(array $params): bool;
}