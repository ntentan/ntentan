<?php

namespace ntentan\http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\MessageInterface;

/**
 * Description of Response
 *
 * @author ekow
 */
class Response extends Message implements ResponseInterface
{
    private int $status = 200;

    #[\Override]
    public function getHeader(string $name): array
    {

    }

    #[\Override]
    public function getHeaderLine(string $name): string
    {

    }

    #[\Override]
    public function getProtocolVersion(): string
    {

    }

    #[\Override]
    public function getReasonPhrase(): string
    {

    }

    #[\Override]
    public function getStatusCode(): int
    {
        return $this->status;
    }


    #[\Override]
    public function withProtocolVersion(string $version): MessageInterface
    {

    }

    #[\Override]
    public function withStatus(int $code, string $reasonPhrase = ''): ResponseInterface
    {
        $this->status = $code;
        return $this;
    }

    public function withJsonBody(array $body): ResponseInterface
    {
        return $this->withBody(new StringStream(json_encode($body, JSON_THROW_ON_ERROR)))
            ->withHeader('Content-Type', 'application/json');
    }

    protected function initializeHeaders(): array
    {
        return [];
    }
}
