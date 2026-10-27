<?php

namespace ntentan\sessions\php;

class DefaultSessionContainer implements \SessionHandlerInterface
{

    /**
     * @inheritDoc
     */
    public function close(): bool
    {
        // TODO: Implement close() method.
    }

    /**
     * @inheritDoc
     */
    public function destroy(string $id): bool
    {
        // TODO: Implement destroy() method.
    }

    /**
     * @inheritDoc
     */
    public function gc(int $max_lifetime): int|false
    {
        // TODO: Implement gc() method.
    }

    /**
     * @inheritDoc
     */
    public function open(string $path, string $name): bool
    {
        // TODO: Implement open() method.
    }

    /**
     * @inheritDoc
     */
    public function read(string $id): string|false
    {
        // TODO: Implement read() method.
    }

    /**
     * @inheritDoc
     */
    public function write(string $id, string $data): bool
    {
        // TODO: Implement write() method.
    }
}