<?php

namespace ntentan\tests\lib;
class MockCallback
{
    public function __invoke()
    {
        return "Hello";
    }
}