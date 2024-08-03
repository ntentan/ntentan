<?php

namespace ntentan\http;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Represents an HTTP request.
 *
 * @author ekow
 */
class Request implements ServerRequestInterface {

    private array $headers;
    private UriInterface $uri;
    private StreamInterface $bodyStream;
    
    public function __construct(UriInterface $uri, StreamInterface $bodyStream) {
        $this->uri = $uri;
        $this->bodyStream = $bodyStream;
    }
    
    private function initializeHeaders(): void {
        if (empty($this->headers)) {
            
        }
    }

    #[\Override]
    public function getBody(): StreamInterface {
        return $this->bodyStream;
    }

    #[\Override]
    public function getHeader(string $name): array {

    }

    #[\Override]
    public function getHeaderLine(string $name): string {
        
    }

    #[\Override]
    public function getHeaders(): array {
        
    }

    #[\Override]
    public function getMethod(): string {
        return $_SERVER['REQUEST_METHOD'];
    }

    #[\Override]
    public function getProtocolVersion(): string {
        
    }

    #[\Override]
    public function getRequestTarget(): string {
        
    }

    #[\Override]
    public function getUri(): UriInterface {
        return $this->uri;
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
    public function withUri(UriInterface $uri, bool $preserveHost = false): RequestInterface {
        $this->uri = $uri;
        return $this;
    }

    #[\Override]
    public function withoutHeader(string $name): MessageInterface {
        
    }

    #[\Override]
    public function getAttribute(string $name, $default = null): mixed {
        
    }

    #[\Override]
    public function getAttributes(): array {
        
    }

    #[\Override]
    public function getCookieParams(): array {
        
    }

    #[\Override]
    public function getParsedBody() {
        
    }

    #[\Override]
    public function getQueryParams(): array {
        
    }

    #[\Override]
    public function getServerParams(): array {
        
    }

    #[\Override]
    public function getUploadedFiles(): array {
        
    }

    #[\Override]
    public function withAttribute(string $name, $value): ServerRequestInterface {
        
    }

    #[\Override]
    public function withCookieParams(array $cookies): ServerRequestInterface {
        
    }

    #[\Override]
    public function withParsedBody($data): ServerRequestInterface {
        
    }

    #[\Override]
    public function withQueryParams(array $query): ServerRequestInterface {
        
    }

    #[\Override]
    public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface {
        
    }

    #[\Override]
    public function withoutAttribute(string $name): ServerRequestInterface {
        
    }
}
