<?php

namespace ntenta\http\filters;

use ntentan\http\filters\Route;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Put extends Route
{
    protected string $method = 'put';
}