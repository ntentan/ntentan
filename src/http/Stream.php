<?php
/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */
namespace ntentan\http;

use Psr\Http\Message\StreamInterface;

/**
 * Description of Stream
 *
 * @author ekow
 */
class Stream implements StreamInterface
{
    private $handle = null;
    private string $path;
    private string $mode;
    
    public function __construct(string $path, string $mode)
    {
        $this->path = $path;
        $this->mode = $mode;
    }
    
    private function getWrapper(): \streamWrapper
    {
        if ($this->handle === null) {
            $this->handle = fopen($this->path, $this->mode);
        }
        return $this->handle;
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
