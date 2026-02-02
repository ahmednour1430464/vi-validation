<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use Vi\Validation\Execution\ValidationContext;

#[RuleName(RuleId::DECLINED_IF)]
final class DeclinedIfRule implements RuleInterface
{
    private const DECLINABLE = ['no', 'off', '0', 0, false, 'false'];

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

        if ($value === null || !in_array($value, self::DECLINABLE, true)) {
            return [
                'rule' => 'declined_if',
                'parameters' => [
                    'other' => $this->otherField,
                    'value' => (string) $this->value,
                ],
            ];
        }

        return null;
    }
}
