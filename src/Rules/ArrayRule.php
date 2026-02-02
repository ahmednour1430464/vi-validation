<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use Vi\Validation\Execution\ValidationContext;

#[RuleName(RuleId::ARRAY)]
final class ArrayRule implements RuleInterface
{
    public function validate(mixed $value, string $field, ValidationContext $context): ?array
    {
        if ($value === null) {
            return null;
        }

        if (!is_array($value)) {
            return ['rule' => 'array'];
        }

        return null;
    }
}
