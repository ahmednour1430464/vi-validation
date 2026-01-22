<?php

declare(strict_types=1);

namespace Vi\Validation\Execution;

final class ValidationResult
{
    private ErrorCollector $errors;

    public function __construct(ErrorCollector $errors)
    {
        $this->errors = $errors;
    }

    public function isValid(): bool
    {
        return !$this->errors->hasErrors();
    }

    /**
     * @return array<string, list<array{rule: string, message: string|null}>>
     */
    public function errors(): array
    {
        return $this->errors->all();
    }
}
