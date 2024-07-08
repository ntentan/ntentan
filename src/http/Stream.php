<?php
namespace ntentan\http;

use Psr\Http\Message\StreamInterface;

/**
 * Wraps standard PHP streams with the PSR-7 streaming interface.
 * This class is useful for constructing PSR-7 compliant streams around standard PHP streams.
 *
 * @author ekow
 */
class Stream implements StreamInterface
{
    private $handle = null;
    private string $path;
    private string $mode;
    private bool $isReadable;
    
    public function __construct(string $path, string $mode)
    {
        $this->path = $path;
        $this->mode = $mode;
        $this->handle = fopen($path, $mode);
        $this->isReadable = in_array($mode, ['r', 'r+', 'w+', 'a+', 'x+', 'c+']);
    }
    
    #[\Override]
    public function __toString(): string
    {
        return file_get_contents($this->path);
    }

    #[\Override]
    public function close(): void
    {
        $this->handle->stream_close();
    }

    #[\Override]
    public function detach()
    {
        
    }

    #[\Override]
    public function eof(): bool
    {
        
    }

    #[\Override]
    public function getContents(): string
    {
        return $this->__toString();
    }

    #[\Override]
    public function getMetadata(?string $key = null)
    {
        
    }

    #[\Override]
    public function getSize(): ?int
    {
        
    }

    #[\Override]
    public function isReadable(): bool
    {
        return $this->isReadable;
    }

    #[\Override]
    public function isSeekable(): bool
    {
        
    }

    #[\Override]
    public function isWritable(): bool
    {
        
    }

    #[\Override]
    public function read(int $length): string
    {
        
    }

    #[\Override]
    public function rewind(): void
    {
        
    }

    #[\Override]
    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        
    }

    #[\Override]
    public function tell(): int
    {
        
    }

    #[\Override]
    public function write(string $string): int
    {
        
    }
}
