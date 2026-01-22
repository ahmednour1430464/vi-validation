<?php

declare(strict_types=1);

namespace Vi\Validation\Execution;

use Vi\Validation\SchemaValidator;

final class ChunkedValidator
{
    private SchemaValidator $validator;

    public function __construct(SchemaValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param iterable<array<string, mixed>> $rows
     * @param positive-int $chunkSize
     * @param callable(int $chunkIndex, array $results): void $onChunk
     */
    public function validateInChunks(iterable $rows, int $chunkSize, callable $onChunk): void
    {
        $buffer = [];
        $chunkIndex = 0;

        foreach ($rows as $row) {
            $buffer[] = $row;

            if (count($buffer) >= $chunkSize) {
                $chunkResults = $this->validator->validateMany($buffer);
                $onChunk($chunkIndex, $chunkResults);
                $buffer = [];
                $chunkIndex++;
            }
        }

        if ($buffer !== []) {
            $chunkResults = $this->validator->validateMany($buffer);
            $onChunk($chunkIndex, $chunkResults);
        }
    }
}
