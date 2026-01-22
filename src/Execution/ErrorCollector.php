<?php

declare(strict_types=1);

namespace Vi\Validation\Execution;

final class ErrorCollector
{
    /** @var array<string, list<array{rule: string, message: string|null}>> */
    private array $errors = [];

    public function add(string $field, string $rule, ?string $message = null): void
    {
        $this->errors[$field][] = [
            'rule' => $rule,
            'message' => $message,
        ];
    }

    public function hasErrors(): bool
    {
        return $this->errors !== [];
    }

    /**
     * @return array<string, list<array{rule: string, message: string|null}>>
     */
    public function all(): array
    {
        return $this->errors;
    }
}
