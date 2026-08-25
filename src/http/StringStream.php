<?php

namespace ntentan\http;

use Psr\Http\Message\StreamInterface;
use RuntimeException;

class StringStream implements StreamInterface
{
    private int $position = 0;
    private string $content;
    private bool $readable = true;
    private bool $writable = true;

    public function __construct(string $content = '')
    {
        $this->content = $content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
        $this->position = 0;
        $this->readable = true;
        $this->writable = true;
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->content;
    }

    #[\Override]
    public function close(): void
    {
        $this->detach();
    }

    #[\Override]
    public function detach()
    {
        $this->content = '';
        $this->position = 0;
        $this->readable = false;
        $this->writable = false;
        return null;
    }

    #[\Override]
    public function eof(): bool
    {
        return $this->position >= strlen($this->content);
    }

    #[\Override]
    public function getContents(): string
    {
        if (!$this->readable) {
            throw new RuntimeException('Stream is detached or not readable');
        }
        $remaining = substr($this->content, $this->position);
        $this->position = strlen($this->content);
        return $remaining;
    }

    #[\Override]
    public function getMetadata(?string $key = null)
    {
        $meta = [
            'timed_out' => false,
            'blocked' => true,
            'eof' => $this->eof(),
            'unread_bytes' => max(0, strlen($this->content) - $this->position),
            'stream_type' => 'string',
            'wrapper_type' => 'string',
            'wrapper_data' => null,
            'mode' => 'r+',
            'seekable' => true,
            'uri' => 'php://memory',
        ];

        if ($key === null) {
            return $meta;
        }

        return $meta[$key] ?? null;
    }

    #[\Override]
    public function getSize(): ?int
    {
        return strlen($this->content);
    }

    #[\Override]
    public function isReadable(): bool
    {
        return $this->readable;
    }

    #[\Override]
    public function isSeekable(): bool
    {
        return $this->readable;
    }

    #[\Override]
    public function isWritable(): bool
    {
        return $this->writable;
    }

    #[\Override]
    public function read(int $length): string
    {
        if (!$this->readable) {
            throw new RuntimeException('Stream is detached or not readable');
        }
        if ($length < 0) {
            throw new RuntimeException('Length parameter cannot be negative');
        }
        if ($length === 0) {
            return '';
        }
        $result = substr($this->content, $this->position, min($length, strlen($this->content) - $this->position));
        $this->position += strlen($result);
        return $result;
    }

    #[\Override]
    public function rewind(): void
    {
        $this->seek(0);
    }

    #[\Override]
    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        if (!$this->readable) {
            throw new RuntimeException('Stream is detached or not seekable');
        }
        $length = strlen($this->content);
        $newPosition = match ($whence) {
            SEEK_SET => $offset,
            SEEK_CUR => $this->position + $offset,
            SEEK_END => $length + $offset,
            default => throw new RuntimeException(sprintf('Invalid whence "%d"', $whence)),
        };

        if ($newPosition < 0 || $newPosition > $length) {
            throw new RuntimeException(sprintf('Invalid offset "%d" for stream seek', $offset));
        }

        $this->position = $newPosition;
    }

    #[\Override]
    public function tell(): int
    {
        if (!$this->readable) {
            throw new RuntimeException('Stream is detached');
        }
        return $this->position;
    }

    #[\Override]
    public function write(string $string): int
    {
        if (!$this->writable) {
            throw new RuntimeException('Stream is detached or not writable');
        }
        $length = strlen($string);
        $before = substr($this->content, 0, $this->position);
        $after = substr($this->content, $this->position + $length);
        $this->content = $before . $string . $after;
        $this->position += $length;
        return $length;
    }

    public static function empty(): self
    {
        return new self("");
    }
}
