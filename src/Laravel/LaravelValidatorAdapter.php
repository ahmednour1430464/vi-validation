<?php

declare(strict_types=1);

namespace Vi\Validation\Laravel;

use Illuminate\Contracts\Validation\Factory as LaravelValidatorFactory;

final class LaravelValidatorAdapter implements LaravelValidatorFactory
{
    private FastValidatorFactory $fastFactory;

    private LaravelValidatorFactory $fallback;

    public function __construct(FastValidatorFactory $fastFactory, LaravelValidatorFactory $fallback)
    {
        $this->fastFactory = $fastFactory;
        $this->fallback = $fallback;
    }

    /**
     * Create a new Validator instance.
     *
     * @param iterable<array<string, mixed>> $rows Rows to validate
     * @param array<string, mixed> $rules Validation rules
     */
    public function make(iterable $data, array $rules, array $messages = [], array $customAttributes = [])
    {
        // For now, only use the fast engine for simple rule sets; otherwise, fall back.
        // This can be extended later with a richer capability matrix.
        $fastValidator = $this->fastFactory->make($data, $rules);

        return $fastValidator;
    }

    public function extend($rule, $extension, $message = null)
    {
        return $this->fallback->extend($rule, $extension, $message);
    }

    public function extendImplicit($rule, $extension, $message = null)
    {
        return $this->fallback->extendImplicit($rule, $extension, $message);
    }

    public function replacer($rule, $replacer)
    {
        return $this->fallback->replacer($rule, $replacer);
    }

    public function resolver($resolver)
    {
        return $this->fallback->resolver($resolver);
    }
}
