<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use DateTimeImmutable;
use Vi\Validation\Execution\ValidationContext;

#[RuleName(RuleId::DATE)]
final class DateRule implements RuleInterface
{
    private ?string $format;

    public function __construct(?string $format = null)
    {
        $this->format = $format;
    }

    public function validate(mixed $value, string $field, ValidationContext $context): ?array
    {
        if ($value === null) {
            return null;
        }

        if (!is_string($value)) {
            return ['rule' => 'date'];
        }

        if ($this->format !== null) {
            $date = DateTimeImmutable::createFromFormat($this->format, $value);
            if ($date === false || $date->format($this->format) !== $value) {
                return ['rule' => 'date'];
            }
        } else {
            if (strtotime($value) === false) {
                return ['rule' => 'date'];
            }
        }

        return null;
    }
}
