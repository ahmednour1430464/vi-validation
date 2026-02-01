<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use Vi\Validation\Execution\ValidationContext;

#[RuleName('alpha_num')]
final class AlphanumericRule implements RuleInterface
{
    public function validate(mixed $value, string $field, ValidationContext $context): ?array
    {
        if ($value === null) {
            return null;
        }

        if (!is_string($value) && !is_numeric($value)) {
            return ['rule' => 'alpha_num'];
        }

        if (!preg_match('/^[\pL\pM\pN]+$/u', (string) $value)) {
            return ['rule' => 'alpha_num'];
        }

        return null;
    }
}
