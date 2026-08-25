<?php

namespace ntentan\http;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

class UploadedFile implements UploadedFileInterface
{
    private ?StreamInterface $stream = null;
    private ?int $size = null;
    private int $error = UPLOAD_ERR_OK;
    private ?string $fileName = null;
    private ?string $mediaType = null;
    private string $path = '';
    private bool $moved = false;

    /**
     * @param array|StreamInterface|string $spec
     * @param int|null $size
     * @param int $error
     * @param string|null $clientFilename
     * @param string|null $clientMediaType
     */
    public function __construct(
        mixed $spec, ?int $size = null, int $error = UPLOAD_ERR_OK, 
        ?string $clientFilename = null, ?string $clientMediaType = null
    ) {
        if (is_array($spec)) {
            $this->size = isset($spec['size']) ? (int)$spec['size'] : null;
            $this->error = $spec['error'] ?? UPLOAD_ERR_OK;
            $this->fileName = $spec['name'] ?? null;
            $this->mediaType = $spec['type'] ?? null;
            $this->path = $spec['tmp_name'] ?? '';
        } elseif ($spec instanceof StreamInterface) {
            $this->stream = $spec;
            $this->size = $size ?? $spec->getSize();
            $this->error = $error;
            $this->fileName = $clientFilename;
            $this->mediaType = $clientMediaType;
        } elseif (is_string($spec)) {
            $this->path = $spec;
            $this->size = $size;
            $this->error = $error;
            $this->fileName = $clientFilename;
            $this->mediaType = $clientMediaType;
        } else {
            throw new InvalidArgumentException('Uploaded file source must be an array, stream, or file path string');
        }
    }

    #[\Override]
    public function getStream(): StreamInterface
    {
        if ($this->moved) {
            throw new RuntimeException('Cannot retrieve stream after file has been moved');
        }
        if ($this->error !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Cannot retrieve stream due to upload error');
        }

        if ($this->stream === null) {
            $this->stream = new Stream($this->path, 'r');
        }
        return $this->stream;
    }

    #[\Override]
    public function moveTo(string $targetPath): void
    {
        if ($this->moved) {
            throw new RuntimeException('File has already been moved');
        }
        if ($this->error !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Cannot move file due to upload error');
        }
        if ($targetPath === '') {
            throw new InvalidArgumentException('Target path cannot be empty');
        }

        $targetDirectory = dirname($targetPath);
        if (!is_dir($targetDirectory) || !is_writable($targetDirectory)) {
            throw new RuntimeException(sprintf('The target directory "%s" is not writable', $targetDirectory));
        }

        $sapi = PHP_SAPI;
        if (empty($sapi) || str_starts_with($sapi, 'cli') || !is_uploaded_file($this->path)) {
            if ($this->path !== '') {
                if (!@rename($this->path, $targetPath)) {
                    if (!@copy($this->path, $targetPath)) {
                        throw new RuntimeException(sprintf('Failed to move uploaded file to "%s"', $targetPath));
                    }
                    @unlink($this->path);
                }
            } else {
                $dest = new Stream($targetPath, 'w');
                $src = $this->getStream();
                if ($src->isSeekable()) {
                    $src->rewind();
                }
                while (!$src->eof()) {
                    $dest->write($src->read(8192));
                }
                $dest->close();
            }
        } else {
            if (!move_uploaded_file($this->path, $targetPath)) {
                throw new RuntimeException(sprintf('Failed to move uploaded file "%s" to "%s"', $this->path, $targetPath));
            }
        }

        $this->moved = true;
    }

    #[\Override]
    public function getSize(): ?int
    {
        return $this->size;
    }

    #[\Override]
    public function getError(): int
    {
        return $this->error;
    }

    #[\Override]
    public function getClientFilename(): ?string
    {
        return $this->fileName;
    }

    #[\Override]
    public function getClientMediaType(): ?string
    {
        return $this->mediaType;
    }
}