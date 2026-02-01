<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use Vi\Validation\Execution\ValidationContext;

#[RuleName('integer', aliases: ['int'])]
final class IntegerTypeRule implements RuleInterface
{
    public function validate(mixed $value, string $field, ValidationContext $context): ?array
    {
        if ($value === null) {
            return null;
        }

        if (!is_int($value)) {
            return ['rule' => 'integer'];
        }

        return null;
    }
}
