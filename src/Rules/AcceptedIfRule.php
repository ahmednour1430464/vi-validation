<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use Vi\Validation\Execution\ValidationContext;

#[RuleName('accepted_if')]
final class AcceptedIfRule implements RuleInterface
{
    private const ACCEPTABLE = ['yes', 'on', '1', 1, true, 'true'];

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

        if ($otherValue !== $this->value) {
            return null;
        }

        if ($value === null || !in_array($value, self::ACCEPTABLE, true)) {
            return [
                'rule' => 'accepted_if',
                'parameters' => [
                    'other' => $this->otherField,
                    'value' => (string) $this->value,
                ],
            ];
        }

        return null;
    }
}
