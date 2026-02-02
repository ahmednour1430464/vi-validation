<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use Vi\Validation\Execution\ValidationContext;

/**
 * Marker rule for 'bail' logic.
 */
#[RuleName(RuleId::BAIL)]
final class BailRule implements RuleInterface
{
    public function validate(mixed $value, string $field, ValidationContext $context): ?array
    {
        return null;
    }
}
