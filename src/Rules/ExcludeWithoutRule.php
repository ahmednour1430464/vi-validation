<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use Vi\Validation\Execution\ValidationContext;

#[RuleName('exclude_without')]
final class ExcludeWithoutRule implements RuleInterface
{
    private string $otherField;

    public function __construct(string $otherField)
    {
        $this->otherField = $otherField;
    }

    public function shouldExclude(ValidationContext $context): bool
    {
        return !$context->hasValue($this->otherField);
    }

    public function validate(mixed $value, string $field, ValidationContext $context): ?array
    {
        return null;
    }
}
