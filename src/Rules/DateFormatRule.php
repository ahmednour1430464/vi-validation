<?php

declare(strict_types=1);

namespace Vi\Validation\Rules;

use DateTimeImmutable;
use Vi\Validation\Execution\ValidationContext;

#[RuleName('date_format')]
final class DateFormatRule implements RuleInterface
{
    public function __construct(private string $format)
    {
    }

    public function validate(mixed $value, string $field, ValidationContext $context): ?array
    {
        if ($value === null) {
            return null;
        }

        if (!is_string($value) && !is_numeric($value)) {
            return ['rule' => 'date_format', 'parameters' => ['format' => $this->format]];
        }

        $value = (string) $value;
        $date = DateTimeImmutable::createFromFormat($this->format, $value);

        if ($date === false || $date->format($this->format) !== $value) {
            return ['rule' => 'date_format', 'parameters' => [0 => $this->format, 'format' => $this->format]];
        }

        return null;
    }
}
