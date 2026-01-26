<?php

declare(strict_types=1);

namespace Vi\Validation\Runtime;

use Vi\Validation\Execution\ValidationResult;
use Vi\Validation\Execution\ValidatorEngine;
use Vi\Validation\Execution\CompiledSchema;

/**
 * Stateless validator wrapper for use in long-running processes.
 * Ensures no state leaks between requests.
 */
final class StatelessValidator implements RuntimeAwareInterface
{
    private ValidatorEngine $engine;
    private ContextManager $contextManager;

    public function __construct(
        ?ValidatorEngine $engine = null,
        ?ContextManager $contextManager = null
    ) {
        $this->engine = $engine ?? new ValidatorEngine();
        $this->contextManager = $contextManager ?? new ContextManager();
    }

    public function onWorkerStart(): void
    {
        $this->contextManager->onWorkerStart();
    }

    public function onRequestStart(): void
    {
        $this->contextManager->onRequestStart();
    }

    public function onRequestEnd(): void
    {
        $this->contextManager->onRequestEnd();
    }

    public function onWorkerStop(): void
    {
        $this->contextManager->onWorkerStop();
    }

    /**
     * Validate data against a compiled schema.
     *
     * @param array<string, mixed> $data
     */
    public function validate(CompiledSchema $schema, array $data): ValidationResult
    {
        try {
            $this->onRequestStart();
            return $this->engine->validate($schema, $data);
        } finally {
            $this->onRequestEnd();
        }
    }

    /**
     * Get the context manager.
     */
    public function getContextManager(): ContextManager
    {
        return $this->contextManager;
    }

    /**
     * Get the validator engine.
     */
    public function getEngine(): ValidatorEngine
    {
        return $this->engine;
    }
}
