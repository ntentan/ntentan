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
class Request extends Message implements ServerRequestInterface
{
    private UriInterface $uri;
    private array $uploadedFiles;

    public function __construct(UriInterface $uri, StreamInterface $bodyStream)
    {
        $this->uri = $uri;
        $this->withBody($bodyStream);
    }

    public function getProtocolVersion(): string
    {
        // TODO: Implement getProtocolVersion() method.
    }

    protected function initializeHeaders(): array
    {
        $headers = [];
        foreach (getallheaders() as $key => $value) {
            $headers[strtolower($key)] = explode(',', $value);
        }
        return $headers;
    }

    #[\Override]
    public function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }


    #[\Override]
    public function getRequestTarget(): string
    {

    }

    #[\Override]
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    #[\Override]
    public function withMethod(string $method): RequestInterface
    {

    }

    #[\Override]
    public function withProtocolVersion(string $version): MessageInterface
    {

    }

    #[\Override]
    public function withRequestTarget(string $requestTarget): RequestInterface
    {

    }

    #[\Override]
    public function withUri(UriInterface $uri, bool $preserveHost = false): RequestInterface
    {
        $this->uri = $uri;
        return $this;
    }

    #[\Override]
    public function getAttribute(string $name, $default = null): mixed
    {

    }

    #[\Override]
    public function getAttributes(): array
    {

    }

    #[\Override]
    public function getCookieParams(): array
    {

    }

    #[\Override]
    public function getParsedBody()
    {

    }

    #[\Override]
    public function getQueryParams(): array
    {

    }

    #[\Override]
    public function getServerParams(): array
    {

    }

    private function setUploadFields(string $field, mixed $value, array &$uploadedFiles)
    {
        if (is_array($value)) {
            if (!isset($uploadedFiles[$field])) {
                $uploadedFiles[$field] = [];
            }
            foreach ($value as $k => $v) {
                $this->setUploadFields($k, $v, $uploadedFiles[$field]);
            }
        } else {
            $uploadedFiles[$field] = $value;
            if (isset($uploadedFiles['name'])
                && isset($uploadedFiles['tmp_name'])
                && isset($uploadedFiles['size'])
                && isset($uploadedFiles['error'])
                && isset($uploadedFiles['type']))
            {
                $uploadedFiles = new UploadedFile($uploadedFiles);
            }
        }
    }

    #[\Override]
    public function getUploadedFiles(): array
    {
        if (!isset($this->uploadedFiles)) {
            $this->uploadedFiles = [];
            foreach ($_FILES as $field => $value) {
                $this->setUploadFields($field, $value, $this->uploadedFiles);
            }
        }
        return $this->uploadedFiles;
    }

    #[\Override]
    public function withAttribute(string $name, $value): ServerRequestInterface
    {

    }

    #[\Override]
    public function withCookieParams(array $cookies): ServerRequestInterface
    {

    }

    #[\Override]
    public function withParsedBody($data): ServerRequestInterface
    {

    }

    #[\Override]
    public function withQueryParams(array $query): ServerRequestInterface
    {

    }

    #[\Override]
    public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface
    {

    }

    #[\Override]
    public function withoutAttribute(string $name): ServerRequestInterface
    {

    }
}
