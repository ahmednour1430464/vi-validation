<?php

declare(strict_types=1);

namespace Vi\Validation\Laravel;

use Illuminate\Contracts\Validation\Validator as LaravelValidatorContract;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\ValidationException;
use Vi\Validation\SchemaValidator;

final class FastValidatorWrapper implements LaravelValidatorContract
{
    private SchemaValidator $validator;

    /** @var array<string, mixed> */
    private array $data;

    private ?array $errors = null;

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
        $result = $this->validator->validate($this->data);
        $this->errors = $result->errors();

        return $result->isValid();
    }

    public function errors(): MessageBag
    {
        $bag = new MessageBag();

        if ($this->errors === null) {
            $this->passes();
        }

        foreach ($this->errors ?? [] as $field => $fieldErrors) {
            foreach ($fieldErrors as $error) {
                $bag->add($field, $error['rule']);
            }
        }

        return $bag;
    }

    // The remaining methods from the contract can be progressively implemented as needed.
    public function after($callback)
    {
        return $this;
    }

    public function sometimes($attribute, $rules, callable $callback)
    {
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
        if ($this->errors === null) {
            $this->passes();
        }

        $failed = [];
        foreach ($this->errors ?? [] as $field => $fieldErrors) {
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
        $this->errors = null;
    }

    public function sometimesWith($attribute, $rules, callable $callback)
    {
        return $this;
    }
}
