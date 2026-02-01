<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use Vi\Validation\Execution\ValidationContext;

#[RuleName('declined')]
final class DeclinedRule implements RuleInterface
{
    private const DECLINED = ['no', 'off', '0', 0, false, 'false'];

    public function validate(mixed $value, string $field, ValidationContext $context): ?array
    {
        if ($value === null) {
            return ['rule' => 'declined'];
        }

        if (!in_array($value, self::DECLINED, true)) {
            return ['rule' => 'declined'];
        }

        return null;
    }
}
