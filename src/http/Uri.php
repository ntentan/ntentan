<?php

namespace ntentan\http;

use InvalidArgumentException;
use Psr\Http\Message\UriInterface;

/**
 * An object that represents a URI according to RFC 3986 and PSR-7.
 *
 * @see https://www.php-fig.org/psr/psr-7/#35-psrhttpmessageuriinterface
 * @author ekow
 */
class Uri implements UriInterface
{
    private const array STANDARD_PORTS = [
        'http' => 80,
        'https' => 443,
    ];

    private string $scheme = '';
    private string $userInfo = '';
    private string $host = '';
    private ?int $port = null;
    private string $path = '';
    private string $query = '';
    private string $fragment = '';
    private string $prefix = '';

    public function __construct(string $uri = '')
    {
        if ($uri !== '') {
            $parts = parse_url($uri);
            if ($parts === false) {
                throw new InvalidArgumentException(sprintf('Unable to parse URI: "%s"', $uri));
            }
            $this->applyParts($parts);
        }
    }

    private function applyParts(array $parts): void
    {
        $this->scheme = isset($parts['scheme']) ? strtolower($parts['scheme']) : '';
        $this->host = isset($parts['host']) ? strtolower($parts['host']) : '';
        $this->port = isset($parts['port']) ? (int)$parts['port'] : null;
        $this->path = $parts['path'] ?? '';
        $this->query = $parts['query'] ?? '';
        $this->fragment = $parts['fragment'] ?? '';

        if (isset($parts['user'])) {
            $this->userInfo = $parts['user'] . (isset($parts['pass']) ? ':' . $parts['pass'] : '');
        } else {
            $this->userInfo = '';
        }
    }

    #[\Override]
    public function getScheme(): string
    {
        return $this->scheme;
    }

    #[\Override]
    public function getAuthority(): string
    {
        if ($this->host === '') {
            return '';
        }

        $authority = $this->host;
        if ($this->userInfo !== '') {
            $authority = $this->userInfo . '@' . $authority;
        }

        $port = $this->getPort();
        if ($port !== null) {
            $authority .= ':' . $port;
        }

        return $authority;
    }

    #[\Override]
    public function getUserInfo(): string
    {
        return $this->userInfo;
    }

    #[\Override]
    public function getHost(): string
    {
        return $this->host;
    }

    #[\Override]
    public function getPort(): ?int
    {
        if ($this->port === null) {
            return null;
        }

        if ($this->scheme !== '' && isset(self::STANDARD_PORTS[$this->scheme]) && self::STANDARD_PORTS[$this->scheme] === $this->port) {
            return null;
        }

        return $this->port;
    }

    #[\Override]
    public function getPath(): string
    {
        return $this->path;
    }

    #[\Override]
    public function getQuery(): string
    {
        return $this->query;
    }

    #[\Override]
    public function getFragment(): string
    {
        return $this->fragment;
    }

    #[\Override]
    public function withScheme(string $scheme): UriInterface
    {
        $scheme = strtolower($scheme);
        if ($scheme !== '' && !preg_match('/^[a-z][a-z0-9+.-]*$/', $scheme)) {
            throw new InvalidArgumentException(sprintf('Invalid URI scheme: "%s"', $scheme));
        }

        if ($this->scheme === $scheme) {
            return $this;
        }

        $clone = clone $this;
        $clone->scheme = $scheme;
        return $clone;
    }

    #[\Override]
    public function withUserInfo(string $user, ?string $password = null): UriInterface
    {
        $info = $user;
        if ($password !== null && $password !== '') {
            $info .= ':' . $password;
        }

        if ($this->userInfo === $info) {
            return $this;
        }

        $clone = clone $this;
        $clone->userInfo = $info;
        return $clone;
    }

    #[\Override]
    public function withHost(string $host): UriInterface
    {
        $host = strtolower($host);

        if ($this->host === $host) {
            return $this;
        }

        $clone = clone $this;
        $clone->host = $host;
        return $clone;
    }

    #[\Override]
    public function withPort(?int $port): UriInterface
    {
        if ($port !== null && ($port < 1 || $port > 65535)) {
            throw new InvalidArgumentException(sprintf('Invalid URI port "%d", must be between 1 and 65535', $port));
        }

        if ($this->port === $port) {
            return $this;
        }

        $clone = clone $this;
        $clone->port = $port;
        return $clone;
    }

    #[\Override]
    public function withPath(string $path): UriInterface
    {
        if ($this->path === $path) {
            return $this;
        }

        $clone = clone $this;
        $clone->path = $path;
        return $clone;
    }

    #[\Override]
    public function withQuery(string $query): UriInterface
    {
        $query = ltrim($query, '?');

        if ($this->query === $query) {
            return $this;
        }

        $clone = clone $this;
        $clone->query = $query;
        return $clone;
    }

    #[\Override]
    public function withFragment(string $fragment): UriInterface
    {
        $fragment = ltrim($fragment, '#');

        if ($this->fragment === $fragment) {
            return $this;
        }

        $clone = clone $this;
        $clone->fragment = $fragment;
        return $clone;
    }

    #[\Override]
    public function __toString(): string
    {
        $uri = '';

        if ($this->scheme !== '') {
            $uri .= $this->scheme . ':';
        }

        $authority = $this->getAuthority();
        if ($authority !== '') {
            $uri .= '//' . $authority;
        }

        $path = $this->path;
        if ($authority !== '' && $path !== '' && !str_starts_with($path, '/')) {
            $path = '/' . $path;
        } elseif ($authority === '' && str_starts_with($path, '//')) {
            $path = '/' . ltrim($path, '/');
        }

        $uri .= $path;

        if ($this->query !== '') {
            $uri .= '?' . $this->query;
        }

        if ($this->fragment !== '') {
            $uri .= '#' . $this->fragment;
        }

        return $uri;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function withPrefix(string $prefix): self
    {
        $clone = clone $this;
        $clone->prefix = $prefix;
        return $clone;
    }
}
