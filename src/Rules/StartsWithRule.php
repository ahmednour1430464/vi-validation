<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use Vi\Validation\Execution\ValidationContext;

final class StartsWithRule implements RuleInterface
{
    /** @var list<string> */
    private array $values;

    /**
     * @param list<string> $values
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    public function validate(mixed $value, string $field, ValidationContext $context): ?array
    {
        if ($value === null) {
            return null;
        }

        if (!is_string($value)) {
            return null;
        }

        foreach ($this->values as $prefix) {
            if (str_starts_with($value, $prefix)) {
                return null;
            }
        }

        return [
            'rule' => 'starts_with',
            'params' => ['values' => implode(', ', $this->values)],
        ];
    }
}
