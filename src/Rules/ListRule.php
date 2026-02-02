<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use Vi\Validation\Execution\ValidationContext;

#[RuleName('list')]
final class ListRule implements RuleInterface
{
    public function validate(mixed $value, string $field, ValidationContext $context): ?array
    {
        if ($value === null || !is_array($value)) {
            return null;
        }

        if (array_is_list($value)) {
            return null;
        }

        return ['rule' => 'list'];
    }
}
