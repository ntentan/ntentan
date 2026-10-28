<?php

namespace ntentan\tests\cases;

use ntentan\sessions\php\DefaultSessionContainer;
use PHPUnit\Framework\TestCase;

class DefaultSessionContainerTest extends TestCase
{
    public function testSessionLifecycle()
    {
        $container = new DefaultSessionContainer();
        $this->assertTrue($container->open('', 'test'));
        $this->assertSame('', $container->read('id_1'));
        $this->assertTrue($container->write('id_1', 'stored_value'));
        $this->assertSame('stored_value', $container->read('id_1'));
        $this->assertTrue($container->destroy('id_1'));
        $this->assertSame('', $container->read('id_1'));
        $this->assertSame(0, $container->gc(100));
        $this->assertTrue($container->close());
    }
}

