<?php

namespace ntentan\sessions;

class PhpSessionStore implements SessionStore
{
    private ?\SessionHandlerInterface $sessionHandler = null;

    public function __construct(?\SessionHandlerInterface $sessionHandler = null)
    {
        if (!is_null($sessionHandler)) {
            $this->sessionHandler = $sessionHandler;
            session_set_save_handler($sessionHandler, true);
        }
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function get(string $key): mixed
    {
        return $_SESSION[$key] ?? null;
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function destroy(): void
    {
        session_destroy();
    }
}
