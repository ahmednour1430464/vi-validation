<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use Vi\Validation\Execution\ValidationContext;

/**
 * Marker rule for 'exclude' logic.
 */
#[RuleName('exclude')]
final class ExcludeRule implements RuleInterface
{
    public function validate(mixed $value, string $field, ValidationContext $context): ?array
    {
        return null;
    }
}
