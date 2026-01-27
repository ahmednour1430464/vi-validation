<?php

declare(strict_types=1);

namespace Vi\Validation\Execution;

use Generator;
use Vi\Validation\SchemaValidator;

/**
 * Memory-efficient chunked validation for very large datasets.
 *
 * Processes data in configurable chunks, allowing for controlled memory usage
 * when dealing with millions of rows.
 */
final class ChunkedValidator
{
    private SchemaValidator $validator;

    public function __construct(SchemaValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Validate rows in chunks with a callback per chunk.
     *
     * Useful when you need to process results in batches (e.g., database inserts).
     *
     * @param iterable<array<string, mixed>> $rows
     * @param positive-int $chunkSize
     * @param callable(int $chunkIndex, list<ValidationResult> $results): void $onChunk
     */
    public function validateInChunks(iterable $rows, int $chunkSize, callable $onChunk): void
    {
        $buffer = [];
        $chunkIndex = 0;

        foreach ($rows as $row) {
            $buffer[] = $row;

            if (count($buffer) >= $chunkSize) {
                $this->processChunk($buffer, $chunkIndex, $onChunk);
                $buffer = [];
                $chunkIndex++;
            }
        }

        if ($buffer !== []) {
            $this->processChunk($buffer, $chunkIndex, $onChunk);
        }
    }

    /**
     * Stream-validate rows in chunks, yielding each chunk's results.
     *
     * Memory-efficient alternative that yields BatchValidationResult for each chunk.
     *
     * @param iterable<array<string, mixed>> $rows
     * @param positive-int $chunkSize
     * @return Generator<int, BatchValidationResult>
     */
    public function streamChunks(iterable $rows, int $chunkSize): Generator
    {
        $buffer = [];
        $chunkIndex = 0;

        foreach ($rows as $row) {
            $buffer[] = $row;

            if (count($buffer) >= $chunkSize) {
                yield $chunkIndex => new BatchValidationResult($this->validator->validateMany($buffer));
                $buffer = [];
                $chunkIndex++;
            }
        }

        if ($buffer !== []) {
            yield $chunkIndex => new BatchValidationResult($this->validator->validateMany($buffer));
        }
    }

    /**
     * Validate rows and yield only failures, processing in memory-efficient chunks.
     *
     * Ideal for error reporting where you only care about failures.
     *
     * @param iterable<array<string, mixed>> $rows
     * @param positive-int $chunkSize
     * @return Generator<int, ValidationResult> Yields failed results with their original row index
     */
    public function streamFailures(iterable $rows, int $chunkSize = 1000): Generator
    {
        $globalIndex = 0;
        $buffer = [];

        foreach ($rows as $row) {
            $buffer[] = ['index' => $globalIndex, 'data' => $row];
            $globalIndex++;

            if (count($buffer) >= $chunkSize) {
                yield from $this->yieldFailuresFromBuffer($buffer);
                $buffer = [];
            }
        }

        if ($buffer !== []) {
            yield from $this->yieldFailuresFromBuffer($buffer);
        }
    }

    /**
     * Count total failures without storing all results in memory.
     *
     * @param iterable<array<string, mixed>> $rows
     * @param positive-int $chunkSize
     */
    public function countFailures(iterable $rows, int $chunkSize = 1000): int
    {
        $failureCount = 0;

        foreach ($this->streamChunks($rows, $chunkSize) as $batchResult) {
            $failureCount += $batchResult->failureCount();
        }

        return $failureCount;
    }

    /**
     * @param list<array<string, mixed>> $buffer
     * @param callable(int $chunkIndex, list<ValidationResult> $results): void $onChunk
     */
    private function processChunk(array $buffer, int $chunkIndex, callable $onChunk): void
    {
        $results = $this->validator->validateMany($buffer);
        $onChunk($chunkIndex, $results);
    }

    /**
     * @param list<array{index: int, data: array<string, mixed>}> $buffer
     * @return Generator<int, ValidationResult>
     */
    private function yieldFailuresFromBuffer(array $buffer): Generator
    {
        foreach ($buffer as $item) {
            $result = $this->validator->validate($item['data']);

            if (!$result->isValid()) {
                yield $item['index'] => $result;
            }
        }
    }
}
