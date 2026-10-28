<?php

namespace ntentan\tests\cases;

use ntentan\exceptions\NtentanException;
use ntentan\sessions\php\FileSessionHandler;
use PHPUnit\Framework\TestCase;

class FileSessionHandlerTest extends TestCase
{
    private string $sessionDir;

    protected function setUp(): void
    {
        $this->sessionDir = sys_get_temp_dir() . '/nt_sess_test_' . uniqid();
    }

    protected function tearDown(): void
    {
        if (is_dir($this->sessionDir)) {
            $files = glob("{$this->sessionDir}/*");
            if ($files !== false) {
                foreach ($files as $file) {
                    unlink($file);
                }
            }
            rmdir($this->sessionDir);
        }
    }

    public function testOpenWithCreatePath()
    {
        $handler = new FileSessionHandler([
            'path' => $this->sessionDir,
            'createPath' => true,
        ]);
        $this->assertTrue($handler->open($this->sessionDir, 'test_sess'));
        $this->assertDirectoryExists($this->sessionDir);
        $this->assertTrue($handler->close());
    }

    public function testOpenNonExistentDirectoryThrowsException()
    {
        $handler = new FileSessionHandler([
            'path' => $this->sessionDir . '/non_existent',
            'createPath' => false,
        ]);
        $this->expectException(NtentanException::class);
        $handler->open($this->sessionDir . '/non_existent', 'test_sess');
    }

    public function testReadWriteDestroy()
    {
        $handler = new FileSessionHandler([
            'path' => $this->sessionDir,
            'createPath' => true,
        ]);
        $handler->open($this->sessionDir, 'test_sess');

        $this->assertSame('', $handler->read('sess_123'));
        $this->assertTrue($handler->write('sess_123', 'session_data_sample'));
        $this->assertSame('session_data_sample', $handler->read('sess_123'));
        $this->assertTrue($handler->destroy('sess_123'));
        $this->assertSame('', $handler->read('sess_123'));
    }

    public function testGc()
    {
        $handler = new FileSessionHandler([
            'path' => $this->sessionDir,
            'createPath' => true,
            'lifespan' => -1,
        ]);
        $handler->open($this->sessionDir, 'test_sess');
        $handler->write('sess_expired', 'expired_data');

        $cleaned = $handler->gc(0);
        $this->assertSame(1, $cleaned);
    }
}

