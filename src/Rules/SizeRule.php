<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use Vi\Validation\Execution\ValidationContext;

final class SizeRule implements RuleInterface
{
    private int|float $size;

    public function __construct(int|float $size)
    {
        $this->size = $size;
    }

    public function validate(mixed $value, string $field, ValidationContext $context): ?array
    {
        if ($value === null) {
            return null;
        }

        $actualSize = $this->getSize($value);

        // Use loose comparison to handle int/float differences
        if ((float) $actualSize != (float) $this->size) {
            return ['rule' => 'size'];
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
