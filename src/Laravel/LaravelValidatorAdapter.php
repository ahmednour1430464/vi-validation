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
     * @param iterable<string, mixed> $data
     * @param array<string, mixed> $rules
     * @param array<string, string> $messages
     * @param array<string, string> $customAttributes
     */
    public function make(iterable $data, array $rules, array $messages = [], array $customAttributes = [])
    {
        // For now, only use the fast engine for simple rule sets; otherwise, fall back.
        // This can be extended later with a richer capability matrix.
        $fastValidator = $this->fastFactory->make($data, $rules);

        return $fastValidator;
    }

    /**
     * @param string $rule
     * @param \Closure|string $extension
     * @param string|null $message
     */
    public function extend($rule, $extension, $message = null): void
    {
        $this->fallback->extend($rule, $extension, $message);
    }

    /**
     * @param string $rule
     * @param \Closure|string $extension
     * @param string|null $message
     */
    public function extendImplicit($rule, $extension, $message = null): void
    {
        $this->fallback->extendImplicit($rule, $extension, $message);
    }

    /**
     * @param string $rule
     * @param \Closure|string $replacer
     */
    public function replacer($rule, $replacer): void
    {
        $this->fallback->replacer($rule, $replacer);
    }

    /**
     * @param \Closure|string $resolver
     */
    public function resolver($resolver): void
    {
        if (method_exists($this->fallback, 'resolver')) {
            $this->fallback->resolver($resolver);
        }
    }
}
