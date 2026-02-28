<?php

namespace ntentan\middleware\filters;

interface ConfigurableFilter extends MiddlewareFilter
{
    public function configure(array $args): void;
}