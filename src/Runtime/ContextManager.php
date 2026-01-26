<?php

declare(strict_types=1);

namespace Vi\Validation\Runtime;

use Vi\Validation\Execution\ValidationContext;
use Vi\Validation\Execution\ErrorCollector;
use Vi\Validation\Messages\MessageResolver;

/**
 * Manages request-scoped validation context for long-running processes.
 */
final class ContextManager implements RuntimeAwareInterface
{
    private ?ValidationContext $currentContext = null;
    private ?MessageResolver $messageResolver = null;

    /** @var array<string, string> */
    private array $customMessages = [];

    /** @var array<string, string> */
    private array $customAttributes = [];

    public function onWorkerStart(): void
    {
        $this->messageResolver = new MessageResolver();
    }

    public function onRequestStart(): void
    {
        $this->currentContext = null;
        $this->customMessages = [];
        $this->customAttributes = [];
    }

    public function onRequestEnd(): void
    {
        $this->currentContext = null;
        $this->customMessages = [];
        $this->customAttributes = [];
    }

    public function onWorkerStop(): void
    {
        $this->currentContext = null;
        $this->messageResolver = null;
    }

    /**
     * Create a new validation context for the current request.
     *
     * @param array<string, mixed> $data
     */
    public function createContext(array $data): ValidationContext
    {
        $errors = new ErrorCollector();
        $this->currentContext = new ValidationContext($data, $errors);

        return $this->currentContext;
    }

    /**
     * Get the current validation context.
     */
    public function getContext(): ?ValidationContext
    {
        return $this->currentContext;
    }

    /**
     * Get or create the message resolver.
     */
    public function getMessageResolver(): MessageResolver
    {
        if ($this->messageResolver === null) {
            $this->messageResolver = new MessageResolver();
        }

        if (!empty($this->customMessages)) {
            $this->messageResolver->setCustomMessages($this->customMessages);
        }

        if (!empty($this->customAttributes)) {
            $this->messageResolver->setCustomAttributes($this->customAttributes);
        }

        return $this->messageResolver;
    }

    /**
     * Set custom validation messages for the current request.
     *
     * @param array<string, string> $messages
     */
    public function setCustomMessages(array $messages): void
    {
        $this->customMessages = $messages;
    }

    /**
     * Set custom attribute names for the current request.
     *
     * @param array<string, string> $attributes
     */
    public function setCustomAttributes(array $attributes): void
    {
        $this->customAttributes = $attributes;
    }

    /**
     * Reset all request-scoped state.
     */
    public function reset(): void
    {
        $this->onRequestEnd();
    }
}
