<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use Vi\Validation\Execution\ValidationContext;

/**
 * When validating arrays, the field must not have any duplicate values.
 */
#[RuleName(RuleId::DISTINCT)]
final class DistinctRule implements RuleInterface
{
    private bool $strict;
    private bool $ignoreCase;

    public function __construct(bool $strict = false, bool $ignoreCase = false)
    {
        $this->strict = $strict;
        $this->ignoreCase = $ignoreCase;
    }

    public function validate(mixed $value, string $field, ValidationContext $context): ?array
    {
        if ($value === null || !is_array($value)) {
            return null;
        }

        $values = $value;

        // Normalize values if ignore_case
        if ($this->ignoreCase) {
            $values = array_map(function ($v) {
                return is_string($v) ? strtolower($v) : $v;
            }, $values);
        }

        // Check for duplicates
        if ($this->strict) {
            $unique = array_unique($values, SORT_REGULAR);
        } else {
            $unique = array_unique($values);
        }

        if (count($unique) !== count($values)) {
            return ['rule' => 'distinct'];
        }

        return null;
    }
}
