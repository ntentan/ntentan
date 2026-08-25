<?php

namespace ntentan\tests\cases;

use ntentan\http\StringStream;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class StringStreamTest extends TestCase
{
    public function testReadWriteSeek()
    {
        $stream = new StringStream('Hello World');
        $this->assertSame(11, $stream->getSize());
        $this->assertSame('Hello World', (string)$stream);
        $this->assertTrue($stream->isReadable());
        $this->assertTrue($stream->isWritable());
        $this->assertTrue($stream->isSeekable());

        $this->assertSame(0, $stream->tell());
        $this->assertSame('Hello', $stream->read(5));
        $this->assertSame(5, $stream->tell());
        $this->assertSame(' World', $stream->getContents());
        $this->assertTrue($stream->eof());

        $stream->seek(0);
        $this->assertSame(0, $stream->tell());
        $stream->write('Jello');
        $this->assertSame('Jello World', (string)$stream);

        $stream->setContent('New Content');
        $this->assertSame('New Content', (string)$stream);
        $this->assertSame(11, $stream->getSize());

        $stream->close();
        $this->assertFalse($stream->isReadable());
        $this->assertFalse($stream->isWritable());
    }

    public function testClosedStreamThrowsException()
    {
        $stream = new StringStream('Hello');
        $stream->close();

        $this->expectException(RuntimeException::class);
        $stream->read(2);
    }
}
