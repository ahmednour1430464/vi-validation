<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use Vi\Validation\Execution\ValidationContext;

#[RuleName(RuleId::MIN)]
final class MinRule implements RuleInterface, NumericAwareInterface
{
    private int|float $min;
    private bool $isNumeric = false;

    public function __construct(int|float $min)
    {
        $this->min = $min;
    }

    public function setNumeric(bool $numeric): void
    {
        $this->isNumeric = $numeric;
    }

    public function validate(mixed $value, string $field, ValidationContext $context): ?array
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            if (count($value) < $this->min) {
            return ['rule' => 'min', 'params' => ['type' => 'array', 'min' => $this->min]];
            }
            return null;
        }

        if (is_string($value)) {
            // If explicit numeric context is set and value is numeric, treat as number
            if ($this->isNumeric && is_numeric($value)) {
                if ($value < $this->min) {
                    return ['rule' => 'min', 'params' => ['type' => 'numeric', 'min' => $this->min]];
                }
                return null;
            }

            if (mb_strlen($value) < $this->min) {
                return ['rule' => 'min', 'params' => ['type' => 'string', 'min' => $this->min]];
            }
            return null;
        }

        if (is_int($value) || is_float($value)) {
            if ($value < $this->min) {
            return ['rule' => 'min', 'params' => ['type' => 'numeric', 'min' => $this->min]];
            }
        }

        return null;
    }
}
