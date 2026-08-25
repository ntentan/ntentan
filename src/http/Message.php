<?php

namespace ntentan\http;

use InvalidArgumentException;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

abstract class Message implements MessageInterface
{
    private StreamInterface $stream;
    private array $headers;
    private array $headerNames;
    private string $protocolVersion = '1.1';

    public function __construct(?StreamInterface $body = null)
    {
        $this->stream = $body ?? new StringStream("");
    }

    /**
     * @inheritDoc
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * @inheritDoc
     */
    public function withProtocolVersion(string $version): MessageInterface
    {
        if ($this->protocolVersion === $version) {
            return $this;
        }

        $clone = clone $this;
        $clone->protocolVersion = $version;
        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getHeaders(): array
    {
        $this->prepareHeaders();
        $headers = [];
        foreach ($this->headerNames as $lower => $name) {
            $headers[$name] = $this->headers[$lower];
        }
        return $headers;
    }

    /**
     * @inheritDoc
     */
    public function hasHeader(string $name): bool
    {
        $this->prepareHeaders();
        return isset($this->headerNames[strtolower($name)]);
    }

    /**
     * @inheritDoc
     */
    public function getHeader(string $name): array
    {
        $this->prepareHeaders();
        $lower = strtolower($name);
        return $this->headers[$lower] ?? [];
    }

    /**
     * @inheritDoc
     */
    public function getHeaderLine(string $name): string
    {
        $values = $this->getHeader($name);
        return implode(', ', $values);
    }

    /**
     * @inheritDoc
     */
    public function withHeader(string $name, $value): MessageInterface
    {
        $this->filterHeaderName($name);
        $normalized = $this->filterHeaderValue($value);

        $clone = clone $this;
        $clone->prepareHeaders();
        $lower = strtolower($name);
        $clone->headerNames[$lower] = $name;
        $clone->headers[$lower] = $normalized;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withAddedHeader(string $name, $value): MessageInterface
    {
        $this->filterHeaderName($name);
        $normalized = $this->filterHeaderValue($value);

        $clone = clone $this;
        $clone->prepareHeaders();
        $lower = strtolower($name);

        if (isset($clone->headerNames[$lower])) {
            $clone->headers[$lower] = array_merge($clone->headers[$lower], $normalized);
        } else {
            $clone->headerNames[$lower] = $name;
            $clone->headers[$lower] = $normalized;
        }

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withoutHeader(string $name): MessageInterface
    {
        $this->prepareHeaders();
        $lower = strtolower($name);
        if (!isset($this->headerNames[$lower])) {
            return $this;
        }

        $clone = clone $this;
        unset($clone->headers[$lower], $clone->headerNames[$lower]);

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getBody(): StreamInterface
    {
        return $this->stream ??= new StringStream("");
    }

    /**
     * @inheritDoc
     */
    public function withBody(StreamInterface $body): MessageInterface
    {
        if (isset($this->stream) && $this->stream === $body) {
            return $this;
        }

        $clone = clone $this;
        $clone->stream = $body;
        return $clone;
    }

    private function prepareHeaders(): void
    {
        if (!isset($this->headers)) {
            $this->headers = [];
            $this->headerNames = [];
            $headers = $this->initializeHeaders();
            foreach ($headers as $name => $value) {
                $name = (string) $name;
                $this->filterHeaderName($name);
                $normalized = $this->filterHeaderValue($value);
                $lower = strtolower($name);
                $this->headerNames[$lower] = $name;
                $this->headers[$lower] = $normalized;
            }
        }
    }

    private function filterHeaderName(mixed $name): void
    {
        if (!is_string($name) || $name === '' || !preg_match('/^[a-zA-Z0-9\'!#$%&*+\-.^_`|~]+$/', $name)) {
            throw new InvalidArgumentException(
                sprintf('"%s" is not a valid HTTP header name', is_scalar($name) ? (string)$name : gettype($name))
            );
        }
    }

    private function filterHeaderValue(mixed $value): array
    {
        if (!is_array($value)) {
            $value = [$value];
        }
        if (empty($value)) {
            throw new InvalidArgumentException('Header value cannot be empty');
        }
        $normalized = [];
        foreach ($value as $v) {
            if ((!is_string($v) && !is_numeric($v) && !(is_object($v) && method_exists($v, '__toString'))) || is_bool($v)) {
                throw new InvalidArgumentException(
                    sprintf('Header value must be a string or array of strings, %s given', gettype($v))
                );
            }
            $str = (string)$v;
            if (preg_match("/[\r\n]/", $str)) {
                throw new InvalidArgumentException('Header value cannot contain CR or LF characters');
            }
            $normalized[] = $str;
        }
        return $normalized;
    }

    protected abstract function initializeHeaders(): array;
}