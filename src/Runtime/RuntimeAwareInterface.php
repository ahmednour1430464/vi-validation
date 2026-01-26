<?php

declare(strict_types=1);

namespace Vi\Validation\Runtime;

/**
 * Interface for components that need lifecycle management in long-running processes.
 */
interface RuntimeAwareInterface
{
    /**
     * Called when a worker process starts.
     */
    public function onWorkerStart(): void;

    /**
     * Called at the beginning of each request.
     */
    public function onRequestStart(): void;

    /**
     * Called at the end of each request.
     */
    public function onRequestEnd(): void;

    /**
     * Called when a worker process stops.
     */
    public function onWorkerStop(): void;
}
