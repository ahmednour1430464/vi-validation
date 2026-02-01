<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use Vi\Validation\Execution\ValidationContext;

#[RuleName('json')]
final class JsonRule implements RuleInterface
{
    public function validate(mixed $value, string $field, ValidationContext $context): ?array
    {
        if ($value === null) {
            return null;
        }

        if (!is_string($value)) {
            return ['rule' => 'json'];
        }

        json_decode($value);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['rule' => 'json'];
        }

        return null;
    }
}
