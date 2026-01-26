<?php

declare(strict_types=1);

namespace Vi\Validation\Cache;

use Vi\Validation\Execution\CompiledSchema;

final class ArraySchemaCache implements SchemaCacheInterface
{
    /** @var array<string, array{schema: CompiledSchema, expires: int|null}> */
    private array $cache = [];

    public function get(string $key): ?CompiledSchema
    {
        if (!$this->has($key)) {
            return null;
        }

        return $this->cache[$key]['schema'];
    }

    public function put(string $key, CompiledSchema $schema, ?int $ttl = null): void
    {
        $this->cache[$key] = [
            'schema' => $schema,
            'expires' => $ttl !== null ? time() + $ttl : null,
        ];
    }

    public function has(string $key): bool
    {
        if (!isset($this->cache[$key])) {
            return false;
        }

        $expires = $this->cache[$key]['expires'];

        if ($expires !== null && time() > $expires) {
            $this->forget($key);
            return false;
        }

        return true;
    }

    public function forget(string $key): void
    {
        unset($this->cache[$key]);
    }

    public function flush(): void
    {
        $this->cache = [];
    }

    /**
     * Get all cached keys.
     *
     * @return list<string>
     */
    public function keys(): array
    {
        return array_keys($this->cache);
    }

    /**
     * Get the number of cached schemas.
     */
    public function count(): int
    {
        return count($this->cache);
    }
}
