<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use Vi\Validation\Execution\ValidationContext;

final class SameRule implements RuleInterface
{
    private string $otherField;

    public function __construct(string $otherField)
    {
        $this->otherField = $otherField;
    }

    public function validate(mixed $value, string $field, ValidationContext $context): ?array
    {
        if ($value === null) {
            return null;
        }

        $otherValue = $context->getValue($this->otherField);

        if ($value !== $otherValue) {
            return ['rule' => 'same'];
        }

        return null;
    }
}
