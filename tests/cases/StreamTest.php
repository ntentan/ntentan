<?php

namespace ntentan\tests\cases;

use InvalidArgumentException;
use ntentan\http\Stream;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class StreamTest extends TestCase
{
    public function testStreamFromResource()
    {
        $res = fopen('php://memory', 'r+');
        fwrite($res, 'Hello World');
        $stream = new Stream($res);

        $this->assertSame('Hello World', (string)$stream);
        $this->assertSame(11, $stream->getSize());
        $this->assertTrue($stream->isReadable());
        $this->assertTrue($stream->isWritable());
        $this->assertTrue($stream->isSeekable());

        $stream->rewind();
        $this->assertSame(0, $stream->tell());
        $this->assertSame('Hello', $stream->read(5));
        $this->assertSame(' World', $stream->getContents());
        $this->assertTrue($stream->eof());

        $stream->seek(6);
        $this->assertSame(6, $stream->tell());
        $this->assertSame('World', $stream->getContents());

        $stream->write('!');
        $this->assertSame('Hello World!', (string)$stream);

        $meta = $stream->getMetadata();
        $this->assertIsArray($meta);
        $this->assertSame('php://memory', $stream->getMetadata('uri'));

        $detached = $stream->detach();
        $this->assertSame($res, $detached);
        $this->assertNull($stream->getSize());
        $this->assertFalse($stream->isReadable());
        $this->assertFalse($stream->isWritable());
        $this->assertFalse($stream->isSeekable());

        fclose($detached);
    }

    public function testStreamFromPath()
    {
        $stream = new Stream('php://temp', 'w+');
        $stream->write('testing');
        $this->assertSame('testing', (string)$stream);
        $stream->close();
    }

    public function testInvalidStreamArgument()
    {
        $this->expectException(InvalidArgumentException::class);
        new Stream(12345);
    }

    public function testNonSeekableOrDetachedExceptions()
    {
        $res = fopen('php://memory', 'r+');
        $stream = new Stream($res);
        $stream->close();

        $this->expectException(RuntimeException::class);
        $stream->read(5);
    }
}
