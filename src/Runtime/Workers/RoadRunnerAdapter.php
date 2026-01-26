<?php

declare(strict_types=1);

namespace Vi\Validation\Runtime\Workers;

use Vi\Validation\Runtime\RuntimeAwareInterface;
use Vi\Validation\Runtime\ValidatorPool;

/**
 * Adapter for RoadRunner worker environments.
 */
final class RoadRunnerAdapter implements RuntimeAwareInterface
{
    private ValidatorPool $pool;
    private bool $workerStarted = false;

    public function __construct(?ValidatorPool $pool = null)
    {
        $this->pool = $pool ?? new ValidatorPool();
    }

    public function onWorkerStart(): void
    {
        $this->workerStarted = true;
        $this->pool->onWorkerStart();
    }

    public function onRequestStart(): void
    {
        if (!$this->workerStarted) {
            $this->onWorkerStart();
        }
        $this->pool->onRequestStart();
    }

    public function onRequestEnd(): void
    {
        $this->pool->onRequestEnd();
        
        // Clear any global state that might leak between requests
        $this->clearRequestState();
    }

    public function onWorkerStop(): void
    {
        $this->pool->onWorkerStop();
        $this->workerStarted = false;
    }

    /**
     * Get the validator pool.
     */
    public function getPool(): ValidatorPool
    {
        return $this->pool;
    }

    /**
     * Check if the worker has been started.
     */
    public function isWorkerStarted(): bool
    {
        return $this->workerStarted;
    }

    /**
     * Check if running in a RoadRunner environment.
     */
    public static function isRoadRunnerEnvironment(): bool
    {
        return isset($_SERVER['RR_MODE']) || 
               getenv('RR_MODE') !== false ||
               class_exists('Spiral\RoadRunner\Worker');
    }

    /**
     * Clear any request-specific state.
     */
    private function clearRequestState(): void
    {
        // Clear any superglobals that might have been modified
        // This is a safety measure for RoadRunner environments
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
    }

    /**
     * Handle a request with automatic lifecycle management.
     *
     * @template T
     * @param callable(): T $handler
     * @return T
     */
    public function handleRequest(callable $handler): mixed
    {
        $this->onRequestStart();

        try {
            return $handler();
        } finally {
            $this->onRequestEnd();
        }
    }
}
