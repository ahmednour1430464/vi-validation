<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use Vi\Validation\Execution\ValidationContext;

final class MaxRule implements RuleInterface
{
    private int|float $max;

    public function __construct(int|float $max)
    {
        $this->max = $max;
    }

    public function validate(mixed $value, string $field, ValidationContext $context): ?array
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value) || is_array($value)) {
            if (count((array) $value) > $this->max) {
                return ['rule' => 'max'];
            }

            return null;
        }

        if (is_int($value) || is_float($value)) {
            if ($value > $this->max) {
                return ['rule' => 'max'];
            }
        }

        return null;
    }
}
