<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use Vi\Validation\Execution\ValidationContext;

final class LowercaseRule implements RuleInterface
{
    public function validate(mixed $value, string $field, ValidationContext $context): ?array
    {
        if ($value === null) {
            return null;
        }

        if (!is_string($value)) {
            return ['rule' => 'lowercase'];
        }

        if (mb_strtolower($value, 'UTF-8') !== $value) {
            return ['rule' => 'lowercase'];
        }

        return null;
    }
}
