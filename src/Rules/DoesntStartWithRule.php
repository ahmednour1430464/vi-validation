<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use Vi\Validation\Execution\ValidationContext;

final class DoesntStartWithRule implements RuleInterface
{
    /** @var string[] */
    private array $needles;

    public function __construct(string ...$needles)
    {
        $this->needles = $needles;
    }

    public function validate(mixed $value, string $field, ValidationContext $context): ?array
    {
        if ($value === null) {
            return null;
        }

        if (!is_string($value)) {
            return ['rule' => 'doesnt_start_with'];
        }

        foreach ($this->needles as $needle) {
            if ($needle !== '' && str_starts_with($value, $needle)) {
                return ['rule' => 'doesnt_start_with', 'parameters' => ['values' => implode(', ', $this->needles)]];
            }
        }

        return null;
    }
}
