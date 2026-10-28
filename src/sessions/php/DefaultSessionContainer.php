<?php

namespace ntentan\sessions\php;

class DefaultSessionContainer implements \SessionHandlerInterface
{
    private array $sessions = [];

    /**
     * @inheritDoc
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function destroy(string $id): bool
    {
        unset($this->sessions[$id]);
        return true;
    }

    /**
     * @inheritDoc
     */
    public function gc(int $max_lifetime): int|false
    {
        return 0;
    }

    /**
     * @inheritDoc
     */
    public function open(string $path, string $name): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function read(string $id): string|false
    {
        return $this->sessions[$id] ?? '';
    }

    /**
     * @inheritDoc
     */
    public function write(string $id, string $data): bool
    {
        $this->sessions[$id] = $data;
        return true;
    }
}
