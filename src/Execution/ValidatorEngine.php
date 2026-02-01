<?php

declare(strict_types=1);

namespace Vi\Validation\Execution;

use Vi\Validation\Messages\MessageResolver;
use Vi\Validation\Rules\NullableRule;
use Vi\Validation\Rules\RuleInterface;

final class ValidatorEngine
{
    private ?MessageResolver $messageResolver;
    private bool $failFast;
    private int $maxErrors;

    private ?ErrorCollector $errors = null;
    private ?ValidationContext $context = null;

    public function __construct(
        ?MessageResolver $messageResolver = null,
        bool $failFast = false,
        int $maxErrors = 100
    ) {
        $this->messageResolver = $messageResolver ?? new MessageResolver();
        $this->failFast = $failFast;
        $this->maxErrors = $maxErrors;
    }

    public function validate(CompiledSchema $schema, array $data): ValidationResult
    {
        if ($this->errors === null) {
            $this->errors = new ErrorCollector();
            $this->context = new ValidationContext($data, $this->errors);
        } else {
            $this->errors->reset();
            $this->context->setData($data);
        }

        $errors = $this->errors;
        $context = $this->context;

        foreach ($schema->getFields() as $field) {
            if ($this->shouldStopValidation($errors)) {
                break;
            }

            $value = $field->getValue($data);

            $rules = $field->getRules();
            $isNullable = $field->isNullable();

            if ($value === null && $isNullable) {
                continue;
            }

            foreach ($rules as $rule) {
                if ($this->applyRule($rule, $field->getName(), $value, $context)) {
                    if ($this->shouldStopValidation($errors)) {
                        break;
                    }
                }
            }
        }

        return new ValidationResult($errors->all(), $data, $this->messageResolver);
    }

    public function setFailFast(bool $failFast): void
    {
        $this->failFast = $failFast;
    }

    public function setMaxErrors(int $maxErrors): void
    {
        $this->maxErrors = $maxErrors;
    }

    public function setMessageResolver(MessageResolver $resolver): void
    {
        $this->messageResolver = $resolver;
    }



    private function shouldStopValidation(ErrorCollector $errors): bool
    {
        if ($this->failFast && $errors->hasErrors()) {
            return true;
        }

        if ($errors->count() >= $this->maxErrors) {
            return true;
        }

        return false;
    }

    /**
     * @param RuleInterface $rule
     */
    private function applyRule(RuleInterface $rule, string $field, mixed $value, ValidationContext $context): bool
    {
        $error = $rule->validate($value, $field, $context);

        if ($error !== null) {
            $message = $error['message'] ?? null;
            
            // Resolve message if MessageResolver is available and no custom message was provided
            if ($message === null && $this->messageResolver !== null) {
                $params = $error['parameters'] ?? $error['params'] ?? [];
                $message = $this->messageResolver->resolve($field, $error['rule'], $params);
            }

            $context->addError($field, $error['rule'], $message);
            return true;
        }

        return false;
    }
}
