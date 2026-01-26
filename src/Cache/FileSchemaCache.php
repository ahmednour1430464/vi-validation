<?php

declare(strict_types=1);

namespace Vi\Validation\Cache;

use Vi\Validation\Execution\CompiledSchema;

final class FileSchemaCache implements SchemaCacheInterface
{
    private string $cachePath;
    private int $defaultTtl;

    public function __construct(string $cachePath, int $defaultTtl = 3600)
    {
        $this->cachePath = rtrim($cachePath, '/\\');
        $this->defaultTtl = $defaultTtl;

        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }

    public function get(string $key): ?CompiledSchema
    {
        $path = $this->getPath($key);

        if (!file_exists($path)) {
            return null;
        }

        $content = file_get_contents($path);
        if ($content === false) {
            return null;
        }

        $data = unserialize($content);

        if (!is_array($data) || !isset($data['schema'], $data['expires'])) {
            $this->forget($key);
            return null;
        }

        if ($data['expires'] !== null && time() > $data['expires']) {
            $this->forget($key);
            return null;
        }

        if (!$data['schema'] instanceof CompiledSchema) {
            $this->forget($key);
            return null;
        }

        return $data['schema'];
    }

    public function put(string $key, CompiledSchema $schema, ?int $ttl = null): void
    {
        $path = $this->getPath($key);
        $ttl = $ttl ?? $this->defaultTtl;

        $data = [
            'schema' => $schema,
            'expires' => $ttl > 0 ? time() + $ttl : null,
        ];

        file_put_contents($path, serialize($data), LOCK_EX);
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function forget(string $key): void
    {
        $path = $this->getPath($key);

        if (file_exists($path)) {
            unlink($path);
        }
    }

    public function flush(): void
    {
        $files = glob($this->cachePath . '/*.cache');

        if ($files !== false) {
            foreach ($files as $file) {
                unlink($file);
            }
        }
    }

    private function getPath(string $key): string
    {
        return $this->cachePath . '/' . md5($key) . '.cache';
    }

    /**
     * Get the cache directory path.
     */
    public function getCachePath(): string
    {
        return $this->cachePath;
    }
}
