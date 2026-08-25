<?php

namespace ntentan\tests\cases;

use InvalidArgumentException;
use ntentan\http\StringStream;
use ntentan\http\UploadedFile;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class UploadedFileTest extends TestCase
{
    public function testUploadedFileFromArray()
    {
        $file = new UploadedFile([
            'tmp_name' => sys_get_temp_dir() . '/test_upload.tmp',
            'size' => 1234,
            'error' => UPLOAD_ERR_OK,
            'name' => 'document.pdf',
            'type' => 'application/pdf',
        ]);

        $this->assertSame(1234, $file->getSize());
        $this->assertSame(UPLOAD_ERR_OK, $file->getError());
        $this->assertSame('document.pdf', $file->getClientFilename());
        $this->assertSame('application/pdf', $file->getClientMediaType());
    }

    public function testUploadedFileFromStreamAndMove()
    {
        $stream = new StringStream('sample file data');
        $file = new UploadedFile($stream, 16, UPLOAD_ERR_OK, 'sample.txt', 'text/plain');

        $this->assertSame(16, $file->getSize());
        $this->assertSame('sample.txt', $file->getClientFilename());
        $this->assertSame('text/plain', $file->getClientMediaType());
        $this->assertSame('sample file data', (string)$file->getStream());

        $target = sys_get_temp_dir() . '/moved_sample_' . uniqid() . '.txt';
        $file->moveTo($target);
        $this->assertFileExists($target);
        $this->assertSame('sample file data', file_get_contents($target));

        // After move, getStream and second moveTo must throw RuntimeException
        try {
            $file->getStream();
            $this->fail('Expected RuntimeException on getStream after moveTo');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('moved', $e->getMessage());
        }

        try {
            $file->moveTo($target);
            $this->fail('Expected RuntimeException on second moveTo');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('moved', $e->getMessage());
        }

        @unlink($target);
    }

    public function testUploadErrorPreventsStreamAndMove()
    {
        $file = new UploadedFile([
            'tmp_name' => '',
            'size' => 0,
            'error' => UPLOAD_ERR_NO_FILE,
            'name' => '',
            'type' => '',
        ]);

        $this->assertSame(UPLOAD_ERR_NO_FILE, $file->getError());

        $this->expectException(RuntimeException::class);
        $file->getStream();
    }
}
