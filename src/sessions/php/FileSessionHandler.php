<?php

namespace ntentan\sessions\php;

use ntentan\exceptions\NtentanException;

/**
 * A session handler that stores session data in files.
 */
class FileSessionHandler implements \SessionHandlerInterface
{
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
     * - lifespan: The session lifetime in seconds (defaults to 3600).
     *
     * @param array $sessionConfig An array containing session configuration settings.
     */
    public function __construct(array $sessionConfig)
    {
        $this->config = $sessionConfig;
        $this->lifespan = $sessionConfig['lifespan'] ?? 3600;
        session_set_save_handler($this, true);
    }

    private function getSessionFile(string $id): string
    {
        return "{$this->sessionPath}/session_{$this->sessionName}_{$id}";
    }

    public function open(string $path, string $name): bool
    {
        $this->sessionPath = $this->config['path'] ?? $path;
        $this->sessionName = $name;
        if (!is_dir($this->sessionPath)) {
            if ($this->config['createPath'] ?? false) {
                mkdir($this->sessionPath, $this->config['pathPermissions'] ?? 0777, true);
            } else {
                throw new NtentanException("Session path [{$this->sessionPath}] does not exist or is not a directory.");
            }
        }
        if (!is_writable($this->sessionPath)) {
            throw new NtentanException("Session path [{$this->sessionPath}] is not writable.");
        }
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        $file = $this->getSessionFile($id);
        if (file_exists($file)) {
            if (filemtime($file) + $this->lifespan > time()) {
                return file_get_contents($file);
            } else {
                return '';
            }
        } else {
            return '';
        }
    }

    public function write(string $id, string $data): bool
    {
        $file = fopen($this->getSessionFile($id), "w");

        if ($file !== false) {
            fwrite($file, $data);
            fclose($file);
            return true;
        } else {
            return false;
        }
    }

    public function destroy(string $id): bool
    {
        $file = $this->getSessionFile($id);
        if (file_exists($file)) {
            return unlink($file);
        }
        return true;
    }

    public function gc(int $max_lifetime): int|false
    {
        $count = 0;
        $files = glob("{$this->sessionPath}/session_{$this->sessionName}_*");
        if ($files === false) {
            return false;
        }
        foreach ($files as $filename) {
            if (filemtime($filename) + $this->lifespan < time()) {
                if (unlink($filename)) {
                    $count++;
                }
            }
        }
        return $count;
    }
}
