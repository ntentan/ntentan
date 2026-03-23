<?php

namespace ntentan\sessions\php;

use ntentan\utils\exceptions\FileNotFoundException;

/**
 * A session handler that stores session data in files.
 * This session handler is
 */
class FileSessionHandler implements \SessionHandlerInterface
{
    private string $file;
    private string $sessionName;
    private string $sessionPath;
    private array $config;
    private int $lifespan;

    /**
     * Constructor for FileSessionHandler.
     * Initializes the session handler with configuration settings.
     *
     * The $sessionConfig array can contain the following keys:
     * - path: The path where session files will be stored.
     * - createPath: Whether to create the path if it does not exist.
     * - pathPermissions: The permissions to set for the session files.
     *
     * @param array $sessionConfig An array containing session configuration settings.
     */
    public function __construct(array $sessionConfig)
    {
        $this->config = $sessionConfig;
        $this->lifespan = $sessionConfig['lifespan'] ?? 3600;
        session_set_save_handler($this, true);
    }

    public function open($path, $name): bool
    {
        $this->sessionPath = $this->config['path'] ?? $path;
        $this->sessionName = $name;
        if (!is_dir($this->sessionPath) && $this->config['createPath'] ?? false) {
            throw new FileNotFoundException("Session path [{$this->sessionPath}] does not exist or is not a directory.");
        }
        if (!is_writable($this->sessionPath)) {
            throw new FileNotFoundException("Session path [{$this->sessionPath}] is not writable.");
        }
        mkdir($this->sessionPath, $this->config['pathPermissions'] ?? 0777, true);
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read($id): string
    {
        $this->file = "{$this->sessionPath}/session_{$this->sessionName}_{$id}";
        if (file_exists($this->file)) {
            if (filemtime($this->file) + $this->lifespan > time()) {
                return file_get_contents($this->file);
            } else {
                return '';
            }
        } else {
            return '';
        }
    }

    public function write($id, $data): bool
    {
        $file = fopen($this->file, "w");

        if ($file !== false) {
            fwrite($file, $data);
            fclose($file);
            return true;
        } else {
            return false;
        }
    }

    public function destroy($id): bool
    {
        $file = $this->getSessionFile($id);
        return unlink($file);
    }

    public function gc($lifetime): false|int
    {
        foreach (glob("{$this->sessionPath}/sess_*") as $filename) {
            if (filemtime($filename) + $this->lifespan < time()) {
                unlink($filename);
            }
        }
        return true;
    }
}
