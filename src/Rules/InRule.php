<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use Vi\Validation\Execution\ValidationContext;

final class InRule implements RuleInterface
{
    /** @var list<string> */
    private array $values;

    /** @param list<string> $values */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    public function validate(mixed $value, string $field, ValidationContext $context): ?array
    {
        if ($value === null) {
            return null;
        }

        if (!in_array((string) $value, $this->values, true)) {
            return ['rule' => 'in'];
        }

        return null;
    }
}
