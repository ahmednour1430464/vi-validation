<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use Vi\Validation\Execution\ValidationContext;

#[RuleName('boolean', aliases: ['bool'])]
final class BooleanRule implements RuleInterface
{
    private const ACCEPTABLE = [true, false, 0, 1, '0', '1'];

    public function validate(mixed $value, string $field, ValidationContext $context): ?array
    {
        if ($value === null) {
            return null;
        }

        if (!in_array($value, self::ACCEPTABLE, true)) {
            return ['rule' => 'boolean'];
        }

        return null;
    }
}
