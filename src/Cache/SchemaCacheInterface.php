<?php

declare(strict_types=1);

namespace Vi\Validation\Cache;

use Vi\Validation\Execution\CompiledSchema;

interface SchemaCacheInterface
{
    /**
     * Get a compiled schema from cache.
     */
    public function get(string $key): ?CompiledSchema;

    /**
     * Store a compiled schema in cache.
     */
    public function put(string $key, CompiledSchema $schema, ?int $ttl = null): void;

    /**
     * Check if a schema exists in cache.
     */
    public function has(string $key): bool;

    /**
     * Remove a schema from cache.
     */
    public function forget(string $key): void;

    /**
     * Clear all cached schemas.
     */
    public function flush(): void;
}
