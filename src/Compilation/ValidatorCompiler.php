<?php

declare(strict_types=1);

namespace Vi\Validation\Compilation;

use Vi\Validation\Execution\CompiledSchema;
use Vi\Validation\Cache\SchemaCacheInterface;

final class ValidatorCompiler
{
    private ?SchemaCacheInterface $cache;
    private bool $precompile;
    private ?string $cachePath;

    public function __construct(
        ?SchemaCacheInterface $cache = null,
        bool $precompile = false,
        ?string $cachePath = null
    ) {
        $this->cache = $cache;
        $this->precompile = $precompile;
        $this->cachePath = $cachePath;
    }

    /**
     * Compile and cache a schema.
     *
     * @param array<string, mixed> $rules
     */
    public function compile(string $key, array $rules, callable $compiler): CompiledSchema
    {
        // Check cache first
        if ($this->cache !== null) {
            $cached = $this->cache->get($key);
            if ($cached !== null) {
                return $cached;
            }
        }

        // Compile the schema
        $schema = $compiler($rules);

        // Store in cache
        if ($this->cache !== null) {
            $this->cache->put($key, $schema);
        }

        // Precompile to file if enabled
        if ($this->precompile && $this->cachePath !== null) {
            $this->writePrecompiled($key, $schema);
        }

        return $schema;
    }

    /**
     * Load a precompiled schema.
     */
    public function loadPrecompiled(string $key): ?CompiledSchema
    {
        if ($this->cachePath === null) {
            return null;
        }

        $path = $this->getPrecompiledPath($key);

        if (!file_exists($path)) {
            return null;
        }

        $content = file_get_contents($path);
        if ($content === false) {
            return null;
        }

        $schema = unserialize($content);

        return $schema instanceof CompiledSchema ? $schema : null;
    }

    /**
     * Write a precompiled schema to file.
     */
    private function writePrecompiled(string $key, CompiledSchema $schema): void
    {
        if ($this->cachePath === null) {
            return;
        }

        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }

        $path = $this->getPrecompiledPath($key);
        file_put_contents($path, serialize($schema), LOCK_EX);
    }

    private function getPrecompiledPath(string $key): string
    {
        return $this->cachePath . '/' . md5($key) . '.compiled';
    }

    /**
     * Clear all precompiled schemas.
     */
    public function clearPrecompiled(): void
    {
        if ($this->cachePath === null || !is_dir($this->cachePath)) {
            return;
        }

        $files = glob($this->cachePath . '/*.compiled');

        if ($files !== false) {
            foreach ($files as $file) {
                unlink($file);
            }
        }
    }

    /**
     * Generate a cache key from rules.
     *
     * @param array<string, mixed> $rules
     */
    public static function generateKey(array $rules): string
    {
        return md5(serialize($rules));
    }
}
