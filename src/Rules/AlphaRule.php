<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use Vi\Validation\Execution\ValidationContext;

#[RuleName('alpha')]
final class AlphaRule implements RuleInterface
{
    public function validate(mixed $value, string $field, ValidationContext $context): ?array
    {
        if ($value === null) {
            return null;
        }

        if (!is_string($value) || !preg_match('/^[\pL\pM]+$/u', $value)) {
            return ['rule' => 'alpha'];
        }

        return null;
    }
}
