<?php

declare(strict_types=1);

namespace Vi\Validation\Execution;

use Vi\Validation\Messages\MessageResolver;

final class ValidationResult
{
    /** @var array<string, list<array{rule: string, message: string|null}>> */
    private array $errors;
    private ?MessageResolver $messageResolver;
    private array $data;
    /** @var list<string> */
    private array $excludedFields;

    /**
     * @param ErrorCollector|array<string, list<array{rule: string, message: string|null}>> $errors
     * @param list<string> $excludedFields
     */
    public function __construct($errors, array $data = [], ?MessageResolver $messageResolver = null, array $excludedFields = [])
    {
        if ($errors instanceof ErrorCollector) {
            $this->errors = $errors->all();
        } else {
            $this->errors = $errors;
        }
        $this->data = $data;
        $this->messageResolver = $messageResolver;
        $this->excludedFields = $excludedFields;
    }

    /**
     * Get the validated data (including excluded fields).
     */
    public function data(): array
    {
        return $this->data;
    }

    /**
     * Get the validated data excluding fields that were marked for exclusion.
     */
    public function validated(): array
    {
        $validated = $this->data;

        foreach ($this->excludedFields as $field) {
            // Simple depth-1 removal for now
            if (isset($validated[$field])) {
                unset($validated[$field]);
            }
        }

        return $validated;
    }

    public function isValid(): bool
    {
        return $this->errors === [];
    }

    /**
     * @return array<string, list<array{rule: string, message: string|null}>>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Get formatted error messages grouped by field.
     *
     * @return array<string, list<string>>
     */
    public function messages(): array
    {
        $messages = [];
        $rawErrors = $this->errors;

        foreach ($rawErrors as $field => $fieldErrors) {
            $messages[$field] = [];
            foreach ($fieldErrors as $error) {
                // Use the pre-resolved message if available, otherwise use rule name
                $message = $error['message'] ?? $error['rule'];
                $messages[$field][] = $message;
            }
        }

        return $messages;
    }

    /**
     * Get all error messages as a flat array.
     *
     * @return list<string>
     */
    public function allMessages(): array
    {
        $all = [];
        foreach ($this->messages() as $messages) {
            array_push($all, ...$messages);
        }
        return $all;
    }

    /**
     * Get the first error message for a given field.
     */
    public function firstMessage(string $field): ?string
    {
        $messages = $this->messages();
        return $messages[$field][0] ?? null;
    }

    /**
     * Get the first error message from all fields.
     */
    public function first(): ?string
    {
        $all = $this->allMessages();
        return $all[0] ?? null;
    }

    /**
     * Convert the validation result to a string.
     */
    public function __toString(): string
    {
        if ($this->isValid()) {
            return 'Validation passed.';
        }

        return implode("\n", $this->allMessages());
    }
}
