<?php

declare(strict_types=1);

namespace Vi\Validation\Runtime\Workers;

use Vi\Validation\Runtime\RuntimeAwareInterface;
use Vi\Validation\Runtime\ValidatorPool;

/**
 * Adapter for Swoole coroutine environments.
 */
final class SwooleAdapter implements RuntimeAwareInterface
{
    private ValidatorPool $pool;

    /** @var array<int, bool> */
    private array $contextInitialized = [];

    public function __construct(?ValidatorPool $pool = null)
    {
        $this->pool = $pool ?? new ValidatorPool();
    }

    public function onWorkerStart(): void
    {
        $this->pool->onWorkerStart();
    }

    public function onRequestStart(): void
    {
        $cid = $this->getCoroutineId();
        if ($cid > 0) {
            $this->contextInitialized[$cid] = true;
        }
        $this->pool->onRequestStart();
    }

    public function onRequestEnd(): void
    {
        $cid = $this->getCoroutineId();
        if ($cid > 0) {
            unset($this->contextInitialized[$cid]);
        }
        $this->pool->onRequestEnd();
    }

    public function onWorkerStop(): void
    {
        $this->contextInitialized = [];
        $this->pool->onWorkerStop();
    }

    /**
     * Get the validator pool.
     */
    public function getPool(): ValidatorPool
    {
        return $this->pool;
    }

    /**
     * Check if running in a Swoole coroutine context.
     */
    public function isCoroutineContext(): bool
    {
        return $this->getCoroutineId() > 0;
    }

    /**
     * Get the current coroutine ID.
     */
    private function getCoroutineId(): int
    {
        if (!extension_loaded('swoole') && !extension_loaded('openswoole')) {
            return -1;
        }

        if (!class_exists('Swoole\Coroutine')) {
            return -1;
        }

        return \Swoole\Coroutine::getCid();
    }

    /**
     * Check if the current coroutine context is initialized.
     */
    public function isContextInitialized(): bool
    {
        $cid = $this->getCoroutineId();
        return $cid < 0 || isset($this->contextInitialized[$cid]);
    }
}
