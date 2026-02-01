<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use Vi\Validation\Execution\ValidationContext;

#[RuleName('not_regex')]
final class NotRegexRule implements RuleInterface
{
    public function __construct(private string $pattern)
    {
    }

    public function validate(mixed $value, string $field, ValidationContext $context): ?array
    {
        if ($value === null) {
            return null;
        }

        if (!is_string($value) && !is_numeric($value)) {
            return ['rule' => 'not_regex'];
        }

        if (preg_match($this->pattern, (string) $value)) {
            return ['rule' => 'not_regex'];
        }

        return null;
    }
}
