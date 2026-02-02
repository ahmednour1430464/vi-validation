<?php

declare(strict_types=1);

namespace Vi\Validation\Execution;

use Vi\Validation\Messages\MessageResolver;

/**
 * NativeValidator executes a precompiled PHP closure for maximum performance.
 */
final class NativeValidator
{
    /** @var \Closure */
    private $closure;
    private ?MessageResolver $messageResolver;

    public function __construct(callable $closure, ?MessageResolver $messageResolver = null)
    {
        $this->closure = $closure;
        $this->messageResolver = $messageResolver ?? new MessageResolver();
    }

    /**
     * Validate the given data using the precompiled closure.
     */
    public function validate(array $data): ValidationResult
    {
        $result = ($this->closure)($data);

        return new ValidationResult(
            $result['errors'],
            $data,
            $this->messageResolver,
            $result['excluded_fields'] ?? []
        );
    }
}
