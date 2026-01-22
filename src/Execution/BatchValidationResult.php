<?php

declare(strict_types=1);

namespace Vi\Validation\Execution;

final class BatchValidationResult
{
    /** @var list<ValidationResult> */
    private array $results;

    /** @param list<ValidationResult> $results */
    public function __construct(array $results)
    {
        $this->results = $results;
    }

    /**
     * @return list<ValidationResult>
     */
    public function all(): array
    {
        return $this->results;
    }
}
