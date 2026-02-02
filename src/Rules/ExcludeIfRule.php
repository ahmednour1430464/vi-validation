<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use Vi\Validation\Execution\ValidationContext;

#[RuleName(RuleId::EXCLUDE_IF)]
final class ExcludeIfRule implements RuleInterface
{
    private string $otherField;
    private mixed $value;

    public function __construct(string $otherField, mixed $value)
    {
        $this->otherField = $otherField;
        $this->value = $value;
    }

    public function shouldExclude(ValidationContext $context): bool
    {
        return $context->getValue($this->otherField) === $this->value;
    }

    public function validate(mixed $value, string $field, ValidationContext $context): ?array
    {
        return null;
    }
}
