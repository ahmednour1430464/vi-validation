<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use Vi\Validation\Execution\ValidationContext;

#[RuleName('nullable')]
final class NullableRule implements RuleInterface
{
    public function validate(mixed $value, string $field, ValidationContext $context): ?array
    {
        // Nullable is handled by short-circuiting other rules; this rule itself never fails.
        return null;
    }
}
