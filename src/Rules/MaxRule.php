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

        if (is_array($value)) {
            if (count($value) > $this->max) {
            return ['rule' => 'max', 'params' => ['type' => 'array', 'max' => $this->max]];
            }
            return null;
        }

        if (is_string($value)) {
            if (mb_strlen($value) > $this->max) {
            return ['rule' => 'max', 'params' => ['type' => 'string', 'max' => $this->max]];
            }
            return null;
        }

        if (is_int($value) || is_float($value)) {
            if ($value > $this->max) {
            return ['rule' => 'max', 'params' => ['type' => 'numeric', 'max' => $this->max]];
            }
        }

        return null;
    }
}
