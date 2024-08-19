<?php

namespace ntentan\sessions;

class PhpSessionStore implements SessionStore
{
    private \SessionHandlerInterface $sessionHandler;

    public function __construct(?\SessionHandlerInterface $sessionHandler)
    {
        if (!is_null($sessionHandler)) {
            $this->sessionHandler = $sessionHandler;
            session_set_save_handler($sessionHandler, true);
        }
        session_start();
    }

    public function set(string $key, mixed $value)
    {
        $_SESSION[$key] = $value;
    }

    public function get(string $key): mixed
    {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }


    public function destroy()
    {
        session_destroy();
    }
}