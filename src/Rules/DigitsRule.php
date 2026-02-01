<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use Vi\Validation\Execution\ValidationContext;

#[RuleName('digits')]
final class DigitsRule implements RuleInterface
{
    private int $length;

    public function __construct(int $length)
    {
        $this->length = $length;
    }

    public function validate(mixed $value, string $field, ValidationContext $context): ?array
    {
        if ($value === null) {
            return null;
        }

        $stringValue = (string) $value;

        // Check if the value contains only digits
        if (!ctype_digit($stringValue)) {
            return [
                'rule' => 'digits',
                'params' => ['digits' => $this->length],
            ];
        }

        // Check if the length matches
        if (strlen($stringValue) !== $this->length) {
            return [
                'rule' => 'digits',
                'params' => ['digits' => $this->length],
            ];
        }

        return null;
    }
}
