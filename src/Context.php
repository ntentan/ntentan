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

    public function setPrefix(string $prefix)
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
}
