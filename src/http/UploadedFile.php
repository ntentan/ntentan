<?php

namespace ntentan\http;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

class UploadedFile implements UploadedFileInterface
{
    private StreamInterface $stream;
    private int $size;
    private int $error;
    private string $fileName;
    private string $mediaType;
    private string $path;

    public function __construct(array $details) {
        $this->size = $details['size'];
        $this->error = $details['error'];
        $this->fileName = $details['name'];
        $this->mediaType = $details['type'];
        $this->path = $details['tmp_name'];
    }

    /**
     * @inheritDoc
     */
    public function getStream(): StreamInterface
    {
        if (!isset($this->stream)) {
            $this->stream = new Stream($this->path, 'r');
        }
        return $this->stream;
    }

    /**
     * @inheritDoc
     */
    public function moveTo(string $targetPath): void
    {
        move_uploaded_file($this->path, $targetPath);
    }

    /**
     * @inheritDoc
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * @inheritDoc
     */
    public function getError(): int
    {
        return $this->error;
    }

    /**
     * @inheritDoc
     */
    public function getClientFilename(): ?string
    {
        return $this->fileName;
    }

    /**
     * @inheritDoc
     */
    public function getClientMediaType(): ?string
    {
        return $this->mediaType;
    }
}