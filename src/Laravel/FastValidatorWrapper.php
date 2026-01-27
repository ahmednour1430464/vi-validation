<?php

declare(strict_types=1);

namespace Vi\Validation\Laravel;

use Illuminate\Contracts\Validation\Validator as LaravelValidatorContract;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\ValidationException;
use Vi\Validation\Execution\ValidationResult;
use Vi\Validation\SchemaValidator;

final class FastValidatorWrapper implements LaravelValidatorContract
{
    private SchemaValidator $validator;

    /** @var array<string, mixed> */
    private array $data;

    /** @var array<string, mixed> */
    private array $rules = [];

    /** @var array<string, string> */
    private array $customMessages = [];

    /** @var array<string, string> */
    private array $customAttributes = [];

    private ?ValidationResult $result = null;

    /** @var array<callable> */
    private array $afterCallbacks = [];

    private bool $stopOnFirstFailure = false;

    public function __construct(SchemaValidator $validator, array $data)
    {
        $this->validator = $validator;
        $this->data = $data;
    }

    public function fails(): bool
    {
        return !$this->passes();
    }

    public function passes(): bool
    {
        if ($this->result === null) {
            $this->result = $this->validator->validate($this->data);
            
            // Execute after callbacks
            foreach ($this->afterCallbacks as $callback) {
                $callback($this);
            }
        }

        return $this->result->isValid();
    }

    public function errors(): MessageBag
    {
        $bag = new MessageBag();

        if ($this->result === null) {
            $this->passes();
        }

        // Use proper messages from ValidationResult
        foreach ($this->result->messages() as $field => $messages) {
            foreach ($messages as $message) {
                $bag->add($field, $message);
            }
        }

        return $bag;
    }

    public function after($callback)
    {
        $this->afterCallbacks[] = $callback;
        return $this;
    }

    public function sometimes($attribute, $rules, callable $callback)
    {
        // TODO: Implement conditional rule application
        return $this;
    }

    public function getMessageBag()
    {
        return $this->errors();
    }

    public function validated()
    {
        if ($this->fails()) {
            throw new ValidationException($this);
        }

        return $this->data;
    }

    public function validate(): array
    {
        if ($this->fails()) {
            throw new ValidationException($this);
        }

        return $this->data;
    }

    public function failed(): array
    {
        if ($this->result === null) {
            $this->passes();
        }

        $failed = [];
        foreach ($this->result->errors() as $field => $fieldErrors) {
            foreach ($fieldErrors as $error) {
                $failed[$field][$error['rule']] = [];
            }
        }

        return $failed;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
        $this->result = null;
    }

    public function sometimesWith($attribute, $rules, callable $callback)
    {
        return $this;
    }

    /**
     * Get the validation rules.
     *
     * @return array<string, mixed>
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * Set the validation rules.
     *
     * @param array<string, mixed> $rules
     */
    public function setRules(array $rules): self
    {
        $this->rules = $rules;
        $this->result = null;
        return $this;
    }

    /**
     * Add additional rules to the existing rules.
     *
     * @param array<string, mixed> $rules
     */
    public function addRules(array $rules): self
    {
        $this->rules = array_merge($this->rules, $rules);
        $this->result = null;
        return $this;
    }

    /**
     * Get the custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function getCustomMessages(): array
    {
        return $this->customMessages;
    }

    /**
     * Set custom messages for validation errors.
     *
     * @param array<string, string> $messages
     */
    public function setCustomMessages(array $messages): self
    {
        $this->customMessages = $messages;
        return $this;
    }

    /**
     * Get the custom attributes for validation errors.
     *
     * @return array<string, string>
     */
    public function getCustomAttributes(): array
    {
        return $this->customAttributes;
    }

    /**
     * Set custom attributes for validation errors.
     *
     * @param array<string, string> $attributes
     */
    public function setCustomAttributes(array $attributes): self
    {
        $this->customAttributes = $attributes;
        return $this;
    }

    /**
     * Stop validation on first failure.
     */
    public function stopOnFirstFailure(bool $stop = true): self
    {
        $this->stopOnFirstFailure = $stop;
        return $this;
    }

    /**
     * Get the safe (validated) data.
     *
     * @return array<string, mixed>
     */
    public function safe(): array
    {
        return $this->validated();
    }
}
