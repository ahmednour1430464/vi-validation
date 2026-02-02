<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use Vi\Validation\Execution\ValidationContext;

#[RuleName(RuleId::REQUIRED)]
final class RequiredRule implements RuleInterface
{
    public function validate(mixed $value, string $field, ValidationContext $context): ?array
    {
        if ($value === null) {
            return ['rule' => 'required'];
        }

        if (is_string($value) && $value === '') {
            return ['rule' => 'required'];
        }

        if (is_array($value) && $value === []) {
            return ['rule' => 'required'];
        }

        return null;
    }
}
