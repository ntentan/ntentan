<?php

namespace ntentan\http;

use ntentan\wyf\forms\f;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

abstract class Message implements MessageInterface
{
    private StreamInterface $stream;
    private array $headers;


    /**
     * @inheritDoc
     */
    public function getHeaders(): array
    {
        $this->prepareHeaders();
        return $this->headers;
    }

    /**
     * @inheritDoc
     */
    public function hasHeader(string $name): bool
    {
        return isset($this->headers[$name]);
    }

    /**
     * @inheritDoc
     */
    public function getHeader(string $name): array
    {
        $this->prepareHeaders();
        return $this->headers[$name];
    }

    /**
     * @inheritDoc
     */
    public function getHeaderLine(string $name): string
    {
        // TODO: Implement getHeaderLine() method.
    }

    /**
     * @inheritDoc
     */
    public function withHeader(string $name, $value): MessageInterface
    {
        $this->prepareHeaders();
        $this->headers[strtolower($name)] = [$value];
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withAddedHeader(string $name, $value): MessageInterface
    {
        $this->prepareHeaders();
        $name = strtolower($name);
        if (!isset($this->headers[$name])) {
            $this->headers[$name] = [];
        }
        $this->headers[$name][] = $value;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withoutHeader(string $name): MessageInterface
    {
        $this->prepareHeaders();
        unset($this->headers[$name]);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getBody(): StreamInterface
    {
        return isset($this->stream) ? $this->stream : new StringStream("");
    }

    /**
     * @inheritDoc
     */
    public function withBody(StreamInterface $body): MessageInterface
    {
        $this->stream = $body;
        return $this;
    }

    private function prepareHeaders(): void
    {
        if (!isset($this->headers)) {
            $this->headers = $this->initializeHeaders();
        }
    }

    protected abstract function initializeHeaders(): array;
}