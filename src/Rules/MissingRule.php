<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use Vi\Validation\Execution\ValidationContext;

#[RuleName('missing')]
final class MissingRule implements RuleInterface
{
    public function validate(mixed $value, string $field, ValidationContext $context): ?array
    {
        if ($context->hasValue($field)) {
            return ['rule' => 'missing'];
        }

        return null;
    }
}
