<?php

namespace ntentan\http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Description of Response
 *
 * @author ekow
 */
class Response implements ResponseInterface 
{
    private int $status = 200;
    private StreamInterface $body;
    private array $headers = [];
   
    #[\Override]
    public function getBody(): StreamInterface {
        return $this->body;
    }

    #[\Override]
    public function getHeader(string $name): array {
        
    }

    #[\Override]
    public function getHeaderLine(string $name): string {
        
    }

    #[\Override]
    public function getHeaders(): array {
        return $this->headers;
    }

    #[\Override]
    public function getProtocolVersion(): string {
        
    }

    #[\Override]
    public function getReasonPhrase(): string {
        
    }

    #[\Override]
    public function getStatusCode(): int {
        return $this->status;
    }

    #[\Override]
    public function hasHeader(string $name): bool {
        
    }

    #[\Override]
    public function withAddedHeader(string $name, $value): \Psr\Http\Message\MessageInterface {
        $name = strtolower($name);
        if (!isset($this->headers[$name])) {
            $this->headers[$name] = [];
        }
        $this->headers[$name][] = $value;
        return $this;
    }

    #[\Override]
    public function withBody(\Psr\Http\Message\StreamInterface $body): \Psr\Http\Message\MessageInterface {
        $this->body = $body;
        return $this;
    }

    #[\Override]
    public function withHeader(string $name, $value): \Psr\Http\Message\MessageInterface {
        $this->headers[$name] = [$value];
        return $this;
    }

    #[\Override]
    public function withProtocolVersion(string $version): \Psr\Http\Message\MessageInterface {
        
    }

    #[\Override]
    public function withStatus(int $code, string $reasonPhrase = ''): ResponseInterface {
        $this->status = $code;
        return $this;
    }

    #[\Override]
    public function withoutHeader(string $name): \Psr\Http\Message\MessageInterface {
        
    }
}
