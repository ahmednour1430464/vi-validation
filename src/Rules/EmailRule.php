<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use Vi\Validation\Execution\ValidationContext;

#[RuleName(RuleId::EMAIL)]
final class EmailRule implements RuleInterface
{
    public function validate(mixed $value, string $field, ValidationContext $context): ?array
    {
        if ($value === null) {
            return null;
        }

        if (!is_string($value)) {
            return ['rule' => 'email'];
        }

        // Use PHP's filter_var for now; can be micro-optimized later.
        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            return ['rule' => 'email'];
        }

        return null;
    }
}
