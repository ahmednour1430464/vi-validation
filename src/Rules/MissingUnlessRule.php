<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use Vi\Validation\Execution\ValidationContext;

#[RuleName('missing_unless')]
final class MissingUnlessRule implements RuleInterface
{
    private string $otherField;
    private mixed $value;

    public function __construct(string $otherField, mixed $value)
    {
        $this->otherField = $otherField;
        $this->value = $value;
    }

    public function validate(mixed $value, string $field, ValidationContext $context): ?array
    {
        $otherValue = $context->getValue($this->otherField);

        if ($otherValue === $this->value) {
            return null;
        }

        if ($context->hasValue($field)) {
            return [
                'rule' => 'missing_unless',
                'parameters' => [
                    'other' => $this->otherField,
                    'value' => (string) $this->value,
                ],
            ];
        }

        return null;
    }
}
