<?php

namespace ntentan\http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\MessageInterface;

/**
 * Description of Request
 *
 * @author ekow
 */
class Request implements RequestInterface {

    private $headers = null;

    #[\Override]
    public function getBody(): StreamInterface {
        
    }

    #[\Override]
    public function getHeader(string $name): array {
        if ($this->headers === null) {
            
        }
    }

    #[\Override]
    public function getHeaderLine(string $name): string {
        
    }

    #[\Override]
    public function getHeaders(): array {
        
    }

    #[\Override]
    public function getMethod(): string {
        
    }

    #[\Override]
    public function getProtocolVersion(): string {
        
    }

    #[\Override]
    public function getRequestTarget(): string {
        
    }

    #[\Override]
    public function getUri(): UriInterface {
        
    }

    #[\Override]
    public function hasHeader(string $name): bool {
        
    }

    #[\Override]
    public function withAddedHeader(string $name, $value): MessageInterface {
        
    }

    #[\Override]
    public function withBody(\Psr\Http\Message\StreamInterface $body): MessageInterface {
        
    }

    #[\Override]
    public function withHeader(string $name, $value): MessageInterface {
        
    }

    #[\Override]
    public function withMethod(string $method): RequestInterface {
        
    }

    #[\Override]
    public function withProtocolVersion(string $version): MessageInterface {
        
    }

    #[\Override]
    public function withRequestTarget(string $requestTarget): RequestInterface {
        
    }

    #[\Override]
    public function withUri(\Psr\Http\Message\UriInterface $uri, bool $preserveHost = false): RequestInterface {
        
    }

    #[\Override]
    public function withoutHeader(string $name): MessageInterface {
        
    }
}
