<?php
namespace ntentan\http;

use Psr\Http\Message\UriInterface;

/**
 * Description of Uri
 *
 * @author ekow
 */
class Uri implements UriInterface
{
    //put your code here
    #[\Override]
    public function __toString(): string {
        
    }

    #[\Override]
    public function getAuthority(): string {
        
    }

    #[\Override]
    public function getFragment(): string {
        
    }

    #[\Override]
    public function getHost(): string {
        
    }

    #[\Override]
    public function getPath(): string {
        
    }

    #[\Override]
    public function getPort(): ?int {
        
    }

    #[\Override]
    public function getQuery(): string {
        
    }

    #[\Override]
    public function getScheme(): string {
        
    }

    #[\Override]
    public function getUserInfo(): string {
        
    }

    #[\Override]
    public function withFragment(string $fragment): UriInterface {
        
    }

    #[\Override]
    public function withHost(string $host): UriInterface {
        
    }

    #[\Override]
    public function withPath(string $path): UriInterface {
        
    }

    #[\Override]
    public function withPort(?int $port): UriInterface {
        
    }

    #[\Override]
    public function withQuery(string $query): UriInterface {
        
    }

    #[\Override]
    public function withScheme(string $scheme): UriInterface {
        
    }

    #[\Override]
    public function withUserInfo(string $user, ?string $password = null): UriInterface {
        
    }
}
