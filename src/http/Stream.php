<?php

namespace ntentan\http;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * Wraps standard PHP streams with the PSR-7 streaming interface.
 *
 * @author ekow
 */
class Stream implements StreamInterface
{
    private const array READ_MODES = [
        'r', 'r+', 'w+', 'a+', 'x+', 'c+',
        'rb', 'r+b', 'w+b', 'a+b', 'x+b', 'c+b',
        'rt', 'r+t', 'w+t', 'a+t', 'x+t', 'c+t'
    ];
    private const array WRITE_MODES = [
        'w', 'w+', 'r+', 'a', 'a+', 'x', 'x+', 'c', 'c+',
        'wb', 'w+b', 'r+b', 'ab', 'a+b', 'xb', 'x+b', 'cb', 'c+b',
        'wt', 'w+t', 'r+t', 'at', 'a+t', 'xt', 'x+t', 'ct', 'c+t'
    ];

    /** @var resource|null */
    private $resource = null;
    private bool $seekable = false;
    private bool $readable = false;
    private bool $writable = false;
    private ?int $size = null;

    /**
     * @param resource|string $stream
     * @param string $mode
     */
    public function __construct(mixed $stream, string $mode = 'r')
    {
        if (is_resource($stream)) {
            $this->resource = $stream;
        } elseif (is_string($stream)) {
            $resource = @fopen($stream, $mode);
            if (!is_resource($resource)) {
                throw new InvalidArgumentException(sprintf('Invalid stream or path provided: "%s"', $stream));
            }
            $this->resource = $resource;
        } else {
            throw new InvalidArgumentException('Stream must be a string path or a resource');
        }

        $meta = stream_get_meta_data($this->resource);
        $this->seekable = $meta['seekable'] ?? false;
        $streamMode = $meta['mode'] ?? $mode;
        $this->readable = in_array($streamMode, self::READ_MODES, true);
        $this->writable = in_array($streamMode, self::WRITE_MODES, true);
    }

    #[\Override]
    public function __toString(): string
    {
        try {
            if ($this->isSeekable()) {
                $this->rewind();
            }
            return $this->getContents();
        } catch (\Throwable) {
            return '';
        }
    }

    #[\Override]
    public function close(): void
    {
        if (is_resource($this->resource)) {
            fclose($this->resource);
        }
        $this->detach();
    }

    #[\Override]
    public function detach()
    {
        $resource = $this->resource;
        $this->resource = null;
        $this->size = null;
        $this->readable = false;
        $this->writable = false;
        $this->seekable = false;
        return $resource;
    }

    #[\Override]
    public function getSize(): ?int
    {
        if ($this->size !== null) {
            return $this->size;
        }
        if (!is_resource($this->resource)) {
            return null;
        }
        $stats = fstat($this->resource);
        if (is_array($stats) && isset($stats['size'])) {
            $this->size = $stats['size'];
            return $this->size;
        }
        return null;
    }

    #[\Override]
    public function tell(): int
    {
        if (!is_resource($this->resource)) {
            throw new RuntimeException('Stream is detached or invalid');
        }
        $result = ftell($this->resource);
        if ($result === false) {
            throw new RuntimeException('Unable to determine stream position');
        }
        return $result;
    }

    #[\Override]
    public function eof(): bool
    {
        return !is_resource($this->resource) || feof($this->resource);
    }

    #[\Override]
    public function isSeekable(): bool
    {
        return $this->seekable;
    }

    #[\Override]
    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        if (!is_resource($this->resource)) {
            throw new RuntimeException('Stream is detached or invalid');
        }
        if (!$this->seekable) {
            throw new RuntimeException('Stream is not seekable');
        }
        if (fseek($this->resource, $offset, $whence) === -1) {
            throw new RuntimeException(sprintf('Unable to seek to stream position "%d" with whence "%d"', $offset, $whence));
        }
    }

    #[\Override]
    public function rewind(): void
    {
        $this->seek(0);
    }

    #[\Override]
    public function isWritable(): bool
    {
        return $this->writable;
    }

    #[\Override]
    public function write(string $string): int
    {
        if (!is_resource($this->resource)) {
            throw new RuntimeException('Stream is detached or invalid');
        }
        if (!$this->writable) {
            throw new RuntimeException('Cannot write to a non-writable stream');
        }
        $this->size = null;
        $result = fwrite($this->resource, $string);
        if ($result === false) {
            throw new RuntimeException('Unable to write to stream');
        }
        return $result;
    }

    #[\Override]
    public function isReadable(): bool
    {
        return $this->readable;
    }

    #[\Override]
    public function read(int $length): string
    {
        if (!is_resource($this->resource)) {
            throw new RuntimeException('Stream is detached or invalid');
        }
        if (!$this->readable) {
            throw new RuntimeException('Cannot read from a non-readable stream');
        }
        if ($length < 0) {
            throw new RuntimeException('Length parameter cannot be negative');
        }
        if ($length === 0) {
            return '';
        }
        $result = fread($this->resource, $length);
        if ($result === false) {
            throw new RuntimeException('Unable to read from stream');
        }
        return $result;
    }

    #[\Override]
    public function getContents(): string
    {
        if (!is_resource($this->resource)) {
            throw new RuntimeException('Stream is detached or invalid');
        }
        if (!$this->readable) {
            throw new RuntimeException('Cannot read from a non-readable stream');
        }
        $contents = stream_get_contents($this->resource);
        if ($contents === false) {
            throw new RuntimeException('Unable to read stream contents');
        }
        return $contents;
    }

    #[\Override]
    public function getMetadata(?string $key = null)
    {
        if (!is_resource($this->resource)) {
            return $key !== null ? null : [];
        }
        $meta = stream_get_meta_data($this->resource);
        if ($key === null) {
            return $meta;
        }
        return $meta[$key] ?? null;
    }
}
