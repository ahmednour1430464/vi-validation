<?php

declare(strict_types=1);

namespace Vi\Validation\Execution;

use Vi\Validation\Rules\DatabaseValidatorInterface;
use Vi\Validation\Rules\PasswordHasherInterface;

final class ValidationContext
{
    /** @var array<string, mixed> */
    private array $data;

    private ErrorCollector $errors;

    private ?DatabaseValidatorInterface $databaseValidator = null;
    private ?PasswordHasherInterface $passwordHasher = null;

    /**
     * @param array<string, mixed> $data
     * @param ErrorCollector $errors
     */
    public function __construct(array $data, ErrorCollector $errors)
    {
        $this->data = $data;
        $this->errors = $errors;
    }

    public function getDatabaseValidator(): ?DatabaseValidatorInterface
    {
        return $this->databaseValidator;
    }

    public function setDatabaseValidator(?DatabaseValidatorInterface $databaseValidator): void
    {
        $this->databaseValidator = $databaseValidator;
    }

    public function getPasswordHasher(): ?PasswordHasherInterface
    {
        return $this->passwordHasher;
    }

    public function setPasswordHasher(?PasswordHasherInterface $passwordHasher): void
    {
        $this->passwordHasher = $passwordHasher;
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

    public function hasValue(string $field): bool
    {
        if (strpos($field, '.') === false) {
            return array_key_exists($field, $this->data);
        }

        [$first, $second] = explode('.', $field, 2);

        if (!array_key_exists($first, $this->data) || !is_array($this->data[$first])) {
            return false;
        }

        return array_key_exists($second, $this->data[$first]);
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

    /**
     * @param array<string, mixed> $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * @param array<string, mixed> $params
     */
    public function addError(string $field, string $rule, ?string $message = null, array $params = []): void
    {
        $this->errors->add($field, $rule, $message, $params);
    }
}
