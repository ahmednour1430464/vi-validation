<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use Vi\Validation\Execution\ValidationContext;

interface RuleInterface
{
    /**
     * @return array{rule: string, message?: string}|null
     */
    public function validate(mixed $value, string $field, ValidationContext $context): ?array;
}
