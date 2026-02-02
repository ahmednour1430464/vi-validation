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
     */
    public static function get(array $data, string $key): mixed
    {
        if (strpos($key, '.') === false) {
            return $data[$key] ?? null;
        }

        foreach (explode('.', $key) as $segment) {
            if (!is_array($data) || !array_key_exists($segment, $data)) {
                return null;
            }

            $data = $data[$segment];
        }

        return $data;
    }
}
