<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use Vi\Validation\Execution\ValidationContext;

#[RuleName(RuleId::CURRENT_PASSWORD)]
final class CurrentPasswordRule implements RuleInterface
{
    public function validate(mixed $value, string $field, ValidationContext $context): ?array
    {
        if ($value === null || !is_string($value)) {
            return null;
        }

        $hasher = $context->getPasswordHasher();

        if ($hasher === null) {
            return null;
        }

        if (!$hasher->check($value)) {
            return ['rule' => 'current_password'];
        }

        return null;
    }
}
