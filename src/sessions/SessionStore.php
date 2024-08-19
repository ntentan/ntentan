<?php

namespace ntentan\sessions;

interface SessionStore
{
    public function set(string $key, mixed $value);
    public function get(string $key): mixed;
    public function has(string $key): bool;
    public function remove(string $key): void;
    public function destroy();
}
