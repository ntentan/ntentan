<?php
namespace ntentan\http;

use Psr\Http\Message\UriInterface;

/**
 * An object that represents a URI.
 * 
 * @see https://www.php-fig.org/psr/psr-7/#35-psrhttpmessageuriinterface
 */
class Uri implements UriInterface
{
    private array $parts;
    
    public function __construct(string $uri) {
        $this->parts = parse_url($uri);
    }

    #[\Override]
    public function __toString(): string {
        $scheme = $this->getScheme();
        $query = $this->getQuery();
        $fragment = $this->getFragment();
        
        return ($scheme == '' ? '' : "$scheme:") . "//{$this->getAuthority()}/{$this->getPath()}" .
                ($query == '' ? '' : "?$query") . ($fragment == '' ? '' : "#{$fragment}");
    }

    #[\Override]
    public function getAuthority(): string {
        $userInfo = $this->getUserInfo();
        $port = $this->getPort();
        return $userInfo . ($userInfo !== '' ? '@' : '') . $this->getHost() . ($port != '' ? ':' : '') . $port;
    }

    #[\Override]
    public function getFragment(): string {
        return $this->parts['fragment'] ?? '';
    }

    #[\Override]
    public function getHost(): string {
        return $this->parts['host'] ?? '';
    }

    #[\Override]
    public function getPath(): string {
        return $this->parts['path'] ?? '';
    }

    #[\Override]
    public function getPort(): ?int {
        return $this->parts['port'] ?? '';
    }

    #[\Override]
    public function getQuery(): string {
        return $this->parts['query'] ?? '';
    }

    #[\Override]
    public function getScheme(): string {
        return $this->parts['scheme'];
    }

    #[\Override]
    public function getUserInfo(): string {
        return $this->parts['user'] . (isset($this->parts['pass']) ? "." . $this->parts['pass'] : "");
    }

    #[\Override]
    public function withFragment(string $fragment): UriInterface {
        $this->parts['fragment'] = $fragment;
        return $this;
    }

    #[\Override]
    public function withHost(string $host): UriInterface {
        $this->parts['host'] = $host;
        return $this;
    }

    #[\Override]
    public function withPath(string $path): UriInterface {
        $this->parts['path'] = $path;
        return $this;
    }

    #[\Override]
    public function withPort(?int $port): UriInterface {
        $this->parts['port'] = $port;
        return $this;
    }

    #[\Override]
    public function withQuery(string $query): UriInterface {
        $this->parts['query'] = $query;
        return $this;
    }

    #[\Override]
    public function withScheme(string $scheme): UriInterface {
        $this->parts['scheme'] = $scheme;
        return $this;
    }

    #[\Override]
    public function withUserInfo(string $user, ?string $password = null): UriInterface {
        $this->parts['user'] = $user;
        if ($password != null) {
            $this->parts['pass'] = $password;
        }
        return $this;
    }
}
