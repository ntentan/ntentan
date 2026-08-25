<?php

namespace ntentan\tests\cases;

use InvalidArgumentException;
use ntentan\http\Response;
use ntentan\http\StringStream;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    public function testDefaultValues()
    {
        $response = new Response();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('OK', $response->getReasonPhrase());
        $this->assertSame('1.1', $response->getProtocolVersion());
        $this->assertSame([], $response->getHeaders());
        $this->assertSame('', (string)$response->getBody());
    }

    public function testCustomStatusAndReasonPhrase()
    {
        $response = new Response(404, ['Content-Type' => 'text/plain'], null, '1.1', 'Not Found Here');
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('Not Found Here', $response->getReasonPhrase());
        $this->assertSame(['text/plain'], $response->getHeader('Content-Type'));
    }

    public function testWithStatus()
    {
        $response = new Response();
        $newResponse = $response->withStatus(500);

        $this->assertNotSame($response, $newResponse);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(500, $newResponse->getStatusCode());
        $this->assertSame('Internal Server Error', $newResponse->getReasonPhrase());

        $customPhraseResponse = $response->withStatus(418, "I'm custom teapot");
        $this->assertSame(418, $customPhraseResponse->getStatusCode());
        $this->assertSame("I'm custom teapot", $customPhraseResponse->getReasonPhrase());
    }

    public function testInvalidStatusCodeThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $response = new Response();
        $response->withStatus(99);
    }

    public function testWithJsonBody()
    {
        $response = new Response();
        $data = ['message' => 'hello', 'count' => 5];
        $jsonResponse = $response->withJsonBody($data);

        $this->assertNotSame($response, $jsonResponse);
        $this->assertSame('application/json', $jsonResponse->getHeaderLine('Content-Type'));
        $this->assertSame(json_encode($data), (string)$jsonResponse->getBody());
    }
}
