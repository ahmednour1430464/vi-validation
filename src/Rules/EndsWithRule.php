<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use Vi\Validation\Execution\ValidationContext;

#[RuleName('ends_with')]
final class EndsWithRule implements RuleInterface
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

        foreach ($this->values as $suffix) {
            if (str_ends_with($value, $suffix)) {
                return null;
            }
        }

        return [
            'rule' => 'ends_with',
            'params' => ['values' => implode(', ', $this->values)],
        ];
    }
}
