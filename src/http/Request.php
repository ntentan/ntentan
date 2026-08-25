<?php

namespace ntentan\http;

use InvalidArgumentException;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;

/**
 * Represents an HTTP request.
 *
 * @author ekow
 */
class Request extends Message implements ServerRequestInterface
{
    private UriInterface $uri;
    private string $method;
    private ?string $requestTarget = null;
    private array $serverParams;
    private array $cookieParams;
    private ?array $queryParams = null;
    private array $attributes;
    private ?array $uploadedFiles = null;
    private mixed $parsedBody = null;
    private bool $parsedBodySet = false;

    public function __construct(
        UriInterface $uri, ?StreamInterface $bodyStream = null, ?string $method = null, array $serverParams = [], 
        array $cookieParams = [], ?array $queryParams = null, array $attributes = [], ?array $uploadedFiles = null, mixed $parsedBody = null
    ) {
        parent::__construct($bodyStream);
        $this->uri = $uri;
        $this->serverParams = !empty($serverParams) ? $serverParams : $_SERVER;
        $this->cookieParams = !empty($cookieParams) ? $cookieParams : $_COOKIE;
        $this->queryParams = $queryParams;
        $this->attributes = $attributes;
        $this->method = $method ?? ($this->serverParams['REQUEST_METHOD'] ?? 'GET');

        if ($uploadedFiles !== null) {
            $this->validateUploadedFiles($uploadedFiles);
            $this->uploadedFiles = $uploadedFiles;
        }

        if ($parsedBody !== null) {
            if (!is_array($parsedBody) && !is_object($parsedBody)) {
                throw new InvalidArgumentException('Parsed body must be an array, object, or null');
            }
            $this->parsedBody = $parsedBody;
            $this->parsedBodySet = true;
        }
    }

    protected function initializeHeaders(): array
    {
        $headers = [];
        if (function_exists('getallheaders')) {
            $allHeaders = getallheaders();
            if (is_array($allHeaders)) {
                foreach ($allHeaders as $key => $value) {
                    $headers[$key] = array_map('trim', explode(',', $value));
                }
            }
        } else {
            foreach ($this->serverParams as $key => $value) {
                if (str_starts_with($key, 'HTTP_')) {
                    $headerName = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                    $headers[$headerName] = array_map('trim', explode(',', $value));
                } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH'], true)) {
                    $headerName = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $key))));
                    $headers[$headerName] = array_map('trim', explode(',', $value));
                }
            }
        }

        // Host header initialization from URI if missing
        if (!isset($headers['Host']) && !isset($headers['host'])) {
            $host = $this->uri->getHost();
            if ($host !== '') {
                $port = $this->uri->getPort();
                if ($port !== null) {
                    $host .= ':' . $port;
                }
                $headers['Host'] = [$host];
            }
        }

        return $headers;
    }

    #[\Override]
    public function getMethod(): string
    {
        return $this->method;
    }

    #[\Override]
    public function withMethod(string $method): RequestInterface
    {
        if (!is_string($method) || $method === '' || !preg_match('/^[a-zA-Z0-9\'!#$%&*+\-.^_`|~]+$/', $method)) {
            throw new InvalidArgumentException(sprintf('"%s" is not a valid HTTP method', $method));
        }

        $clone = clone $this;
        $clone->method = $method;
        return $clone;
    }

    #[\Override]
    public function getRequestTarget(): string
    {
        if ($this->requestTarget !== null) {
            return $this->requestTarget;
        }

        $target = $this->uri->getPath();
        if ($target === '') {
            $target = '/';
        }
        if (!str_starts_with($target, '/')) {
            $target = '/' . $target;
        }
        $query = $this->uri->getQuery();
        if ($query !== '') {
            $target .= '?' . $query;
        }

        return $target;
    }

    #[\Override]
    public function withRequestTarget(string $requestTarget): RequestInterface
    {
        if (preg_match('/\s/', $requestTarget)) {
            throw new InvalidArgumentException('Invalid request target; cannot contain whitespace');
        }

        $clone = clone $this;
        $clone->requestTarget = $requestTarget;
        return $clone;
    }

    #[\Override]
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    #[\Override]
    public function withUri(UriInterface $uri, bool $preserveHost = false): RequestInterface
    {
        if ($uri === $this->uri) {
            return $this;
        }

        $clone = clone $this;
        $clone->uri = $uri;

        if (!$preserveHost) {
            if ($uri->getHost() !== '') {
                $host = $uri->getHost();
                if ($uri->getPort() !== null) {
                    $host .= ':' . $uri->getPort();
                }
                return $clone->withHeader('Host', $host);
            }
        } else {
            if ((!$this->hasHeader('Host') || $this->getHeaderLine('Host') === '') && $uri->getHost() !== '') {
                $host = $uri->getHost();
                if ($uri->getPort() !== null) {
                    $host .= ':' . $uri->getPort();
                }
                return $clone->withHeader('Host', $host);
            }
        }

        return $clone;
    }

    #[\Override]
    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    #[\Override]
    public function getCookieParams(): array
    {
        return $this->cookieParams;
    }

    #[\Override]
    public function withCookieParams(array $cookies): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->cookieParams = $cookies;
        return $clone;
    }

    #[\Override]
    public function getQueryParams(): array
    {
        if ($this->queryParams !== null) {
            return $this->queryParams;
        }

        $query = $this->uri->getQuery();
        if ($query !== '') {
            parse_str($query, $params);
            return $params;
        }

        return $_GET;
    }

    #[\Override]
    public function withQueryParams(array $query): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->queryParams = $query;
        return $clone;
    }

    #[\Override]
    public function getUploadedFiles(): array
    {
        if ($this->uploadedFiles === null) {
            $this->uploadedFiles = self::normalizeFiles($_FILES);
        }
        return $this->uploadedFiles;
    }

    #[\Override]
    public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface
    {
        $this->validateUploadedFiles($uploadedFiles);
        $clone = clone $this;
        $clone->uploadedFiles = $uploadedFiles;
        return $clone;
    }

    #[\Override]
    public function getParsedBody()
    {
        if ($this->parsedBodySet) {
            return $this->parsedBody;
        }

        $contentType = $this->getHeaderLine('Content-Type');
        $method = strtoupper($this->getMethod());

        if ($method === 'POST') {
            if (preg_match('/^application\/x-www-form-urlencoded/i', $contentType) ||
                preg_match('/^multipart\/form-data/i', $contentType)) {
                return $_POST;
            }
        }

        if (preg_match('/^application\/json/i', $contentType)) {
            $body = (string)$this->getBody();
            if ($body !== '') {
                $parsed = json_decode($body, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $parsed;
                }
            }
        }

        return null;
    }

    #[\Override]
    public function withParsedBody($data): ServerRequestInterface
    {
        if (!is_array($data) && !is_object($data) && $data !== null) {
            throw new InvalidArgumentException('Parsed body must be an array, object, or null');
        }

        $clone = clone $this;
        $clone->parsedBody = $data;
        $clone->parsedBodySet = true;
        return $clone;
    }

    #[\Override]
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    #[\Override]
    public function getAttribute(string $name, $default = null): mixed
    {
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }
        return $default;
    }

    #[\Override]
    public function withAttribute(string $name, $value): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->attributes[$name] = $value;
        return $clone;
    }

    #[\Override]
    public function withoutAttribute(string $name): ServerRequestInterface
    {
        if (!array_key_exists($name, $this->attributes)) {
            return $this;
        }

        $clone = clone $this;
        unset($clone->attributes[$name]);
        return $clone;
    }

    private function validateUploadedFiles(array $uploadedFiles): void
    {
        foreach ($uploadedFiles as $file) {
            if (is_array($file)) {
                $this->validateUploadedFiles($file);
                continue;
            }
            if (!$file instanceof UploadedFileInterface) {
                throw new InvalidArgumentException('Invalid file uploaded; must be an UploadedFileInterface instance');
            }
        }
    }

    private static function normalizeFiles(array $files): array
    {
        $normalized = [];
        foreach ($files as $key => $value) {
            if ($value instanceof UploadedFileInterface) {
                $normalized[$key] = $value;
            } elseif (is_array($value) && isset($value['tmp_name'])) {
                $normalized[$key] = self::createUploadedFileFromSpec($value);
            } elseif (is_array($value)) {
                $normalized[$key] = self::normalizeFiles($value);
            } else {
                throw new InvalidArgumentException('Invalid value in files specification');
            }
        }
        return $normalized;
    }

    private static function createUploadedFileFromSpec(array $value): array|UploadedFileInterface
    {
        if (is_array($value['tmp_name'])) {
            return self::normalizeNestedFileSpec($value);
        }
        return new UploadedFile($value);
    }

    private static function normalizeNestedFileSpec(array $files): array
    {
        $normalized = [];
        foreach (array_keys($files['tmp_name']) as $key) {
            $spec = [
                'tmp_name' => $files['tmp_name'][$key],
                'size'     => $files['size'][$key] ?? null,
                'error'    => $files['error'][$key] ?? null,
                'name'     => $files['name'][$key] ?? null,
                'type'     => $files['type'][$key] ?? null,
            ];
            $normalized[$key] = self::createUploadedFileFromSpec($spec);
        }
        return $normalized;
    }
}
