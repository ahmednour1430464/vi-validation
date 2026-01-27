<?php

declare(strict_types=1);

namespace Vi\Validation\Execution;

final class ValidationContext
{
    /** @var array<string, mixed> */
    private array $data;

    private ErrorCollector $errors;

    public function __construct(array $data, ErrorCollector $errors)
    {
        $this->data = $data;
        $this->errors = $errors;
    }

    public function getValue(string $field): mixed
    {
        // Support simple fields and depth-2 nested fields using dot notation: parent.child
        if (strpos($field, '.') === false) {
            return $this->data[$field] ?? null;
        }

        [$first, $second] = explode('.', $field, 2);

        $level1 = $this->data[$first] ?? null;
        if (!is_array($level1)) {
            return null;
        }

        return $level1[$second] ?? null;
    }

    /**
     * Get all validation data.
     *
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    public function addError(string $field, string $rule, ?string $message = null): void
    {
        $this->errors->add($field, $rule, $message);
    }
}
