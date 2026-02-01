<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use Vi\Validation\Execution\ValidationContext;

final class MultipleOfRule implements RuleInterface
{
    public function __construct(private int|float $factor)
    {
    }

    public function validate(mixed $value, string $field, ValidationContext $context): ?array
    {
        if ($value === null) {
            return null;
        }

        if (!is_numeric($value)) {
            return ['rule' => 'multiple_of', 'parameters' => [$this->factor]];
        }

        $value = (float) $value;
        $factor = (float) $this->factor;

        if ($factor == 0) {
            return ['rule' => 'multiple_of', 'parameters' => [$this->factor]];
        }

        if (fmod($value, $factor) !== 0.0) {
            return ['rule' => 'multiple_of', 'parameters' => [$this->factor]];
        }

        return null;
    }
}
