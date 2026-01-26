<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use Vi\Validation\Execution\ValidationContext;

final class UrlRule implements RuleInterface
{
    public function validate(mixed $value, string $field, ValidationContext $context): ?array
    {
        if ($value === null) {
            return null;
        }

        if (!is_string($value)) {
            return ['rule' => 'url'];
        }

        if (filter_var($value, FILTER_VALIDATE_URL) === false) {
            return ['rule' => 'url'];
        }

        return null;
    }
}
