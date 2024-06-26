<?php
namespace ntentan\http;

use Psr\Http\Message\StreamInterface;

class StringStream implements StreamInterface
{
    private int $position = 0;
    private string $content;
    private bool $readable = false;
    
    public function __construct(string $content = '') {
        $this->content = $content;
    }
    
    public function setContent(string $content): void {
        $this->content = $content;
        $this->readable = true;
    }

    #[\Override]
    public function __toString(): string {
        return $this->content;
    }

    #[\Override]
    public function close(): void {
        $this->content = '';
        $this->position = 0;
        $this->readable = false;
    }

    #[\Override]
    public function detach() {
        $this->content = '';
        $this->position = 0;
        $this->readable = false;
    }

    #[\Override]
    public function eof(): bool {
        return $this->position == strlen($this->content);
    }

    #[\Override]
    public function getContents(): string {
        return $this->content;
    }

    #[\Override]
    public function getMetadata(?string $key = null) {
        return [];
    }

    #[\Override]
    public function getSize(): ?int {
        return strlen($this->content);
    }

    #[\Override]
    public function isReadable(): bool {
        return $this->readable;
    }

    #[\Override]
    public function isSeekable(): bool {
        return true;
    }

    #[\Override]
    public function isWritable(): bool {
        return false;
    }

    #[\Override]
    public function read(int $length): string {
        return substr($this->content, $this->position, min($length, strlen($this->content) - $this->position));
    }

    #[\Override]
    public function rewind(): void {
        $this->position = 0;
    }

    #[\Override]
    public function seek(int $offset, int $whence = SEEK_SET): void {
        $length = $this->getSize();
        $this->position = min(match($whence) {
            SEEK_SET => $offset,
            SEEK_CUR => $this->position + $offset,
            SEEK_END => $length
        }, $length);
    }

    #[\Override]
    public function tell(): int {
        return $this->position;
    }

    #[\Override]
    public function write(string $string): int {
        return 0;
    }
    
    public static function empty() {
        return new self("");
    }
}
