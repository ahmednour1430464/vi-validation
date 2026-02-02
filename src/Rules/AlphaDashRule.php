<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use Vi\Validation\Execution\ValidationContext;

#[RuleName(RuleId::ALPHA_DASH)]
final class AlphaDashRule implements RuleInterface
{
    public function validate(mixed $value, string $field, ValidationContext $context): ?array
    {
        if ($value === null) {
            return null;
        }

        if (!is_string($value) && !is_numeric($value)) {
            return ['rule' => 'alpha_dash'];
        }

        if (!preg_match('/^[\pL\pM\pN_-]+$/u', (string) $value)) {
            return ['rule' => 'alpha_dash'];
        }

        return null;
    }
}
