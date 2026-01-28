<?php

declare(strict_types=1);

namespace Vi\Validation\Laravel;

use Generator;
use Illuminate\Contracts\Validation\Validator as LaravelValidatorContract;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\ValidationException;
use Vi\Validation\Execution\ValidationResult;
use Vi\Validation\SchemaValidator;

final class FastValidatorWrapper implements LaravelValidatorContract
{
    private SchemaValidator $validator;

    /** @var iterable<string, mixed> */
    private iterable $data;

    /** @var array<string, mixed>|null Materialized data cache */
    private ?array $materializedData = null;

    /** @var array<string, mixed> */
    private array $rules = [];

    /** @var array<string, string> */
    private array $customMessages = [];

    /** @var array<string, string> */
    private array $customAttributes = [];

    private ?ValidationResult $result = null;

    /** @var array<callable> */
    private array $afterCallbacks = [];

    private bool $stopOnFirstFailure = false;

    /**
     * @param iterable<string, mixed> $data Array or generator of data to validate
     */
    public function __construct(SchemaValidator $validator, iterable $data)
    {
        $this->validator = $validator;
        $this->data = $data;
    }

    /**
     * Materialize the data to an array if it's a generator/iterator.
     *
     * @return array<string, mixed>
     */
    private function materializeData(): array
    {
        if ($this->materializedData !== null) {
            return $this->materializedData;
        }

        if (is_array($this->data)) {
            $this->materializedData = $this->data;
        } else {
            $this->materializedData = iterator_to_array($this->data);
        }

        return $this->materializedData;
    }

    public function fails(): bool
    {
        return !$this->passes();
    }

    public function passes(): bool
    {
        if ($this->result === null) {
            $this->result = $this->validator->validate($this->materializeData());
            
            // Execute after callbacks
            foreach ($this->afterCallbacks as $callback) {
                $callback($this);
            }
        }

        return $this->result->isValid();
    }

    public function errors(): MessageBag
    {
        $bag = new MessageBag();

        if ($this->result === null) {
            $this->passes();
        }

        // Use proper messages from ValidationResult
        foreach ($this->result->messages() as $field => $messages) {
            foreach ($messages as $message) {
                $bag->add($field, $message);
            }
        }

        return $bag;
    }

    public function after($callback)
    {
        $this->afterCallbacks[] = $callback;
        return $this;
    }

    public function sometimes($attribute, $rules, callable $callback)
    {
        // TODO: Implement conditional rule application
        return $this;
    }

    public function getMessageBag()
    {
        return $this->errors();
    }

    public function validated()
    {
        if ($this->fails()) {
            throw new ValidationException($this);
        }

        return $this->materializeData();
    }

    public function validate(): array
    {
        if ($this->fails()) {
            throw new ValidationException($this);
        }

        return $this->materializeData();
    }

    public function failed(): array
    {
        if ($this->result === null) {
            $this->passes();
        }

        $failed = [];
        foreach ($this->result->errors() as $field => $fieldErrors) {
            foreach ($fieldErrors as $error) {
                $failed[$field][$error['rule']] = [];
            }
        }

        return $failed;
    }

    public function getData()
    {
        return $this->materializeData();
    }

    /**
     * Get the raw iterable data without materializing.
     *
     * Useful when you need to pass the original generator/iterator
     * to streaming methods without consuming it.
     *
     * @return iterable<string, mixed>
     */
    public function getRawData(): iterable
    {
        return $this->data;
    }

    /**
     * @param iterable<string, mixed> $data
     */
    public function setData($data)
    {
        $this->data = $data;
        $this->materializedData = null;
        $this->result = null;
    }

    public function sometimesWith($attribute, $rules, callable $callback)
    {
        return $this;
    }

    /**
     * Get the validation rules.
     *
     * @return array<string, mixed>
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * Set the validation rules.
     *
     * @param array<string, mixed> $rules
     */
    public function setRules(array $rules): self
    {
        $this->rules = $rules;
        $this->result = null;
        return $this;
    }

    /**
     * Add additional rules to the existing rules.
     *
     * @param array<string, mixed> $rules
     */
    public function addRules(array $rules): self
    {
        $this->rules = array_merge($this->rules, $rules);
        $this->result = null;
        return $this;
    }

    /**
     * Get the custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function getCustomMessages(): array
    {
        return $this->customMessages;
    }

    /**
     * Set custom messages for validation errors.
     *
     * @param array<string, string> $messages
     */
    public function setCustomMessages(array $messages): self
    {
        $this->customMessages = $messages;
        return $this;
    }

    /**
     * Get the custom attributes for validation errors.
     *
     * @return array<string, string>
     */
    public function getCustomAttributes(): array
    {
        return $this->customAttributes;
    }

    /**
     * Set custom attributes for validation errors.
     *
     * @param array<string, string> $attributes
     */
    public function setCustomAttributes(array $attributes): self
    {
        $this->customAttributes = $attributes;
        return $this;
    }

    /**
     * Stop validation on first failure.
     */
    public function stopOnFirstFailure(bool $stop = true): self
    {
        $this->stopOnFirstFailure = $stop;
        return $this;
    }

    /**
     * Get the safe (validated) data.
     *
     * @return array<string, mixed>
     */
    public function safe(): array
    {
        return $this->validated();
    }

    /**
     * Stream-validate multiple rows using a generator for memory-efficient batch processing.
     *
     * This method yields results one at a time, allowing PHP to garbage collect
     * each result after processing. Ideal for large datasets (ETL, imports, queues).
     *
     * Usage:
     * ```php
     * // Stream through wrapper's data
     * foreach ($wrapper->stream() as $index => $result) {
     *     if (!$result->isValid()) {
     *         // Handle error
     *     }
     * }
     *
     * // Or stream through custom rows
     * foreach ($wrapper->stream($rows) as $index => $result) {
     *     // ...
     * }
     * ```
     *
     * @param iterable<array<string, mixed>>|null $rows Rows to validate, or null to use wrapper's data
     * @return Generator<int, ValidationResult>
     */
    public function stream(?iterable $rows = null): Generator
    {
        return $this->validator->stream($rows ?? $this->data);
    }

    /**
     * Validate rows with a callback, processing each result immediately.
     *
     * This method never stores results in memory, making it ideal for
     * fire-and-forget validation of large datasets.
     *
     * Usage:
     * ```php
     * // Process wrapper's data
     * $wrapper->each(function (ValidationResult $result, int $index) {
     *     if (!$result->isValid()) {
     *         Log::error("Row $index failed", $result->errors());
     *     }
     * });
     *
     * // Or process custom rows
     * $wrapper->each($rows, function (ValidationResult $result, int $index) {
     *     // ...
     * });
     * ```
     *
     * @param iterable<array<string, mixed>>|callable $rowsOrCallback Rows to validate, or callback when using wrapper's data
     * @param callable(ValidationResult $result, int $index): void|null $callback
     */
    public function each(iterable|callable $rowsOrCallback, ?callable $callback = null): void
    {
        if (is_callable($rowsOrCallback)) {
            $this->validator->each($this->data, $rowsOrCallback);
        } else {
            $this->validator->each($rowsOrCallback, $callback);
        }
    }

    /**
     * Validate rows and collect only failures, streaming through all data.
     *
     * Memory-efficient way to find all validation errors without storing
     * successful validations. Useful for batch import error reporting.
     *
     * @param iterable<array<string, mixed>>|null $rows Rows to validate, or null to use wrapper's data
     * @return Generator<int, ValidationResult> Yields only failed validation results with their original index
     */
    public function failures(?iterable $rows = null): Generator
    {
        return $this->validator->failures($rows ?? $this->data);
    }

    /**
     * Validate rows until the first failure, then stop.
     *
     * Useful for fail-fast validation where you want to abort on first error.
     *
     * @param iterable<array<string, mixed>>|null $rows Rows to validate, or null to use wrapper's data
     * @return ValidationResult|null The first failed result, or null if all pass
     */
    public function firstFailure(?iterable $rows = null): ?ValidationResult
    {
        return $this->validator->firstFailure($rows ?? $this->data);
    }

    /**
     * Check if all rows pass validation without storing results.
     *
     * Memory-efficient way to validate entire dataset. Stops at first failure.
     *
     * @param iterable<array<string, mixed>>|null $rows Rows to validate, or null to use wrapper's data
     */
    public function allValid(?iterable $rows = null): bool
    {
        return $this->validator->allValid($rows ?? $this->data);
    }

    /**
     * Validate multiple rows and return all results at once.
     *
     * WARNING: This method materializes all results in memory. For large datasets
     * (10,000+ rows), use stream() or each() instead to avoid memory exhaustion.
     *
     * @param iterable<array<string, mixed>>|null $rows Rows to validate, or null to use wrapper's data
     * @return list<ValidationResult>
     */
    public function validateMany(?iterable $rows = null): array
    {
        return $this->validator->validateMany($rows ?? $this->data);
    }
}
