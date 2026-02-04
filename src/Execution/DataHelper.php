<?php

declare(strict_types=1);

namespace Vi\Validation\Execution;

/**
 * Helper class for data access.
 */
final class DataHelper
{
    /**
     * Get a value from an array using dot notation.
     *
     * @param array<string, mixed> $data
     */
    public static function get(array $data, string $key, mixed $default = null): mixed
    {
        if (strpos($key, '.') === false) {
            return $data[$key] ?? $default;
        }

        foreach (explode('.', $key) as $segment) {
            if (!is_array($data) || !array_key_exists($segment, $data)) {
                return $default;
            }

            $data = $data[$segment];
        }

        return $data;
    }
}
