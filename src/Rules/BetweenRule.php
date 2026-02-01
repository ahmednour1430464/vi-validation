<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use Vi\Validation\Execution\ValidationContext;

final class BetweenRule implements RuleInterface
{
    private int|float $min;
    private int|float $max;

    public function __construct(int|float $min, int|float $max)
    {
        $this->min = $min;
        $this->max = $max;
    }

    public function validate(mixed $value, string $field, ValidationContext $context): ?array
    {
        if ($value === null) {
            return null;
        }

        $size = $this->getSize($value);
        
        // Determine type
        $type = 'numeric';
        if (is_array($value)) {
            $type = 'array';
        } elseif (is_string($value)) {
            $type = 'string';
        }

        if ($size < $this->min || $size > $this->max) {
            return ['rule' => 'between', 'type' => $type, 'min' => $this->min, 'max' => $this->max];
        }

        return null;
    }

    private function getSize(mixed $value): int|float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        if (is_string($value)) {
            return mb_strlen($value);
        }

        if (is_array($value)) {
            return count($value);
        }

        return 0;
    }
}
