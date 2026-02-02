<?php

declare(strict_types=1);

namespace Vi\Validation\Execution;

final class ErrorCollector
{
    /** @var array<string, list<array{rule: string, params: array<string, mixed>, message: string|null}>> */
    private array $errors = [];

    private int $errorCount = 0;

    /**
     * @param array<string, mixed> $params
     */
    public function add(string $field, string $rule, ?string $message = null, array $params = []): void
    {
        $this->errors[$field][] = [
            'rule' => $rule,
            'params' => $params,
            'message' => $message,
        ];
        $this->errorCount++;
    }

    public function reset(): void
    {
        $this->errors = [];
        $this->errorCount = 0;
    }

    public function hasErrors(): bool
    {
        return $this->errors !== [];
    }

    public function count(): int
    {
        return $this->errorCount;
    }

    /**
     * @return array<string, list<array{rule: string, message: string|null}>>
     */
    public function all(): array
    {
        return $this->errors;
    }
}
