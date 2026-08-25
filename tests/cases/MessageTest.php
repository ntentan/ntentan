<?php

namespace ntentan\tests\cases;

use InvalidArgumentException;
use ntentan\http\Message;
use ntentan\http\StringStream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

class ConcreteMessage extends Message
{
    private array $initialHeaders;

    public function __construct(?StreamInterface $body = null, array $initialHeaders = [])
    {
        $this->initialHeaders = $initialHeaders;
        parent::__construct($body);
    }

    protected function initializeHeaders(): array
    {
        return $this->initialHeaders;
    }
}

class MessageTest extends TestCase
{
    public function testProtocolVersionDefault()
    {
        $message = new ConcreteMessage();
        $this->assertSame('1.1', $message->getProtocolVersion());
    }

    public function testWithProtocolVersion()
    {
        $message = new ConcreteMessage();
        $newMessage = $message->withProtocolVersion('2.0');

        $this->assertNotSame($message, $newMessage);
        $this->assertSame('1.1', $message->getProtocolVersion());
        $this->assertSame('2.0', $newMessage->getProtocolVersion());

        // Same version returns the same instance
        $sameMessage = $newMessage->withProtocolVersion('2.0');
        $this->assertSame($newMessage, $sameMessage);
    }

    public function testInitializeHeadersAndGetHeaders()
    {
        $message = new ConcreteMessage(null, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Accept' => ['application/json', 'text/plain'],
        ]);

        $this->assertTrue($message->hasHeader('content-type'));
        $this->assertTrue($message->hasHeader('Content-Type'));
        $this->assertTrue($message->hasHeader('ACCEPT'));
        $this->assertFalse($message->hasHeader('Authorization'));

        $this->assertSame(['text/html; charset=UTF-8'], $message->getHeader('Content-Type'));
        $this->assertSame(['text/html; charset=UTF-8'], $message->getHeader('content-type'));
        $this->assertSame('text/html; charset=UTF-8', $message->getHeaderLine('content-type'));

        $this->assertSame(['application/json', 'text/plain'], $message->getHeader('accept'));
        $this->assertSame('application/json, text/plain', $message->getHeaderLine('accept'));

        $headers = $message->getHeaders();
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertArrayHasKey('Accept', $headers);
        $this->assertSame(['text/html; charset=UTF-8'], $headers['Content-Type']);
        $this->assertSame(['application/json', 'text/plain'], $headers['Accept']);
    }

    public function testNonExistentHeader()
    {
        $message = new ConcreteMessage();
        $this->assertFalse($message->hasHeader('X-Non-Existent'));
        $this->assertSame([], $message->getHeader('X-Non-Existent'));
        $this->assertSame('', $message->getHeaderLine('X-Non-Existent'));
    }

    public function testWithHeader()
    {
        $message = new ConcreteMessage(null, ['Content-Type' => 'text/html']);
        $newMessage = $message->withHeader('content-type', 'application/json');

        $this->assertNotSame($message, $newMessage);
        $this->assertSame(['text/html'], $message->getHeader('Content-Type'));
        $this->assertSame(['application/json'], $newMessage->getHeader('Content-Type'));

        // Header name casing is updated to new casing in getHeaders()
        $headers = $newMessage->getHeaders();
        $this->assertArrayHasKey('content-type', $headers);
        $this->assertArrayNotHasKey('Content-Type', $headers);
    }

    public function testWithAddedHeader()
    {
        $message = new ConcreteMessage(null, ['Accept' => 'text/html']);
        $newMessage = $message->withAddedHeader('accept', 'application/json');

        $this->assertNotSame($message, $newMessage);
        $this->assertSame(['text/html'], $message->getHeader('Accept'));
        $this->assertSame(['text/html', 'application/json'], $newMessage->getHeader('Accept'));
        $this->assertSame('text/html, application/json', $newMessage->getHeaderLine('Accept'));

        // Original casing is preserved
        $headers = $newMessage->getHeaders();
        $this->assertArrayHasKey('Accept', $headers);

        // Adding to non-existent header
        $withNewHeader = $message->withAddedHeader('X-Custom-Header', 'custom-value');
        $this->assertTrue($withNewHeader->hasHeader('x-custom-header'));
        $this->assertSame(['custom-value'], $withNewHeader->getHeader('X-Custom-Header'));
        $this->assertArrayHasKey('X-Custom-Header', $withNewHeader->getHeaders());
    }

    public function testWithoutHeader()
    {
        $message = new ConcreteMessage(null, [
            'Content-Type' => 'text/html',
            'X-Header' => 'value'
        ]);

        $newMessage = $message->withoutHeader('content-type');
        $this->assertNotSame($message, $newMessage);
        $this->assertFalse($newMessage->hasHeader('Content-Type'));
        $this->assertTrue($newMessage->hasHeader('X-Header'));
        $this->assertTrue($message->hasHeader('Content-Type'));

        // Removing non-existent header returns same instance
        $sameMessage = $message->withoutHeader('Non-Existent');
        $this->assertSame($message, $sameMessage);
    }

    public function testInvalidHeaderNameThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $message = new ConcreteMessage();
        $message->withHeader('Invalid Header Name With Spaces', 'value');
    }

    public function testEmptyHeaderNameThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $message = new ConcreteMessage();
        $message->withHeader('', 'value');
    }

    public function testEmptyHeaderValueThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $message = new ConcreteMessage();
        $message->withHeader('Valid-Name', []);
    }

    public function testHeaderValueWithCRLFThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $message = new ConcreteMessage();
        $message->withHeader('Valid-Name', "invalid\r\nvalue");
    }

    public function testInvalidHeaderValueTypeThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $message = new ConcreteMessage();
        $message->withHeader('Valid-Name', false);
    }

    public function testGetBodyDefault()
    {
        $message = new ConcreteMessage();
        $body = $message->getBody();
        $this->assertInstanceOf(StreamInterface::class, $body);
        $this->assertSame('', (string)$body);
    }

    public function testWithBody()
    {
        $message = new ConcreteMessage();
        $body = new StringStream('Hello World');
        $newMessage = $message->withBody($body);

        $this->assertNotSame($message, $newMessage);
        $this->assertSame('Hello World', (string)$newMessage->getBody());
        $this->assertSame('', (string)$message->getBody());

        // Same stream returns same instance
        $sameMessage = $newMessage->withBody($body);
        $this->assertSame($newMessage, $sameMessage);
    }
}
