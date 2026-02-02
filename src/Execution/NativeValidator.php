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

        // Map errors to include resolved messages if needed
        $errors = $result['errors'];
        foreach ($errors as $field => &$fieldErrors) {
            foreach ($fieldErrors as &$error) {
                if ($error['message'] === null && $this->messageResolver !== null) {
                    $params = $error['params'] ?? [];
                    $error['message'] = $this->messageResolver->resolve($field, $error['rule'], $params);
                }
            }
        }

        return new ValidationResult(
            $errors,
            $data,
            $this->messageResolver,
            $result['excluded_fields'] ?? []
        );
    }
}
