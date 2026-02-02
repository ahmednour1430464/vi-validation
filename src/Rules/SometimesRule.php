<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use Vi\Validation\Execution\ValidationContext;

/**
 * Marker rule for 'sometimes' logic.
 */
#[RuleName(RuleId::SOMETIMES)]
final class SometimesRule implements RuleInterface
{
    public function validate(mixed $value, string $field, ValidationContext $context): ?array
    {
        return null;
    }
}
