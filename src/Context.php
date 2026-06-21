<?php

namespace ntentan;

use ntentan\sessions\SessionStore;

class Context
{
    private string $prefix;
    private SessionStore $session;

    public function __construct(SessionStore $session)
    {
        $this->prefix = '';
        $this->session = $session;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }

    public function getPath($path): string
    {
        return "{$this->prefix}{$path}";
    }

    public function getSession(): SessionStore
    {
        return $this->session;
    }

    public function getIpAddress(): string
    {
        $ip_keys = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR'
        ];

        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                // Headers can contain comma-separated lists of IPs if passed through multiple proxies
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);

                    // Validate that it is a proper IP format (v4 or v6)
                    if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return '0.0.0.0';
    }
}
