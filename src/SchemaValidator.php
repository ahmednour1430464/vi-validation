<?php

declare(strict_types=1);

namespace Vi\Validation;

use Generator;
use Vi\Validation\Execution\CompiledSchema;
use Vi\Validation\Execution\ValidatorEngine;
use Vi\Validation\Execution\ValidationResult;
use Vi\Validation\Schema\SchemaBuilder;

final class SchemaValidator
{
    private CompiledSchema $schema;
    private ValidatorEngine $engine;

    public function __construct(CompiledSchema $schema, ?ValidatorEngine $engine = null)
    {
        $this->schema = $schema;
        $this->engine = $engine ?? new ValidatorEngine();
    }

    public static function build(callable $definition): self
    {
        $builder = new SchemaBuilder();
        $definition($builder);

        return new self($builder->compile());
    }

    public function validate(array $data): ValidationResult
    {
        return $this->engine->validate($this->schema, $data);
    }

    /**
     * Validate multiple rows and return all results at once.
     *
     * WARNING: This method materializes all results in memory. For large datasets
     * (10,000+ rows), use stream() or each() instead to avoid memory exhaustion.
     *
     * @param iterable<array<string, mixed>> $rows
     * @return list<ValidationResult>
     */
    public function validateMany(iterable $rows): array
    {
        $results = [];

        foreach ($rows as $row) {
            $results[] = $this->validate($row);
        }

        return $results;
    }

    /**
     * Stream-validate rows using a generator for memory-efficient batch processing.
     *
     * This method yields results one at a time, allowing PHP to garbage collect
     * each result after processing. Ideal for large datasets (ETL, imports, queues).
     *
     * Usage:
     * ```php
     * foreach ($validator->stream($rows) as $index => $result) {
     *     if (!$result->isValid()) {
     *         // Handle error
     *     }
     * }
     * ```
     *
     * @param iterable<array<string, mixed>> $rows
     * @return Generator<int, ValidationResult>
     */
    public function stream(iterable $rows): Generator
    {
        $index = 0;

        foreach ($rows as $row) {
            yield $index => $this->validate($row);
            $index++;
        }
    }

    /**
     * Validate rows with a callback, processing each result immediately.
     *
     * This method never stores results in memory, making it ideal for
     * fire-and-forget validation of large datasets.
     *
     * Usage:
     * ```php
     * $validator->each($rows, function (ValidationResult $result, int $index) {
     *     if (!$result->isValid()) {
     *         Log::error("Row $index failed", $result->errors());
     *     }
     * });
     * ```
     *
     * @param iterable<array<string, mixed>> $rows
     * @param callable(ValidationResult $result, int $index): void $callback
     */
    public function each(iterable $rows, callable $callback): void
    {
        $index = 0;

        foreach ($rows as $row) {
            $result = $this->validate($row);
            $callback($result, $index);
            $index++;
        }
    }

    /**
     * Validate rows and collect only failures, streaming through all data.
     *
     * Memory-efficient way to find all validation errors without storing
     * successful validations. Useful for batch import error reporting.
     *
     * @param iterable<array<string, mixed>> $rows
     * @return Generator<int, ValidationResult> Yields only failed validation results with their original index
     */
    public function failures(iterable $rows): Generator
    {
        $index = 0;

        foreach ($rows as $row) {
            $result = $this->validate($row);

            if (!$result->isValid()) {
                yield $index => $result;
            }

            $index++;
        }
    }

    /**
     * Validate rows until the first failure, then stop.
     *
     * Useful for fail-fast validation where you want to abort on first error.
     *
     * @param iterable<array<string, mixed>> $rows
     * @return ValidationResult|null The first failed result, or null if all pass
     */
    public function firstFailure(iterable $rows): ?ValidationResult
    {
        foreach ($this->failures($rows) as $result) {
            return $result;
        }

        return null;
    }

    /**
     * Check if all rows pass validation without storing results.
     *
     * Memory-efficient way to validate entire dataset. Stops at first failure.
     *
     * @param iterable<array<string, mixed>> $rows
     */
    public function allValid(iterable $rows): bool
    {
        return $this->firstFailure($rows) === null;
    }
}
