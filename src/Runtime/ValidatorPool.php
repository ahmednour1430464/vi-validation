<?php

declare(strict_types=1);

namespace Vi\Validation\Runtime;

use SplQueue;

/**
 * Pool of validator instances for reuse in long-running processes.
 */
final class ValidatorPool implements RuntimeAwareInterface
{
    /** @var SplQueue<StatelessValidator> */
    private SplQueue $pool;

    private int $maxSize;
    private int $created = 0;

    public function __construct(int $maxSize = 10)
    {
        $this->maxSize = $maxSize;
        $this->pool = new SplQueue();
    }

    public function onWorkerStart(): void
    {
        // Pre-warm the pool with some validators
        $warmCount = min(3, $this->maxSize);
        for ($i = 0; $i < $warmCount; $i++) {
            $validator = $this->createValidator();
            $validator->onWorkerStart();
            $this->pool->enqueue($validator);
        }
    }

    public function onRequestStart(): void
    {
        // Nothing to do at request start
    }

    public function onRequestEnd(): void
    {
        // Nothing to do at request end
    }

    public function onWorkerStop(): void
    {
        while (!$this->pool->isEmpty()) {
            $validator = $this->pool->dequeue();
            $validator->onWorkerStop();
        }
        $this->created = 0;
    }

    /**
     * Acquire a validator from the pool.
     */
    public function acquire(): StatelessValidator
    {
        if (!$this->pool->isEmpty()) {
            $validator = $this->pool->dequeue();
            $validator->onRequestStart();
            return $validator;
        }

        if ($this->created < $this->maxSize) {
            $validator = $this->createValidator();
            $validator->onWorkerStart();
            $validator->onRequestStart();
            return $validator;
        }

        // Pool exhausted, create temporary validator
        $validator = $this->createValidator();
        $validator->onRequestStart();
        return $validator;
    }

    /**
     * Release a validator back to the pool.
     */
    public function release(StatelessValidator $validator): void
    {
        $validator->onRequestEnd();

        if ($this->pool->count() < $this->maxSize) {
            $this->pool->enqueue($validator);
        }
    }

    /**
     * Execute validation with automatic acquire/release.
     *
     * @template T
     * @param callable(StatelessValidator): T $callback
     * @return T
     */
    public function withValidator(callable $callback): mixed
    {
        $validator = $this->acquire();

        try {
            return $callback($validator);
        } finally {
            $this->release($validator);
        }
    }

    /**
     * Get the current pool size.
     */
    public function getPoolSize(): int
    {
        return $this->pool->count();
    }

    /**
     * Get the maximum pool size.
     */
    public function getMaxSize(): int
    {
        return $this->maxSize;
    }

    /**
     * Get the total number of validators created.
     */
    public function getCreatedCount(): int
    {
        return $this->created;
    }

    private function createValidator(): StatelessValidator
    {
        $this->created++;
        return new StatelessValidator();
    }
}
